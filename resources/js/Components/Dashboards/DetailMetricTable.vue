<template>
    <div class="detail-metric-table rounded-xl border border-[var(--th-border)] bg-[var(--th-input-bg)] backdrop-blur-sm overflow-hidden">
        <div class="flex flex-wrap items-center gap-3 border-b border-[var(--th-border)] bg-[var(--th-input-bg)] px-4 py-3">
            <span class="relative flex-1 min-w-[220px] max-w-sm">
                <Icon
                    icon="heroicons:magnifying-glass"
                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[color:var(--th-text-muted)]"
                    aria-hidden="true"
                />
                <InputText
                    v-model="rawSearchText"
                    :placeholder="searchPlaceholder"
                    class="w-full rounded-lg border-[var(--th-input-border)] bg-[var(--th-input-bg)] pl-9 text-sm placeholder:italic"
                    aria-label="Buscar en la tabla"
                />
            </span>

            <div class="flex items-center gap-3">
                <span
                    v-if="searchText"
                    class="rounded-full bg-[var(--th-item-active-bg)] px-2.5 py-1 text-xs font-medium text-[color:var(--th-item-active-color)]"
                >
                    {{ totalRecords }} de {{ rows.length }} coincidencias
                </span>
                <span
                    v-if="isProcessing"
                    class="text-xs text-[color:var(--th-text-muted)]"
                >
                    Procesando...
                </span>
                <Button
                    label="Exportar CSV"
                    icon="pi pi-download"
                    size="small"
                    outlined
                    :disabled="totalRecords === 0"
                    severity="secondary"
                    class="shrink-0"
                    :loading="exportingCsv"
                    @click="exportCSV"
                />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-[var(--th-border)] bg-[var(--th-input-bg)]">
                    <tr>
                        <th
                            v-for="header in table.getHeaderGroups()[0]?.headers ?? []"
                            :key="header.id"
                            class="px-4 py-2.5 text-left font-semibold text-[color:var(--th-text-primary)]"
                            :class="{ 'cursor-pointer select-none': header.column.getCanSort() }"
                            @click="header.column.getCanSort() && toggleSort(header.column.id)"
                        >
                            <div class="flex items-center gap-2">
                                <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
                                <span v-if="header.column.getCanSort()" class="text-xs text-[color:var(--th-text-muted)]">
                                    {{ sortIndicator(header.column.id) }}
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody v-if="pageRows.length > 0">
                    <tr
                        v-for="(row, rowIndex) in pageRows"
                        :key="rowIndex"
                        class="border-b border-[var(--th-border)]/60 odd:bg-[var(--th-input-bg)] even:bg-[var(--th-item-hover-bg)]/20"
                    >
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            class="px-4 py-2.5 text-[color:var(--th-text-primary)]"
                            :class="[col.class, { 'tabular-nums font-semibold': col.numeric }]"
                        >
                            {{ formatCellValue(row, col) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="pageRows.length === 0" class="px-4 py-8">
            <EmptyState
                :title="searchText ? 'Ningún registro coincide con la búsqueda' : 'No hay datos para mostrar'"
                :description="searchText ? 'Prueba con otros términos.' : 'Selecciona una métrica o filtra los datos.'"
                icon="heroicons:magnifying-glass"
            />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[var(--th-border)] bg-[var(--th-input-bg)] px-4 py-3">
            <p class="text-sm text-[color:var(--th-text-secondary)]">
                <span class="font-semibold tabular-nums text-[color:var(--th-text-primary)]">{{ totalRecords }}</span>
                {{ totalRecords === 1 ? 'registro' : 'registros' }}
                <span v-if="searchText" class="ml-1 text-[color:var(--th-text-muted)]">(filtrados)</span>
            </p>
            <div class="flex items-center gap-2 text-xs">
                <button
                    type="button"
                    class="rounded-md border border-[var(--th-border)] px-2 py-1 text-[color:var(--th-text-secondary)] disabled:opacity-50"
                    :disabled="pageIndex === 0 || isProcessing"
                    @click="pageIndex = Math.max(0, pageIndex - 1)"
                >
                    Anterior
                </button>
                <span class="text-[color:var(--th-text-muted)]">
                    Página {{ currentPage }} de {{ totalPages }}
                </span>
                <button
                    type="button"
                    class="rounded-md border border-[var(--th-border)] px-2 py-1 text-[color:var(--th-text-secondary)] disabled:opacity-50"
                    :disabled="pageIndex >= totalPages - 1 || isProcessing"
                    @click="pageIndex = Math.min(totalPages - 1, pageIndex + 1)"
                >
                    Siguiente
                </button>
                <label class="ml-2 text-[color:var(--th-text-muted)]">Filas:</label>
                <select
                    v-model.number="pageSize"
                    class="rounded-md border border-[var(--th-border)] bg-[var(--th-input-bg)] px-2 py-1 text-[color:var(--th-text-secondary)]"
                    :disabled="isProcessing"
                >
                    <option v-for="opt in rowsPerPageOptions" :key="opt" :value="opt">{{ opt }}</option>
                </select>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onBeforeUnmount, toRaw } from 'vue'
import { Icon } from '@iconify/vue'
import EmptyState from '@/Components/EmptyState.vue'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import { FlexRender, createColumnHelper, getCoreRowModel, useVueTable } from '@tanstack/vue-table'

/** Configuración de una columna (datos dinámicos) */
export interface DetailMetricColumn<T = Record<string, unknown>> {
    /** Clave del campo en cada fila */
    key: string
    /** Encabezado en la tabla y por defecto en CSV */
    header: string
    /** Etiqueta para exportación CSV (por defecto: header) */
    exportLabel?: string
    /** Si la columna es ordenable */
    sortable?: boolean
    /** Formatear valor para mostrar (y para CSV si no hay exportFormat) */
    format?: (value: unknown, row: T) => string
    /** Formatear para CSV (por defecto: format o String(value)) */
    exportFormat?: (value: unknown, row: T) => string
    /** Clase CSS para las celdas */
    class?: string
    /** Aplicar tabular-nums y font-semibold (para números) */
    numeric?: boolean
}

const props = withDefaults(
    defineProps<{
        /** Filas genéricas (cualquier objeto con claves que coincidan con columns[].key) */
        rows: Record<string, unknown>[]
        /** Definición de columnas */
        columns: DetailMetricColumn[]
        /** Placeholder del campo de búsqueda */
        searchPlaceholder?: string
        /** Etiqueta para el nombre del archivo al exportar */
        exportLabel?: string
        /** Filas por página por defecto */
        rowsPerPage?: number
        /** Opciones del selector de filas por página */
        rowsPerPageOptions?: number[]
        /** Si > 0, export CSV se divide en varios archivos de hasta esta cantidad de filas cada uno (evita memoria/string gigante). */
        maxRowsPerCsvFile?: number
    }>(),
    {
        searchPlaceholder: 'Buscar en la tabla…',
        rowsPerPage: 5,
        rowsPerPageOptions: () => [5, 10, 15, 25, 50],
        maxRowsPerCsvFile: 0,
    }
)

const rawSearchText = ref('')
const searchText = ref('')
const exportingCsv = ref(false)
const isProcessing = ref(false)
const pageIndex = ref(0)
const pageSize = ref(props.rowsPerPage)
const workerTotal = ref(props.rows.length)
const workerPageIndexes = ref<number[]>([])
const sorting = ref<{ key: string; desc: boolean } | null>(null)

let searchDebounce: ReturnType<typeof setTimeout> | undefined

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

function getCellValue(row: Record<string, unknown>, key: string): unknown {
    return row[key]
}

function formatCellValue(row: Record<string, unknown>, col: DetailMetricColumn): string {
    const raw = getCellValue(row, col.key)
    if (col.format) return col.format(raw, row as Record<string, unknown>)
    return raw == null ? '' : String(raw)
}

const columnHelper = createColumnHelper<Record<string, unknown>>()
const tanstackColumns = computed(() =>
    props.columns.map((col) =>
        columnHelper.accessor((row) => getCellValue(row, col.key), {
            id: col.key,
            header: col.header,
            enableSorting: col.sortable !== false,
            cell: (info) => {
                const row = info.row.original
                return formatCellValue(row, col)
            },
        })
    )
)

const pageRows = computed<Record<string, unknown>[]>(() => {
    if (workerPageIndexes.value.length === 0) return []
    return workerPageIndexes.value
        .map((idx) => props.rows[idx])
        .filter((row): row is Record<string, unknown> => !!row)
})

const table = useVueTable({
    get data() {
        return pageRows.value
    },
    get columns() {
        return tanstackColumns.value
    },
    getCoreRowModel: getCoreRowModel(),
})

const totalRecords = computed(() => workerTotal.value)
const totalPages = computed(() => Math.max(1, Math.ceil(totalRecords.value / pageSize.value)))
const currentPage = computed(() => Math.min(pageIndex.value + 1, totalPages.value))

let worker: Worker | null = null
let requestId = 0

function ensureWorker() {
    if (worker) return worker
    const workerSource = `
    let dataset = [];
    let columnKeys = [];
    self.onmessage = (event) => {
      const payload = event.data;
      if (payload.type === 'init') {
        dataset = Array.isArray(payload.rows) ? payload.rows : [];
        columnKeys = Array.isArray(payload.columnKeys) ? payload.columnKeys : [];
        return;
      }
      if (payload.type !== 'process') return;
      const q = (payload.query || '').toLowerCase();
      const sort = payload.sorting;
      const pageIndex = Number(payload.pageIndex || 0);
      const pageSize = Number(payload.pageSize || 10);

      let indexes = [];
      for (let i = 0; i < dataset.length; i++) {
        if (!q) {
          indexes.push(i);
          continue;
        }
        const row = dataset[i] || {};
        let match = false;
        for (let j = 0; j < columnKeys.length; j++) {
          const v = row[columnKeys[j]];
          const s = v == null ? '' : String(v).toLowerCase();
          if (s.includes(q)) {
            match = true;
            break;
          }
        }
        if (match) indexes.push(i);
      }

      if (sort && sort.key) {
        indexes.sort((a, b) => {
          const av = dataset[a]?.[sort.key];
          const bv = dataset[b]?.[sort.key];
          if (av == null && bv == null) return 0;
          if (av == null) return sort.desc ? 1 : -1;
          if (bv == null) return sort.desc ? -1 : 1;
          if (typeof av === 'number' && typeof bv === 'number') {
            return sort.desc ? bv - av : av - bv;
          }
          const as = String(av);
          const bs = String(bv);
          const cmp = as.localeCompare(bs, 'es', { numeric: true, sensitivity: 'base' });
          return sort.desc ? -cmp : cmp;
        });
      }

      const total = indexes.length;
      const start = pageIndex * pageSize;
      const end = Math.min(start + pageSize, total);
      const pageIndexes = start >= total ? [] : indexes.slice(start, end);

      self.postMessage({
        reqId: payload.reqId,
        total,
        pageIndexes,
      });
    };
  `
    const blob = new Blob([workerSource], { type: 'application/javascript' })
    worker = new Worker(URL.createObjectURL(blob))
    return worker
}

function initWorkerData() {
    const w = ensureWorker()
    w.postMessage({
        type: 'init',
        rows: toRaw(props.rows),
        columnKeys: props.columns.map((c) => c.key),
    })
}

function processRows() {
    const w = ensureWorker()
    isProcessing.value = true
    requestId += 1
    const currentReqId = requestId
    w.postMessage({
        type: 'process',
        reqId: currentReqId,
        query: searchText.value,
        sorting: sorting.value ? { key: sorting.value.key, desc: sorting.value.desc } : null,
        pageIndex: pageIndex.value,
        pageSize: pageSize.value,
    })
}

function toggleSort(key: string) {
    if (sorting.value?.key !== key) {
        sorting.value = { key, desc: false }
        pageIndex.value = 0
        return
    }
    if (sorting.value.desc === false) {
        sorting.value = { key, desc: true }
        pageIndex.value = 0
        return
    }
    sorting.value = null
    pageIndex.value = 0
}

function sortIndicator(key: string): string {
    if (sorting.value?.key !== key) return ''
    return sorting.value.desc ? '↓' : '↑'
}

watch(
    () => [props.rows, props.columns],
    () => {
        initWorkerData()
        pageIndex.value = 0
        processRows()
    },
    { immediate: true }
)

watch(
    () => [searchText.value, pageIndex.value, pageSize.value, sorting.value],
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

if (typeof window !== 'undefined') {
    const w = ensureWorker()
    w.onmessage = (event: MessageEvent<{ reqId: number; total: number; pageIndexes: number[] }>) => {
        const payload = event.data
        if (payload.reqId !== requestId) return
        workerTotal.value = payload.total
        workerPageIndexes.value = payload.pageIndexes
        isProcessing.value = false
    }
}

onBeforeUnmount(() => {
    if (searchDebounce) clearTimeout(searchDebounce)
    worker?.terminate()
})

function escapeCsvCell(value: string): string {
    if (/[",\n\r]/.test(value)) return `"${value.replace(/"/g, '""')}"`
    return value
}

/**
 * Export CSV: si maxRowsPerCsvFile > 0 y hay más filas, se empaquetan todas las partes
 * en un único archivo ZIP (una sola descarga). Incluye LEEME.txt con la lista de partes.
 */
async function exportCSV() {
    if (totalRecords.value === 0) return
    const query = searchText.value.toLowerCase()
    const filteredRows = !query
        ? props.rows
        : props.rows.filter((row) =>
              props.columns.some((col) => {
                  const value = getCellValue(row, col.key)
                  return String(value ?? '').toLowerCase().includes(query)
              })
          )
    const rows = sorting.value
        ? [...filteredRows].sort((a, b) => {
              const av = getCellValue(a, sorting.value!.key)
              const bv = getCellValue(b, sorting.value!.key)
              if (av == null && bv == null) return 0
              if (av == null) return sorting.value!.desc ? 1 : -1
              if (bv == null) return sorting.value!.desc ? -1 : 1
              if (typeof av === 'number' && typeof bv === 'number') {
                  return sorting.value!.desc ? bv - av : av - bv
              }
              const cmp = String(av).localeCompare(String(bv), 'es', { numeric: true, sensitivity: 'base' })
              return sorting.value!.desc ? -cmp : cmp
          })
        : filteredRows
    const cols = props.columns
    const maxPerFile = props.maxRowsPerCsvFile ?? 0
    const baseName = `detalle-${(props.exportLabel || 'datos').replace(/[^\w\s-]/g, '').replace(/\s+/g, '-')}-${new Date().toISOString().slice(0, 10)}`

    function buildCsvChunk(chunkRows: Record<string, unknown>[]): string {
        const header = cols.map((c) => c.exportLabel ?? c.header).join(',')
        const lines = chunkRows.map((row) =>
            cols
                .map((c) => {
                    const raw = getCellValue(row, c.key)
                    const str = c.exportFormat
                        ? c.exportFormat(raw, row as Record<string, unknown>)
                        : c.format
                          ? c.format(raw, row as Record<string, unknown>)
                          : raw == null ? '' : String(raw)
                    return escapeCsvCell(str)
                })
                .join(',')
        )
        return [header, ...lines].join('\r\n')
    }

    function downloadBlob(blob: Blob, filename: string) {
        const url = URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = filename
        a.click()
        URL.revokeObjectURL(url)
    }

    if (maxPerFile > 0 && rows.length > maxPerFile) {
        exportingCsv.value = true
        try {
            const JSZip = (await import('jszip')).default
            const zip = new JSZip()
            const totalParts = Math.ceil(rows.length / maxPerFile)
            const partNames: string[] = []

            for (let part = 0; part < totalParts; part++) {
                const start = part * maxPerFile
                const end = Math.min(start + maxPerFile, rows.length)
                const chunk = rows.slice(start, end)
                const csv = buildCsvChunk(chunk)
                const partName = `parte-${part + 1}-de-${totalParts}.csv`
                partNames.push(partName)
                zip.file(partName, '\ufeff' + csv, { createFolders: false })
            }

            const leeme = [
                'Exportación en varias partes',
                '────────────────────────────',
                `Total de registros: ${rows.length.toLocaleString('es')}`,
                `Partes: ${totalParts}`,
                '',
                'Archivos incluidos (en orden):',
                ...partNames.map((name, i) => `  ${i + 1}. ${name}`),
                '',
                'Cada archivo tiene cabecera. Para unir en Excel/LibreOffice:',
                'abrir el primero y luego insertar las filas de parte-2, parte-3, etc.',
            ].join('\r\n')
            zip.file('LEEME.txt', leeme, { createFolders: false })

            const blob = await zip.generateAsync({ type: 'blob' })
            downloadBlob(blob, `${baseName}.zip`)
        } finally {
            exportingCsv.value = false
        }
    } else {
        const csv = buildCsvChunk(rows)
        downloadBlob(new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8' }), `${baseName}.csv`)
    }
}
</script>
