<template>
    <div
        class="detail-metric-table overflow-hidden rounded-xl border border-[var(--th-border)] bg-[var(--th-input-bg)] shadow-sm"
        role="region"
        :aria-label="tableRegionLabel"
        :aria-busy="isProcessing ? 'true' : 'false'"
    >
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
                <span v-if="enableRowSelection && showSelectionCount" class="text-xs text-[color:var(--th-text-muted)]">
                    {{ selectedCount }} seleccionados
                </span>
                <button
                    v-if="enableRowSelection && showSelectAllFilteredButton && totalRecords > 0"
                    type="button"
                    class="shrink-0 rounded-md border border-[var(--th-border)] px-2 py-1 text-xs font-medium text-[color:var(--th-item-active-color)] transition-colors hover:bg-[var(--th-item-hover-bg)] disabled:opacity-40"
                    :disabled="isProcessing || selectingAllFiltered"
                    :title="'Incluye todas las páginas; respeta la búsqueda y el orden actuales.'"
                    @click="onSelectAllFiltered"
                >
                    {{ selectingAllFiltered ? 'Seleccionando…' : `Seleccionar todas (${totalRecords.toLocaleString('es')})` }}
                </button>
                <span v-if="showProcessingStatus && isProcessing" class="text-xs text-[color:var(--th-text-muted)]">
                    Procesando...
                </span>
                <slot name="selection-actions" :selected-indexes="selectedIndexesSorted" :clear-selection="clearSelection" />
                <Button v-if="showExportButton" label="Exportar CSV" icon="pi pi-download" size="small" outlined
                    :disabled="totalRecords === 0 || isProcessing || exportingCsv" severity="secondary" class="shrink-0"
                    :loading="exportingCsv" @click="exportCSV" />
            </div>
        </div>

        <div :class="tableScrollClass" :style="tableViewportStyle">
            <table class="min-w-full text-sm border-separate border-spacing-0">
                <caption v-if="tableSummary" class="sr-only">{{ tableSummary }}</caption>
                <thead :class="headerClass">
                    <tr>
                        <th v-if="enableRowSelection" scope="col"
                            class="w-12 px-3 text-left text-xs uppercase tracking-wide font-semibold text-[color:var(--th-text-secondary)] border-b border-[var(--th-border)]/80"
                            :class="selectionHeaderPaddingClass">
                            <Checkbox binary :model-value="isAllPageSelected" :indeterminate="isSomePageSelected"
                                :disabled="isProcessing || pageRowIndexes.length === 0"
                                aria-label="Seleccionar filas visibles"
                                @update:model-value="toggleSelectAllPage" />
                        </th>
                        <th v-for="col in columns" :key="col.key" scope="col"
                            class="px-4 text-xs uppercase tracking-wide font-semibold text-[color:var(--th-text-secondary)] border-b border-[var(--th-border)]/80"
                            :class="[
                                col.numeric ? 'text-end' : 'text-left',
                                headerCellClass(col),
                                {
                                    'cursor-pointer select-none hover:text-[color:var(--th-item-active-color)]':
                                        col.sortable !== false,
                                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--th-item-active-color)] focus-visible:ring-offset-1 focus-visible:ring-offset-[var(--th-input-bg)]':
                                        col.sortable !== false,
                                },
                            ]" :tabindex="col.sortable !== false ? 0 : undefined"
                            :aria-sort="col.sortable !== false
                                ? sorting?.key === col.key
                                    ? sorting.desc
                                        ? 'descending'
                                        : 'ascending'
                                    : 'none'
                                : undefined
                                " @click="col.sortable !== false && toggleSort(col.key)"
                            @keydown.enter.prevent="col.sortable !== false && toggleSort(col.key)"
                            @keydown.space.prevent="col.sortable !== false && toggleSort(col.key)">
                            <div
                                class="flex items-center gap-2"
                                :class="col.numeric ? 'justify-end' : ''"
                            >
                                <span>{{ col.header }}</span>
                                <span v-if="col.sortable !== false" class="text-xs text-[color:var(--th-text-muted)]">
                                    {{ sortIndicator(col.key) }}
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody v-if="normalizedPageRows.length > 0">
                    <tr v-for="(row, rowIndex) in normalizedPageRows" :key="rowKey(rowIndex)"
                        class="border-b border-[var(--th-border)]/60 transition-colors"
                        :class="rowClass(rowIndex, rowDatasetIndex(rowIndex))">
                        <td v-if="enableRowSelection" class="w-12 px-3 align-middle" :class="bodyCellClass" @click.stop>
                            <Checkbox binary :model-value="isRowSelected(rowDatasetIndex(rowIndex))"
                                :disabled="isProcessing"
                                :aria-label="`Seleccionar fila ${rowDatasetIndex(rowIndex) + 1}`"
                                @update:model-value="(v) => toggleRowSelected(rowDatasetIndex(rowIndex), !!v)" />
                        </td>
                        <td v-for="col in columns" :key="col.key"
                            class="px-4 text-[color:var(--th-text-primary)]" :class="[
                                bodyCellClass,
                                col.numeric ? 'text-end tabular-nums font-semibold' : 'text-start',
                                col.class,
                            ]">
                            <template v-if="col.cellRender">
                                <component :is="renderCell(col, row)" />
                            </template>
                            <template v-else>
                                {{ formatCellValue(row, col) }}
                            </template>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="normalizedPageRows.length === 0" class="px-4 py-8" role="status" aria-live="polite">
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
            <div v-if="showPagination" class="flex flex-wrap items-center gap-2 text-xs">
                <button
                    v-if="showFirstLastPageButtons"
                    type="button"
                    class="rounded-md border border-[var(--th-border)] px-2 py-1 text-[color:var(--th-text-secondary)] disabled:opacity-40 hover:bg-[var(--th-item-hover-bg)]"
                    :disabled="pageIndex === 0 || isProcessing"
                    aria-label="Ir a la primera página"
                    @click="goFirstPage"
                >
                    Primera
                </button>
                <button type="button"
                    class="rounded-md border border-[var(--th-border)] px-2 py-1 text-[color:var(--th-text-secondary)] disabled:opacity-40 hover:bg-[var(--th-item-hover-bg)]"
                    :disabled="pageIndex === 0 || isProcessing" aria-label="Página anterior"
                    @click="goPrevPage">
                    Anterior
                </button>
                <span v-if="showPageIndicator" class="min-w-[7.5rem] text-center text-[color:var(--th-text-muted)]">
                    Página {{ currentPage }} de {{ totalPages }}
                </span>
                <button type="button"
                    class="rounded-md border border-[var(--th-border)] px-2 py-1 text-[color:var(--th-text-secondary)] disabled:opacity-40 hover:bg-[var(--th-item-hover-bg)]"
                    :disabled="pageIndex >= totalPages - 1 || isProcessing" aria-label="Página siguiente"
                    @click="goNextPage">
                    Siguiente
                </button>
                <button
                    v-if="showFirstLastPageButtons"
                    type="button"
                    class="rounded-md border border-[var(--th-border)] px-2 py-1 text-[color:var(--th-text-secondary)] disabled:opacity-40 hover:bg-[var(--th-item-hover-bg)]"
                    :disabled="pageIndex >= totalPages - 1 || isProcessing"
                    aria-label="Ir a la última página"
                    @click="goLastPage"
                >
                    Última
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
import { computed, ref, toRef, useSlots } from 'vue'
import { Icon } from '@iconify/vue'
import EmptyState from '@/Components/EmptyState.vue'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Checkbox from 'primevue/checkbox'
import type { DetailMetricTableProps } from './detail-metric-table/detailMetricTableProps'
import { DETAIL_METRIC_TABLE_DEFAULTS } from './detail-metric-table/detailMetricTableProps'
import { useDetailMetricTableWorker } from './detail-metric-table/useDetailMetricTableWorker'
import { formatCellValue, renderCell } from './detail-metric-table/cellUtils'
import { useDetailMetricTableSelection } from './detail-metric-table/useDetailMetricTableSelection'
import { useDetailMetricTableStyles } from './detail-metric-table/useDetailMetricTableStyles'
import { useDetailMetricTableExport } from './detail-metric-table/useDetailMetricTableExport'

