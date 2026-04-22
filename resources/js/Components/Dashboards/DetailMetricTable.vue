<template>
    <div class="detail-metric-table overflow-hidden rounded-xl border border-[var(--th-border)] bg-[var(--th-input-bg)] backdrop-blur-sm shadow-sm">
        <div v-if="showToolbar"
            class="flex flex-wrap items-center gap-3 border-b border-[var(--th-border)] bg-[var(--th-input-bg)] px-4 py-3">
            <span v-if="showSearch" class="relative flex-1 min-w-[220px] max-w-sm">
                <Icon icon="heroicons:magnifying-glass"
                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[color:var(--th-text-muted)]"
                    aria-hidden="true" />
                <InputText v-model="rawSearchText" :placeholder="searchPlaceholder"
                    class="w-full rounded-lg border-[var(--th-input-border)] bg-[var(--th-input-bg)] pl-9 pr-8 text-sm placeholder:italic shadow-sm"
                    aria-label="Buscar en la tabla" />
                <button v-if="allowClearSearch && rawSearchText" type="button"
                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1 text-[color:var(--th-text-muted)] hover:bg-[var(--th-item-hover-bg)]"
                    @click="clearSearch" aria-label="Limpiar búsqueda">
                    <Icon icon="heroicons:x-mark" class="h-4 w-4" />
                </button>
            </span>

            <div class="flex items-center gap-3">
                <span v-if="showSearchMatches && searchText"
                    class="rounded-full border border-[var(--th-border)] bg-[var(--th-item-active-bg)] px-2.5 py-1 text-xs font-medium text-[color:var(--th-item-active-color)]">
                    {{ totalRecords }} de {{ rows.length }} coincidencias
                </span>
                <span v-if="showProcessingStatus && isProcessing" class="text-xs text-[color:var(--th-text-muted)]">
                    Procesando...
                </span>
                <Button v-if="showExportButton" label="Exportar CSV" icon="pi pi-download" size="small" outlined
                    :disabled="totalRecords === 0" severity="secondary" class="shrink-0" :loading="exportingCsv"
                    @click="exportCSV" />
            </div>
        </div>

        <div class="overflow-x-auto" :style="tableViewportStyle">
            <table class="min-w-full text-sm border-separate border-spacing-0">
                <thead :class="headerClass">
                    <tr>
                        <th v-for="col in columns" :key="col.key"
                            class="px-4 text-left text-xs uppercase tracking-wide font-semibold text-[color:var(--th-text-secondary)] border-b border-[var(--th-border)]/80"
                            :class="[
                                headerCellClass(col),
                                {
                                    'cursor-pointer select-none hover:text-[color:var(--th-item-active-color)]':
                                        col.sortable !== false,
                                },
                            ]" :aria-sort="col.sortable !== false
                                ? sorting?.key === col.key
                                    ? sorting.desc
                                        ? 'descending'
                                        : 'ascending'
                                    : 'none'
                                : undefined
                                " @click="col.sortable !== false && toggleSort(col.key)">
                            <div class="flex items-center gap-2">
                                <span>{{ col.header }}</span>
                                <span v-if="col.sortable !== false" class="text-xs text-[color:var(--th-text-muted)]">
                                    {{ sortIndicator(col.key) }}
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody v-if="normalizedPageRows.length > 0">
                    <tr v-for="(row, rowIndex) in normalizedPageRows" :key="rowIndex"
                        class="border-b border-[var(--th-border)]/60 transition-colors"
                        :class="rowClass(rowIndex)">
                        <td v-for="col in columns" :key="col.key"
                            class="px-4 text-[color:var(--th-text-primary)]" :class="[
                                bodyCellClass,
                                col.class,
                                { 'tabular-nums font-semibold': col.numeric },
                            ]">
                            {{ formatCellValue(row, col) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="normalizedPageRows.length === 0" class="px-4 py-8">
            <EmptyState :title="searchText
                ? 'Ningún registro coincide con la búsqueda'
                : 'No hay datos para mostrar'
                " :description="searchText
                    ? 'Prueba con otros términos.'
                    : 'Selecciona una métrica o filtra los datos.'
                    " icon="heroicons:magnifying-glass" />
        </div>

        <div v-if="showFooter"
            class="flex flex-wrap items-center justify-between gap-3 border-t border-[var(--th-border)] bg-[var(--th-input-bg)] px-4 py-3">
            <p v-if="showRecordCount" class="text-sm text-[color:var(--th-text-secondary)]">
                <span class="font-semibold tabular-nums text-[color:var(--th-text-primary)]">{{ totalRecords }}</span>
                {{ totalRecords === 1 ? 'registro' : 'registros' }}
                <span v-if="searchText" class="ml-1 text-[color:var(--th-text-muted)]">(filtrados)</span>
            </p>
            <div v-if="showPagination" class="flex items-center gap-2 text-xs">
                <button type="button"
                    class="rounded-md border border-[var(--th-border)] px-2 py-1 text-[color:var(--th-text-secondary)] disabled:opacity-40 hover:bg-[var(--th-item-hover-bg)]"
                    :disabled="pageIndex === 0 || isProcessing" @click="pageIndex = Math.max(0, pageIndex - 1)">
                    Anterior
                </button>
                <span v-if="showPageIndicator" class="text-[color:var(--th-text-muted)]">
                    Página {{ currentPage }} de {{ totalPages }}
                </span>
                <button type="button"
                    class="rounded-md border border-[var(--th-border)] px-2 py-1 text-[color:var(--th-text-secondary)] disabled:opacity-40 hover:bg-[var(--th-item-hover-bg)]"
                    :disabled="pageIndex >= totalPages - 1 || isProcessing"
                    @click="pageIndex = Math.min(totalPages - 1, pageIndex + 1)">
                    Siguiente
                </button>
                <template v-if="showRowsPerPageSelector">
                    <label class="ml-2 text-[color:var(--th-text-muted)]">Filas:</label>
                    <select v-model.number="pageSize"
                        class="rounded-md border border-[var(--th-border)] bg-[var(--th-input-bg)] px-2 py-1 text-[color:var(--th-text-secondary)]"
                        :disabled="isProcessing">
                        <option v-for="opt in rowsPerPageOptions" :key="opt" :value="opt">
                            {{ opt }}
                        </option>
                    </select>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, toRef } from 'vue'
import { Icon } from '@iconify/vue'
import EmptyState from '@/Components/EmptyState.vue'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import type { DetailMetricColumn } from './detail-metric-table/types'
import { useDetailMetricTableWorker } from './detail-metric-table/useDetailMetricTableWorker'
import { buildCsvChunk, downloadBlob } from './detail-metric-table/csvUtils'
export type { DetailMetricColumn } from './detail-metric-table/types'

const props = withDefaults(
    defineProps<{
        /** Filas genéricas (cualquier objeto con claves que coincidan con columns[].key) */
        rows: Record<string, unknown>[];
        /** Definición de columnas */
        columns: DetailMetricColumn[];
        /** Placeholder del campo de búsqueda */
        searchPlaceholder?: string;
        /** Etiqueta para el nombre del archivo al exportar */
        exportLabel?: string;
        /** Filas por página por defecto */
        rowsPerPage?: number;
        /** Opciones del selector de filas por página */
        rowsPerPageOptions?: number[];
        /** Ajustes visuales/UX configurables */
        showSearch?: boolean;
        showExportButton?: boolean;
        showSearchMatches?: boolean;
        showProcessingStatus?: boolean;
        allowClearSearch?: boolean;
        showFooter?: boolean;
        showRecordCount?: boolean;
        showPagination?: boolean;
        showPageIndicator?: boolean;
        showRowsPerPageSelector?: boolean;
        stickyHeader?: boolean;
        stripedRows?: boolean;
        rowHover?: boolean;
        compact?: boolean;
        maxBodyHeight?: string;
        /**
         * Si > 0, export CSV se divide en varios archivos de hasta esta cantidad
         * de filas cada uno y se empaqueta en un único ZIP.
         */
        maxRowsPerCsvFile?: number
    }>(),
    {
        searchPlaceholder: 'Buscar en la tabla…',
        rowsPerPage: 10,
        rowsPerPageOptions: () => [10, 15, 25, 50, 100],
        showSearch: true,
        showExportButton: true,
        showSearchMatches: true,
        showProcessingStatus: true,
        allowClearSearch: true,
        showFooter: true,
        showRecordCount: true,
        showPagination: true,
        showPageIndicator: true,
        showRowsPerPageSelector: true,
        stickyHeader: true,
        stripedRows: true,
        rowHover: true,
        compact: false,
        maxBodyHeight: '62vh',
        maxRowsPerCsvFile: 0,
    }
)

const {
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
} = useDetailMetricTableWorker({
    rows: toRef(props, 'rows'),
    columns: toRef(props, 'columns'),
    initialRowsPerPage: props.rowsPerPage,
})

const exportingCsv = ref(false)

const normalizedPageRows = computed<Record<string, unknown>[]>(() => pageRows.value.filter((row) => !!row))
const showToolbar = computed(() => props.showSearch || props.showExportButton || props.showSearchMatches || props.showProcessingStatus)
const tableViewportStyle = computed(() => ({ maxHeight: props.maxBodyHeight }))
const headerClass = computed(() =>
    props.stickyHeader
        ? 'sticky top-0 z-10 border-b border-[var(--th-border)] bg-[var(--th-input-bg)]'
        : 'border-b border-[var(--th-border)] bg-[var(--th-input-bg)]'
)
const bodyCellClass = computed(() => (props.compact ? 'py-2' : 'py-2.5'))

function headerCellClass(col: DetailMetricColumn): string {
    const isSorted = sorting.value?.key === col.key
    const basePadding = props.compact ? 'py-2.5' : 'py-3'
    if (!isSorted) return basePadding
    return `${basePadding} bg-[var(--th-item-hover-bg)]/30 text-[color:var(--th-item-active-color)]`
}

function rowClass(rowIndex: number): string {
    const classes: string[] = []
    if (props.stripedRows) {
        classes.push(rowIndex % 2 === 0 ? 'bg-[var(--th-input-bg)]' : 'bg-[var(--th-item-hover-bg)]/20')
    }
    if (props.rowHover) {
        classes.push('hover:bg-[var(--th-item-hover-bg)]/35')
    }
    return classes.join(' ')
}

function clearSearch() {
    rawSearchText.value = ''
}

function getCellValue(row: Record<string, unknown>, key: string): unknown {
    return row[key]
}

function formatCellValue(row: Record<string, unknown>, col: DetailMetricColumn): string {
    const raw = getCellValue(row, col.key)
    if (col.format) return col.format(raw, row as Record<string, unknown>)
    return raw == null ? '' : String(raw)
}

async function exportCSV() {
    if (totalRecords.value === 0) return
    exportingCsv.value = true

    try {
        const indexes = await requestExportIndexes()
        const rows = indexes.map((i) => props.rows[i]).filter((row): row is Record<string, unknown> => !!row)
        const maxPerFile = props.maxRowsPerCsvFile ?? 0
        const baseName = `detalle-${(props.exportLabel || 'datos')
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')}-${new Date().toISOString().slice(0, 10)}`

        if (maxPerFile > 0 && rows.length > maxPerFile) {
            const JSZip = (await import('jszip')).default
            const zip = new JSZip()
            const totalParts = Math.ceil(rows.length / maxPerFile)
            const partNames: string[] = []

            for (let part = 0; part < totalParts; part++) {
                const start = part * maxPerFile
                const chunk = rows.slice(start, start + maxPerFile)
                const csv = buildCsvChunk(chunk, props.columns, getCellValue)
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
        } else {
            const csv = buildCsvChunk(rows, props.columns, getCellValue)
            downloadBlob(
                new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8' }),
                `${baseName}.csv`
            )
        }
    } finally {
        exportingCsv.value = false
    }
}
</script>
