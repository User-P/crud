import { computed, onBeforeUnmount, ref, toRaw, watch, type Ref } from 'vue'
import { WORKER_SOURCE } from './workerSource'
import type { DetailMetricColumn, TableSort } from './types'

type WorkerResponse = {
    reqId: number
    datasetVersion: number
    total: number
    pageIndexes: number[]
    exportAll?: boolean
}

export function useDetailMetricTableWorker(params: {
    rows: Ref<Record<string, unknown>[]>
    columns: Ref<DetailMetricColumn[]>
    initialRowsPerPage: number
}) {
    const rawSearchText = ref('')
    const searchText = ref('')
    const isProcessing = ref(false)
    const pageIndex = ref(0)
    const pageSize = ref(params.initialRowsPerPage)
    const workerTotal = ref(params.rows.value.length)
    const workerPageIndexes = ref<number[]>([])
    const sorting = ref<TableSort>(null)
    const datasetVersion = ref(0)
    /** Se incrementa al cambiar filas/columnas para forzar un `process` aunque búsqueda/página no varien. */
    const dataRevision = ref(0)

    let searchDebounce: ReturnType<typeof setTimeout> | undefined
    let worker: Worker | null = null
    let workerObjectUrl: string | null = null
    /** Peticiones de página / orden / búsqueda (no confundir con exportaciones masivas). */
    let uiRequestId = 0
    /** Contador aparte: las respuestas `exportAll` deben emparejarse por `reqId`, no con el último `uiRequestId`. */
    let exportRequestId = 0
    /** Resoluciones pendientes de `exportAll` (export CSV y «seleccionar todas las filtradas»). */
    const pendingExportResolves = new Map<number, (indexes: number[]) => void>()

    const pageRows = computed<Record<string, unknown>[]>(() => {
        if (workerPageIndexes.value.length === 0) return []
        return workerPageIndexes.value.map((idx) => params.rows.value[idx])
    })

    const totalRecords = computed(() => workerTotal.value)
    const totalPages = computed(() => Math.max(1, Math.ceil(totalRecords.value / Math.max(1, pageSize.value))))
    const currentPage = computed(() => Math.min(pageIndex.value + 1, totalPages.value))

    function ensureWorker(): Worker {
        if (worker) return worker
        const blob = new Blob([WORKER_SOURCE], { type: 'application/javascript' })
        workerObjectUrl = URL.createObjectURL(blob)
        const w = new Worker(workerObjectUrl)
        w.onmessage = (event: MessageEvent<WorkerResponse>) => {
            const payload = event.data
            if (payload.datasetVersion !== datasetVersion.value) {
                if (payload.exportAll) {
                    const resolve = pendingExportResolves.get(payload.reqId)
                    if (resolve) {
                        resolve([])
                        pendingExportResolves.delete(payload.reqId)
                    }
                }
                return
            }

            if (payload.exportAll) {
                const resolve = pendingExportResolves.get(payload.reqId)
                if (resolve) {
                    resolve(payload.pageIndexes)
                    pendingExportResolves.delete(payload.reqId)
                }
                return
            }

            if (payload.reqId !== uiRequestId) return

            workerTotal.value = payload.total
            workerPageIndexes.value = payload.pageIndexes
            isProcessing.value = false
        }
        worker = w
        return worker
    }

    function initWorkerData() {
        datasetVersion.value += 1
        for (const resolve of pendingExportResolves.values()) {
            resolve([])
        }
        pendingExportResolves.clear()
        ensureWorker().postMessage({
            type: 'init',
            rows: toRaw(params.rows.value),
            columnKeys: params.columns.value.map((c) => c.key),
            datasetVersion: datasetVersion.value,
        })
    }

    function processRows() {
        isProcessing.value = true
        uiRequestId += 1
        ensureWorker().postMessage({
            type: 'process',
            reqId: uiRequestId,
            datasetVersion: datasetVersion.value,
            query: searchText.value,
            sorting: sorting.value ? { key: sorting.value.key, desc: sorting.value.desc } : null,
            pageIndex: pageIndex.value,
            pageSize: pageSize.value,
        })
    }

    function requestAllFilteredRowIndexes(): Promise<number[]> {
        return new Promise((resolve) => {
            exportRequestId += 1
            const id = exportRequestId
            pendingExportResolves.set(id, resolve)
            ensureWorker().postMessage({
                type: 'process',
                reqId: id,
                datasetVersion: datasetVersion.value,
                query: searchText.value,
                sorting: sorting.value ? { key: sorting.value.key, desc: sorting.value.desc } : null,
                pageIndex: 0,
                pageSize: 0,
                exportAll: true,
            })
        })
    }

    /** Índices de todas las filas que cumplen búsqueda y orden (misma lista que el CSV completo). */
    const requestExportIndexes = requestAllFilteredRowIndexes

    function toggleSort(key: string) {
        if (sorting.value?.key !== key) {
            sorting.value = { key, desc: false }
        } else if (!sorting.value.desc) {
            sorting.value = { key, desc: true }
        } else {
            sorting.value = null
        }
        pageIndex.value = 0
    }

    function sortIndicator(key: string): string {
        if (sorting.value?.key !== key) return ''
        return sorting.value.desc ? '↓' : '↑'
    }

    watch(
        () => rawSearchText.value,
        (value) => {
            if (searchDebounce) clearTimeout(searchDebounce)
            searchDebounce = setTimeout(() => {
                searchText.value = value.trim()
                pageIndex.value = 0
            }, 220)
        }
    )

    watch(
        () => [params.rows.value, params.columns.value] as const,
        () => {
            rawSearchText.value = ''
            searchText.value = ''
            sorting.value = null
            pageIndex.value = 0
            workerPageIndexes.value = []
            workerTotal.value = params.rows.value.length
            dataRevision.value += 1
            initWorkerData()
        },
        { immediate: true }
    )

    watch(
        () =>
            [dataRevision.value, searchText.value, pageIndex.value, pageSize.value, sorting.value] as const,
        () => {
            processRows()
        },
        { immediate: true }
    )

    watch(
        () => totalPages.value,
        (pages) => {
            if (pageIndex.value > pages - 1) pageIndex.value = Math.max(0, pages - 1)
        }
    )

    onBeforeUnmount(() => {
        if (searchDebounce) clearTimeout(searchDebounce)
        for (const resolve of pendingExportResolves.values()) {
            resolve([])
        }
        pendingExportResolves.clear()
        worker?.terminate()
        worker = null
        if (workerObjectUrl) {
            URL.revokeObjectURL(workerObjectUrl)
            workerObjectUrl = null
        }
    })

    return {
        rawSearchText,
        searchText,
        isProcessing,
        pageIndex,
        pageSize,
        sorting,
        pageRows,
        workerPageIndexes,
        totalRecords,
        totalPages,
        currentPage,
        requestExportIndexes,
        requestAllFilteredRowIndexes,
        toggleSort,
        sortIndicator,
    }
}