export type { DetailMetricColumn } from './detail-metric-table/types'

const props = withDefaults(defineProps<DetailMetricTableProps>(), {
    ...DETAIL_METRIC_TABLE_DEFAULTS,
    rowsPerPageOptions: () => [10, 15, 25, 50, 100],
})

const emit = defineEmits<{
    (e: 'selection-change', value: { indexes: number[]; rows: Record<string, unknown>[] }): void
}>()

const selectedIndexesModel = defineModel<number[]>('selectedIndexes', { default: () => [] })

const tableRegionLabel = computed(() => {
    const s = props.tableSummary?.trim()
    return s || 'Tabla de datos con búsqueda, orden y paginación'
})

const {
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
} = useDetailMetricTableWorker({
    rows: toRef(props, 'rows'),
    columns: toRef(props, 'columns'),
    initialRowsPerPage: props.rowsPerPage,
})

const normalizedPageRows = computed<Record<string, unknown>[]>(() => pageRows.value.filter((row) => !!row))
const pageRowIndexes = computed(() => workerPageIndexes.value)

const slots = useSlots()
/** No invocar el slot aquí: el padre usa props scoped (`{ selectedIndexes }`) y sin argumentos revienta la desestructuración. */
const hasSelectionActionsSlot = computed(() => typeof slots['selection-actions'] === 'function')

