<template>
    <div class="text-slate-900 w-full min-w-0">
        <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3 w-full min-w-0 sm:w-auto">
                <div class="flex flex-wrap items-center gap-2 w-full min-w-0 sm:w-auto">
                    <slot name="toolbar" :table="table" />
                </div>
                <div v-if="enableGlobalFilter" class="flex items-center gap-2 sm:ml-0 text-sm text-gray-600 shrink-0">
                    <span class="text-gray-500 whitespace-nowrap">Resultados: <span class="font-medium">{{ filteredTotal
                    }}</span></span>
                </div>
            </div>
            <div class="flex flex-col gap-2 w-full min-w-0 sm:flex-row sm:items-center sm:w-auto sm:shrink-0">
                <template v-if="enableGlobalFilter">
                    <label for="table-search" class="sr-only">Buscar</label>
                    <InputText id="table-search" v-model="globalSearch" type="search"
                        class="w-full min-w-0 sm:w-52 lg:w-64" placeholder="Buscar..." />
                </template>
                <template v-if="selectable">
                    <span class="text-sm text-gray-600 whitespace-nowrap">{{ selectedCount }} seleccionados</span>
                </template>
            </div>
        </div>

        <div class="w-full min-w-0 overflow-auto rounded-md shadow-sm border border-gray-200/80 bg-white touch-pan-x touch-pan-y"
            :style="scrollWrapperStyle">
            <table class="table-auto divide-y divide-gray-200 border-collapse w-full" role="table" :aria-busy="loading"
                style="min-width: max(100%, max-content)">
                <thead class="relative">
                    <tr v-for="(headerGroup, headerGroupIndex) in table.getHeaderGroups()" :key="headerGroup.id">
                        <th v-if="hasExpandedSlot" class="py-2 px-2 sm:py-3 sm:px-3 text-left w-10 sm:w-12 shrink-0"
                            :class="showStickyHeader ? 'sticky top-0 z-10 bg-white shadow-[0_1px_0_0_rgba(0,0,0,0.06)]' : ''">
                        </th>
                        <th v-if="selectable" class="py-2 px-2 sm:py-3 sm:px-3 text-left w-10 sm:w-12 shrink-0"
                            :class="showStickyHeader ? 'sticky top-0 z-10 bg-white shadow-[0_1px_0_0_rgba(0,0,0,0.06)]' : ''">
                            <Checkbox :model-value="table.getIsAllPageRowsSelected()" binary
                                :indeterminate="table.getIsSomePageRowsSelected()"
                                :disabled="loading || table.getRowModel().rows.length === 0"
                                aria-label="Seleccionar todas las filas visibles"
                                @update:model-value="(v) => table.toggleAllPageRowsSelected(!!v)" />
                        </th>
                        <th v-for="header in headerGroup.headers" :key="header.id" :colspan="header.colSpan"
                            class="py-2 px-2 sm:py-3 sm:px-4 text-left text-xs sm:text-sm font-semibold text-gray-700 whitespace-nowrap"
                            :class="[
                                header.column.getCanSort() ? 'cursor-pointer select-none' : '',
                                showStickyHeader ? 'sticky top-0 z-10 bg-white shadow-[0_1px_0_0_rgba(0,0,0,0.06)]' : '',
                            ]" @click="onHeaderClick(header)">
                            <div class="flex items-center gap-2">
                                <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
                                <span v-if="header.column.getCanSort()" class="ml-1 text-xs text-gray-400">
                                    {{ sortIndicator(header.column.getIsSorted()) }}
                                </span>
                            </div>
                        </th>
                        <th v-if="hasRowActions && headerGroupIndex === table.getHeaderGroups().length - 1"
                            class="py-2 px-2 sm:py-3 sm:px-4 text-left text-xs sm:text-sm font-semibold text-gray-700 w-20 sm:w-28 shrink-0"
                            :class="showStickyHeader ? 'sticky top-0 z-10 bg-white shadow-[0_1px_0_0_rgba(0,0,0,0.06)]' : ''">
                            {{ rowActionsLabel }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <!-- Skeleton loading -->
                    <template v-if="loading && skeletonLoading">
                        <tr v-for="i in skeletonCount" :key="'skeleton-' + i" class="animate-pulse">
                            <td v-if="hasExpandedSlot" class="py-2 px-2 sm:py-3 sm:px-3 w-10 sm:w-12 shrink-0">
                                <Skeleton shape="circle" size="1.25rem" />
                            </td>
                            <td v-if="selectable" class="py-2 px-2 sm:py-3 sm:px-3 w-10 sm:w-12 shrink-0">
                                <Skeleton shape="rectangle" class="h-5 w-5" />
                            </td>
                            <td v-for="col in table.getAllLeafColumns()" :key="col.id"
                                class="py-2 px-2 sm:py-3 sm:px-4">
                                <Skeleton width="80%" height="1.25rem" />
                            </td>
                            <td v-if="hasRowActions" class="py-2 px-2 sm:py-3 sm:px-4 w-20 sm:w-28 shrink-0">
                                <Skeleton width="4rem" height="1.5rem" />
                            </td>
                        </tr>
                    </template>
                    <tr v-else-if="loading" :key="'loading'">
                        <td :colspan="table.getAllLeafColumns().length + extraColumns"
                            class="py-6 sm:py-8 px-3 sm:px-4 text-center text-xs sm:text-sm text-gray-500">
                            Cargando...
                        </td>
                    </tr>
                    <tr v-else-if="table.getRowModel().rows.length === 0">
                        <td :colspan="table.getAllLeafColumns().length + extraColumns"
                            class="py-6 sm:py-8 px-3 sm:px-4 text-center text-xs sm:text-sm text-gray-500">
                            <slot name="empty">{{ emptyText }}</slot>
                        </td>
                    </tr>
                    <template v-else>
                        <template v-for="row in table.getRowModel().rows" :key="row.id">
                            <tr class="hover:bg-gray-50 transition-colors"
                                :class="rowClick !== 'none' ? 'cursor-pointer' : ''"
                                @click="rowClick !== 'none' ? onRowClick($event, row) : undefined">
                                <td v-if="hasExpandedSlot" class="py-2 px-2 sm:py-3 sm:px-3 align-top shrink-0"
                                    @click.stop>
                                    <button type="button"
                                        class="p-0.5 rounded text-gray-500 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300"
                                        :aria-label="row.getIsExpanded() ? 'Contraer' : 'Expandir'"
                                        @click="row.toggleExpanded()">
                                        <span class="text-sm font-medium">{{ row.getIsExpanded() ? '−' : '+' }}</span>
                                    </button>
                                </td>
                                <td v-if="selectable" class="py-2 px-2 sm:py-3 sm:px-3 align-top shrink-0" @click.stop>
                                    <Checkbox :model-value="row.getIsSelected()" binary :disabled="loading"
                                        :aria-label="`Seleccionar fila ${row.id}`"
                                        @update:model-value="(v) => row.toggleSelected(!!v)" />
                                </td>
                                <td v-for="cell in row.getVisibleCells()" :key="cell.id"
                                    class="py-2 px-2 sm:py-3 sm:px-4 align-top text-xs sm:text-sm text-gray-700 whitespace-nowrap"
                                    @click="onCellClick($event, row, cell)">
                                    <slot name="cell" :cell="cell" :row="row" :value="cell.getValue()"
                                        :is-editing="isCellEditing(row.id, cell.column.id)"
                                        :editing-value="editingValue"
                                        :start-edit="() => startEdit(row, cell)"
                                        :save="() => saveEdit(row, cell.column.id)" :cancel="cancelEdit"
                                        :meta="getCellMeta(cell)">
                                        <template
                                            v-if="getCellMeta(cell).editable && isCellEditing(row.id, cell.column.id)">
                                            <div class="flex items-center gap-1 min-w-0">
                                                <Select v-if="getEditType(cell) === 'select'"
                                                    :model-value="editingValue"
                                                    @update:model-value="(v) => (editingValue = v as string | number | null)"
                                                    :options="getCellMeta(cell).editOptions" option-label="label"
                                                    option-value="value" class="flex-1 min-w-0 text-xs" size="small"
                                                    :placeholder="getCellMeta(cell).editPlaceholder"
                                                    @keydown.enter="saveEdit(row, cell.column.id)"
                                                    @keydown.esc="cancelEdit" />
                                                <InputNumber v-else-if="getEditType(cell) === 'number'"
                                                    :model-value="typeof editingValue === 'number' ? editingValue : null"
                                                    @update:model-value="(v) => (editingValue = typeof v === 'number' ? v : null)"
                                                    class="flex-1 min-w-0 text-xs"
                                                    input-class="w-full min-w-0 text-xs p-1"
                                                    :placeholder="getCellMeta(cell).editPlaceholder"
                                                    @keydown.enter="saveEdit(row, cell.column.id)"
                                                    @keydown.esc="cancelEdit" />
                                                <DatePicker v-else-if="getEditType(cell) === 'date'"
                                                    :model-value="getDatePickerValue(editingValue)"
                                                    @update:model-value="updateDateEditValue"
                                                    class="flex-1 min-w-0 text-xs"
                                                    input-class="w-full min-w-0 text-xs p-1"
                                                    date-format="yy-mm-dd"
                                                    :placeholder="getCellMeta(cell).editPlaceholder"
                                                    @keydown.enter="saveEdit(row, cell.column.id)"
                                                    @keydown.esc="cancelEdit" />
                                                <InputText v-else
                                                    :model-value="editingValue != null ? String(editingValue) : ''"
                                                    @update:model-value="(v) => (editingValue = (v as string) || null)"
                                                    class="flex-1 min-w-0 text-xs p-1"
                                                    :placeholder="getCellMeta(cell).editPlaceholder"
                                                    @keydown.enter="saveEdit(row, cell.column.id)"
                                                    @keydown.esc="cancelEdit" />
                                                <button type="button"
                                                    class="p-1 text-green-600 hover:bg-green-50 rounded"
                                                    aria-label="Guardar"
                                                    @click="saveEdit(row, cell.column.id)">✓</button>
                                                <button type="button"
                                                    class="p-1 text-gray-500 hover:bg-gray-100 rounded"
                                                    aria-label="Cancelar" @click="cancelEdit">✕</button>
                                            </div>
                                        </template>
                                        <FlexRender v-else :render="cell.column.columnDef.cell"
                                            :props="cell.getContext()" />
                                    </slot>
                                </td>
                                <td v-if="hasRowActions"
                                    class="py-2 px-2 sm:py-3 sm:px-4 align-top text-xs sm:text-sm text-gray-700 shrink-0"
                                    @click.stop>
                                    <slot name="row-actions" :row="row" :original="row.original" :table="table" />
                                </td>
                            </tr>
                            <!-- Fila expandida: subtabla, timeline, notas, JSON raw, etc. -->
                            <tr v-if="row.getIsExpanded() && hasExpandedSlot" class="bg-gray-50/80">
                                <td :colspan="table.getAllLeafColumns().length + extraColumns"
                                    class="py-3 px-4 align-top border-b border-gray-200">
                                    <slot name="expanded-row" :row="row" :original="row.original" :table="table" />
                                </td>
                            </tr>
                        </template>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <slot name="pagination" :table="table" />
        </div>

        <!-- Drawer lateral (modo analista): ver registro sin salir de la tabla -->
        <PrimeSidebar v-model:visible="drawerVisible" position="right" class="w-full sm:max-w-lg lg:max-w-xl"
            :modal="true" :dismissable="true" @hide="drawerRow = null">
            <template #header>
                <span class="font-semibold">{{ drawerTitle }}</span>
            </template>
            <div v-if="drawerRow" class="p-2">
                <slot name="drawer" :row="drawerRow" :original="drawerRow.original" :close="closeDrawer" />
            </div>
        </PrimeSidebar>
    </div>
</template>

<script setup lang="ts" generic="TData extends Record<string, any>">
import { computed, ref, watch, useSlots, onBeforeUnmount } from 'vue'
import Checkbox from 'primevue/checkbox'
import DatePicker from 'primevue/datepicker'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import PrimeSidebar from 'primevue/sidebar'
import Skeleton from 'primevue/skeleton'
import {
    type ColumnDef,
    type Cell,
    type Header,
    type Row,
    type FilterFn,
    type PaginationState,
    type SortingState,
    type ColumnFiltersState,
    type RowSelectionState,
    FlexRender,
    getCoreRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    getFilteredRowModel,
    type ExpandedState,
    getExpandedRowModel,
    useVueTable,
} from '@tanstack/vue-table'
import Select from 'primevue/select'
import { toDateOnly, toYYYYMMDD, type DateRangeValue } from './dateFilterUtils'
import type { ColumnMetaBase, RowClickMode } from './tableMeta'

interface Props<TData> {
    data: TData[]
    columns: ColumnDef<TData, any>[]
    getRowId?: (row: TData, index: number) => string
    enableSorting?: boolean
    enablePagination?: boolean
    pageSize?: number
    emptyText?: string
    loading?: boolean
    /** Si true, muestra filas skeleton en lugar del mensaje "Cargando..." (número de filas = skeletonRows). */
    skeletonLoading?: boolean
    /** Número de filas skeleton cuando loading y skeletonLoading son true (por defecto pageSize o 10). */
    skeletonRows?: number
    selectable?: boolean
    enableGlobalFilter?: boolean
    enableColumnFilters?: boolean
    showStickyHeader?: boolean
    scrollMaxHeight?: string
    rowActionsLabel?: string
    /** Comportamiento al hacer clic en una fila: 'none' | 'expand' | 'drawer' | 'custom'. */
    rowClick?: RowClickMode
    /** Título del drawer cuando rowClick es 'drawer'. */
    drawerTitle?: string
}

const props = withDefaults(defineProps<Props<TData>>(), {
    enableSorting: false,
    enablePagination: false,
    pageSize: 10,
    emptyText: 'Sin datos',
    loading: false,
    skeletonLoading: false,
    skeletonRows: 0,
    selectable: false,
    enableGlobalFilter: false,
    enableColumnFilters: false,
    showStickyHeader: true,
    scrollMaxHeight: '70vh',
    rowActionsLabel: 'Acciones',
    rowClick: 'none',
    drawerTitle: 'Detalle',
})

const emit = defineEmits<{
    (e: 'row-click', payload: { row: Row<TData>; original: TData }): void
    (e: 'update:cell', payload: { rowId: string; columnId: string; value: unknown; oldValue: unknown; original: TData }): void
}>()

const slots = useSlots()

const sorting = ref<SortingState>([])
const pagination = ref<PaginationState>({ pageIndex: 0, pageSize: props.pageSize })
const columnFilters = ref<ColumnFiltersState>([])
const globalFilter = ref('')
const rowSelection = ref<RowSelectionState>({})
const expanded = ref<ExpandedState>({})
const drawerVisible = ref(false)
const drawerRow = ref<Row<TData> | null>(null)
const editingCell = ref<{ rowId: string; columnId: string } | null>(null)
const editingValue = ref<string | number | null>(null)

const numberRangeFilter: FilterFn<any> = (row, columnId, value) => {
    const raw = row.getValue(columnId)
    const rowValue = typeof raw === 'number' ? raw : Number(raw)
    if (!Number.isFinite(rowValue)) return false
    const min = value?.min ?? value?.[0]
    const max = value?.max ?? value?.[1]
    const parsedMin = min === undefined || min === '' ? undefined : Number(min)
    const parsedMax = max === undefined || max === '' ? undefined : Number(max)
    if (parsedMin !== undefined && rowValue < parsedMin) return false
    if (parsedMax !== undefined && rowValue > parsedMax) return false
    return true
}

const dateRangeFilter: FilterFn<any> = (row, columnId, value) => {
    const raw = row.getValue(columnId)
    const rowDate = toDateOnly(raw as string | Date | number | null)
    if (!rowDate) return false

    let fromStr: string | undefined
    let toStr: string | undefined
    if (Array.isArray(value)) {
        fromStr = value[0]
        toStr = value[1]
    } else if (value && typeof value === 'object' && 'from' in value && 'to' in value) {
        fromStr = (value as DateRangeValue).from
        toStr = (value as DateRangeValue).to
    } else {
        fromStr = undefined
        toStr = undefined
    }
    const fromDate = fromStr ? toDateOnly(fromStr) : undefined
    const toDate = toStr ? toDateOnly(toStr) : undefined

    let from = fromDate
    let to = toDate
    if (from && to && from.getTime() > to.getTime()) [from, to] = [to, from]

    if (from && rowDate.getTime() < from.getTime()) return false
    if (to && rowDate.getTime() > to.getTime()) return false
    return true
}

const table = useVueTable({
    get data() {
        return props.data
    },
    columns: props.columns,
    getRowId: props.getRowId,
    state: {
        get sorting() {
            return sorting.value
        },
        get pagination() {
            return pagination.value
        },
        get columnFilters() {
            return columnFilters.value
        },
        get globalFilter() {
            return globalFilter.value
        },
        get rowSelection() {
            return rowSelection.value
        },
        get expanded() {
            return expanded.value
        },
    },
    onExpandedChange: (updater) => {
        expanded.value = typeof updater === 'function' ? updater(expanded.value) : updater
    },
    getExpandedRowModel: getExpandedRowModel(),
    onSortingChange: (updater) => {
        sorting.value = typeof updater === 'function' ? updater(sorting.value) : updater
    },
    onPaginationChange: (updater) => {
        pagination.value = typeof updater === 'function' ? updater(pagination.value) : updater
    },
    onColumnFiltersChange: (updater) => {
        columnFilters.value = typeof updater === 'function' ? updater(columnFilters.value) : updater
    },
    onGlobalFilterChange: (updater) => {
        globalFilter.value = typeof updater === 'function' ? updater(globalFilter.value) : updater
    },
    onRowSelectionChange: (updater) => {
        rowSelection.value = typeof updater === 'function' ? updater(rowSelection.value) : updater
    },
    getCoreRowModel: getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getSortedRowModel: props.enableSorting ? getSortedRowModel() : undefined,
    getPaginationRowModel: props.enablePagination ? getPaginationRowModel() : undefined,
    filterFns: {
        includesString: (row, columnId, filterValue: string) => {
            if (filterValue == null || filterValue === '') return true
            const rowValue = String(row.getValue(columnId) ?? '')
            return rowValue.toLowerCase().includes(String(filterValue).toLowerCase())
        },
        equalsString: (row, columnId, filterValue) => {
            if (filterValue == null || filterValue === '') return true
            return String(row.getValue(columnId)) === String(filterValue)
        },
        inNumberRange: numberRangeFilter,
        numberRange: numberRangeFilter,
        dateRange: dateRangeFilter,
    },
    enableGlobalFilter: props.enableGlobalFilter,
    globalFilterFn: 'includesString',
    enableSorting: props.enableSorting,
    enableColumnFilters: props.enableColumnFilters,
    enableRowSelection: props.selectable,
})

const globalSearch = ref(globalFilter.value)
let debounceTimer: ReturnType<typeof setTimeout> | undefined
watch(
    () => globalSearch.value,
    (v) => {
        if (debounceTimer) clearTimeout(debounceTimer)
        debounceTimer = setTimeout(() => {
            globalFilter.value = v ?? ''
            debounceTimer = undefined
        }, 250)
    }
)

const hasRowActions = computed(() => !!slots['row-actions'])
const hasExpandedSlot = computed(() => !!slots['expanded-row'])
const extraColumns = computed(
    () => (props.selectable ? 1 : 0) + (hasRowActions.value ? 1 : 0) + (hasExpandedSlot.value ? 1 : 0)
)
const skeletonCount = computed(() => {
    if (props.skeletonRows > 0) return props.skeletonRows
    return typeof props.pageSize === 'number' && props.pageSize > 0 ? props.pageSize : 10
})

function closeDrawer() {
    drawerVisible.value = false
    drawerRow.value = null
}

function onRowClick(event: MouseEvent, row: Row<TData>) {
    if (shouldIgnoreRowClick(event)) return
    if (props.rowClick === 'expand' && hasExpandedSlot.value) {
        row.toggleExpanded()
        return
    }
    if (props.rowClick === 'drawer') {
        drawerRow.value = row
        drawerVisible.value = true
        return
    }
    if (props.rowClick === 'custom') {
        emit('row-click', { row, original: row.original })
    }
}

function isCellEditing(rowId: string, columnId: string) {
    const c = editingCell.value
    return c !== null && c.rowId === rowId && c.columnId === columnId
}

function startEdit(row: Row<TData>, cell: Cell<TData, unknown>) {
    editingCell.value = { rowId: row.id, columnId: cell.column.id }
    editingValue.value = normalizeEditValue(cell.getValue(), getEditType(cell))
}

function onCellClick(event: MouseEvent, row: Row<TData>, cell: Cell<TData, unknown>) {
    const meta = getCellMeta(cell)
    if (!meta.editable) return
    event.stopPropagation()
    startEdit(row, cell)
}

function saveEdit(row: Row<TData>, columnId: string) {
    if (editingCell.value?.rowId !== row.id || editingCell.value?.columnId !== columnId) return
    const oldValue = row.getValue(columnId)
    emit('update:cell', {
        rowId: row.id,
        columnId,
        value: editingValue.value,
        oldValue,
        original: row.original,
    })
    editingCell.value = null
    editingValue.value = null
}

function cancelEdit() {
    editingCell.value = null
    editingValue.value = null
}

function getCellMeta(cell: { column: { columnDef: { meta?: unknown } } }) {
    const meta = cell.column.columnDef.meta as ColumnMetaBase | undefined
    return (meta ?? {}) as ColumnMetaBase
}

type EditType = 'text' | 'number' | 'date' | 'select'

function getEditType(cell: { column: { columnDef: { meta?: unknown } } }) {
    const meta = getCellMeta(cell)
    if (meta.editOptions?.length) return 'select'
    return (meta.editType ?? 'text') as EditType
}

function normalizeEditValue(value: unknown, editType: EditType) {
    if (editType === 'number') {
        if (value === undefined || value === null || value === '') return null
        const parsed = typeof value === 'number' ? value : Number(value)
        return Number.isFinite(parsed) ? parsed : null
    }
    if (editType === 'date') {
        const date = toDateOnly(value as string | Date | number | null | undefined)
        return date ? toYYYYMMDD(date) : null
    }
    if (value === undefined || value === null) return null
    return typeof value === 'string' || typeof value === 'number' ? value : String(value)
}

function getDatePickerValue(value: string | number | null) {
    if (value === null || value === undefined || value === '') return null
    return toDateOnly(value as string | Date | number | null | undefined) ?? null
}

function updateDateEditValue(value: Date | Date[] | (Date | null)[] | null | undefined) {
    if (!value) {
        editingValue.value = null
        return
    }
    const date = Array.isArray(value) ? value[0] : value
    editingValue.value = date ? toYYYYMMDD(date) : null
}
const selectedCount = computed(() => Object.values(rowSelection.value).filter(Boolean).length)
const filteredTotal = computed(() => table.getFilteredRowModel().rows.length)

const scrollWrapperStyle = computed(() => ({
    WebkitOverflowScrolling: 'touch' as const,
    ...(props.scrollMaxHeight ? { maxHeight: props.scrollMaxHeight } : {}),
}))

function onHeaderClick(header: Header<TData, unknown>) {
    if (!props.enableSorting || !header.column.getCanSort()) return
    header.column.toggleSorting()
}

function shouldIgnoreRowClick(event: MouseEvent) {
    const target = event.target as HTMLElement | null
    if (!target) return false
    const selector = [
        'button',
        'a',
        'input',
        'select',
        'textarea',
        'label',
        '[role="button"]',
        '[role="link"]',
        '[contenteditable="true"]',
        '[data-stop-row-click]',
    ].join(',')
    return !!target.closest(selector)
}

function sortIndicator(state: false | 'asc' | 'desc') {
    if (state === 'asc') return '↑'
    if (state === 'desc') return '↓'
    return ''
}

watch(
    () => props.pageSize,
    (v) => {
        if (typeof v === 'number' && table.getState().pagination.pageSize !== v) {
            table.setPageSize?.(v)
            table.setPageIndex?.(0)
        }
    },
    { immediate: true }
)

watch(globalFilter, () => {
    if (props.enablePagination) table.setPageIndex?.(0)
})

watch(
    () => props.data,
    () => {
        if (props.selectable) table.resetRowSelection?.()
        if (props.enablePagination) table.setPageIndex?.(0)
    },
    { immediate: true }
)

watch(
    () => table.getFilteredRowModel().rows.length,
    () => {
        ensurePageInRange()
    }
)

onBeforeUnmount(() => {
    if (debounceTimer) clearTimeout(debounceTimer)
})

function ensurePageInRange() {
    if (!props.enablePagination) return
    const pageCount = Math.max(table.getPageCount(), 1)
    const lastIndex = Math.max(pageCount - 1, 0)
    const current = table.getState().pagination.pageIndex ?? 0
    if (current > lastIndex) table.setPageIndex?.(lastIndex)
}

defineExpose({
    table,
    selectedCount,
    totalRows: computed(() => table.getPreFilteredRowModel().rows.length),
    filteredTotal,
    closeDrawer,
    openDrawer: (row: Row<TData>) => {
        drawerRow.value = row
        drawerVisible.value = true
    },
})
</script>
