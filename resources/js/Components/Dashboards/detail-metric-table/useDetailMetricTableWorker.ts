import { computed, onBeforeUnmount, onMounted, ref, toRaw, watch, type Ref } from 'vue'
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

    let searchDebounce: ReturnType<typeof setTimeout> | undefined
    let worker: Worker | null = null
    let requestId = 0
    let pendingExportResolve: ((indexes: number[]) => void) | null = null

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
        worker = new Worker(URL.createObjectURL(blob))
        return worker
    }

    function initWorkerData() {
        datasetVersion.value += 1
        ensureWorker().postMessage({
            type: 'init',
            rows: toRaw(params.rows.value),
            columnKeys: params.columns.value.map((c) => c.key),
            datasetVersion: datasetVersion.value,
        })
    }

    function processRows() {
        isProcessing.value = true
        requestId += 1
        ensureWorker().postMessage({
            type: 'process',
            reqId: requestId,
            datasetVersion: datasetVersion.value,
            query: searchText.value,
            sorting: sorting.value ? { key: sorting.value.key, desc: sorting.value.desc } : null,
            pageIndex: pageIndex.value,
            pageSize: pageSize.value,
        })
    }

    function requestExportIndexes(): Promise<number[]> {
        return new Promise((resolve) => {
            pendingExportResolve = resolve
            requestId += 1
            ensureWorker().postMessage({
                type: 'process',
                reqId: requestId,
                datasetVersion: datasetVersion.value,
                query: searchText.value,
                sorting: sorting.value ? { key: sorting.value.key, desc: sorting.value.desc } : null,
                pageIndex: 0,
                pageSize: 0,
                exportAll: true,
            })
        })
    }

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
            initWorkerData()
            processRows()
        },
        { immediate: true }
    )

    watch(
        () => [searchText.value, pageIndex.value, pageSize.value, sorting.value] as const,
        () => {
            processRows()
        }
    )

    watch(
        () => totalPages.value,
        (pages) => {
            if (pageIndex.value > pages - 1) pageIndex.value = Math.max(0, pages - 1)
        }
    )

    onMounted(() => {
        const w = ensureWorker()
        w.onmessage = (event: MessageEvent<WorkerResponse>) => {
            const payload = event.data
            if (payload.reqId !== requestId) return
            if (payload.datasetVersion !== datasetVersion.value) return

            if (payload.exportAll) {
                pendingExportResolve?.(payload.pageIndexes)
                pendingExportResolve = null
                return
            }

            workerTotal.value = payload.total
            workerPageIndexes.value = payload.pageIndexes
            isProcessing.value = false
        }
    })

    onBeforeUnmount(() => {
        if (searchDebounce) clearTimeout(searchDebounce)
        worker?.terminate()
        worker = null
        pendingExportResolve = null
    })

    return {
        rawSearchText,
        searchText,
        isProcessing,
        pageIndex,
        pageSize,
        sorting,
        pageRows,
        totalRecords,
        totalPages,
        currentPage,
        requestExportIndexes,
        toggleSort,
        sortIndicator,
    }
}