const selection = useDetailMetricTableSelection({
    props,
    rows: toRef(props, 'rows'),
    columns: toRef(props, 'columns'),
    pageRowIndexes,
    searchText,
    selectedIndexes: selectedIndexesModel,
    onSelectionChange: (indexes, rows) => emit('selection-change', { indexes, rows }),
})

const styles = useDetailMetricTableStyles({
    props,
    sorting,
    isRowSelected: selection.isRowSelected,
    hasSelectionActionsSlot,
})

const {
    showToolbar,
    tableViewportStyle,
    tableScrollClass,
    headerClass,
    bodyCellClass,
    selectionHeaderPaddingClass,
    headerCellClass,
    rowClass,
} = styles

const {
    selectedIndexesSorted,
    selectedCount,
    isAllPageSelected,
    isSomePageSelected,
    rowDatasetIndex,
    isRowSelected,
    toggleRowSelected,
    toggleSelectAllPage,
    clearSelection,
    selectAllFilteredRows,
} = selection

const selectingAllFiltered = ref(false)

async function onSelectAllFiltered() {
    if (totalRecords.value === 0 || selectingAllFiltered.value) return
    selectingAllFiltered.value = true
    try {
        await selectAllFilteredRows(requestAllFilteredRowIndexes)
    } finally {
        selectingAllFiltered.value = false
    }
}

function rowKey(pageRowIndex: number): string {
    return `row-${rowDatasetIndex(pageRowIndex)}`
}

const { exportingCsv, exportCSV } = useDetailMetricTableExport({
    rows: toRef(props, 'rows'),
    columns: toRef(props, 'columns'),
    totalRecords,
    requestExportIndexes,
    exportLabel: computed(() => props.exportLabel),
    maxRowsPerCsvFile: computed(() => props.maxRowsPerCsvFile ?? 0),
})

function clearSearch() {
    rawSearchText.value = ''
}

function goFirstPage() {
    pageIndex.value = 0
}

function goPrevPage() {
    pageIndex.value = Math.max(0, pageIndex.value - 1)
}

function goNextPage() {
    pageIndex.value = Math.min(totalPages.value - 1, pageIndex.value + 1)
}

function goLastPage() {
    pageIndex.value = Math.max(0, totalPages.value - 1)
}
</script>
