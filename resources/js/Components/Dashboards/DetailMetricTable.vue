<template>
    <div class="detail-metric-table rounded-xl border border-(--th-border) bg-(--th-input-bg)/60 backdrop-blur-sm overflow-hidden">
        <!-- Barra: búsqueda + exportar -->
        <div class="flex flex-wrap items-center gap-3 border-b border-(--th-border) bg-(--th-input-bg)/40 px-4 py-3">
            <span class="relative flex-1 min-w-[200px] max-w-sm">
                <Icon
                    icon="heroicons:magnifying-glass"
                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-(--th-text-muted)"
                    aria-hidden="true"
                />
                <InputText
                    v-model="searchText"
                    :placeholder="searchPlaceholder"
                    class="w-full rounded-lg border-(--th-input-border) bg-(--th-input-bg) pl-9 text-sm placeholder:italic"
                    aria-label="Buscar en la tabla"
                />
            </span>
            <div class="flex items-center gap-3">
                <span
                    v-if="searchText"
                    class="rounded-full bg-(--th-item-active-bg) px-2.5 py-1 text-xs font-medium text-(--th-item-active-color)"
                >
                    {{ filteredRows.length }} de {{ rows.length }} coincidencias
                </span>
                <Button
                    label="Exportar CSV"
                    icon="pi pi-download"
                    size="small"
                    outlined
                    :disabled="filteredRows.length === 0"
                    severity="secondary"
                    class="shrink-0"
                    :loading="exportingCsv"
                    @click="exportCSV"
                />
            </div>
        </div>

        <DataTable
            :value="filteredRows"
            size="small"
            striped-rows
            :rows="rowsPerPage"
            :rows-per-page-options="rowsPerPageOptions"
            paginator
            paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
            current-page-report-template="{first} – {last} de {totalRecords}"
            responsive-layout="scroll"
            class="detail-datatable text-(--th-text-primary) border-0"
        >
            <Column
                v-for="col in columns"
                :key="col.key"
                :field="col.key"
                :header="col.header"
                :sortable="col.sortable !== false"
                :class="col.class"
            >
                <template v-if="col.format" #body="{ data }">
                    <span :class="{ 'tabular-nums font-semibold': col.numeric }">
                        {{ col.format(getCellValue(data, col.key), data) }}
                    </span>
                </template>
            </Column>

            <template #empty>
                <EmptyState
                    :title="searchText ? 'Ningún registro coincide con la búsqueda' : 'No hay datos para mostrar'"
                    :description="searchText ? 'Prueba con otros términos.' : 'Selecciona una métrica o filtra los datos.'"
                    icon="heroicons:magnifying-glass"
                />
            </template>

            <template #footer>
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-t border-(--th-border) bg-(--th-input-bg)/30 px-4 py-3"
                >
                    <p class="text-sm text-(--th-text-secondary)">
                        <span class="font-semibold tabular-nums text-(--th-text-primary)">{{ totalRecords }}</span>
                        {{ totalRecords === 1 ? 'registro' : 'registros' }}
                        <span v-if="searchText" class="ml-1 text-(--th-text-muted)">(filtrados)</span>
                    </p>
                    <p class="text-xs text-(--th-text-muted)">
                        Usa el paginador para ver más resultados.
                    </p>
                </div>
            </template>
        </DataTable>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Icon } from '@iconify/vue'
import EmptyState from '@/Components/EmptyState.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'

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

const searchText = ref('')
const exportingCsv = ref(false)

function getCellValue(row: Record<string, unknown>, key: string): unknown {
    return row[key]
}

/** Búsqueda en todos los valores de las columnas */
const filteredRows = computed(() => {
    const q = searchText.value.trim().toLowerCase()
    if (!q) return props.rows
    const keys = props.columns.map((c) => c.key)
    return props.rows.filter((row) => {
        return keys.some((key) => {
            const val = getCellValue(row, key)
            const str =
                val == null
                    ? ''
                    : props.columns.find((c) => c.key === key)?.format
                      ? (props.columns.find((c) => c.key === key)!.format as (v: unknown, r: Record<string, unknown>) => string)(val, row)
                      : String(val)
            return str.toLowerCase().includes(q)
        })
    })
})

const totalRecords = computed(() => filteredRows.value.length)

function escapeCsvCell(value: string): string {
    if (/[",\n\r]/.test(value)) return `"${value.replace(/"/g, '""')}"`
    return value
}

/**
 * Export CSV: si maxRowsPerCsvFile > 0 y hay más filas, se empaquetan todas las partes
 * en un único archivo ZIP (una sola descarga). Incluye LEEME.txt con la lista de partes.
 */
async function exportCSV() {
    if (filteredRows.value.length === 0) return
    const rows = filteredRows.value
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
