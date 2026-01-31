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
                    <tr v-if="loading">
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
                    <tr v-for="row in table.getRowModel().rows" :key="row.id"
                        class="hover:bg-gray-50 transition-colors">
                        <td v-if="selectable" class="py-2 px-2 sm:py-3 sm:px-3 align-top shrink-0">
                            <Checkbox :model-value="row.getIsSelected()" binary :disabled="loading"
                                :aria-label="`Seleccionar fila ${row.id}`"
                                @update:model-value="(v) => row.toggleSelected(!!v)" />
                        </td>
                        <td v-for="cell in row.getVisibleCells()" :key="cell.id"
                            class="py-2 px-2 sm:py-3 sm:px-4 align-top text-xs sm:text-sm text-gray-700 whitespace-nowrap">
                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                        </td>
                        <td v-if="hasRowActions"
                            class="py-2 px-2 sm:py-3 sm:px-4 align-top text-xs sm:text-sm text-gray-700 shrink-0">
                            <slot name="row-actions" :row="row" :original="row.original" :table="table" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <slot name="pagination" :table="table" />
        </div>
    </div>
</template>

<script setup lang="ts" generic="TData extends Record<string, any>">
import { computed, ref, watch, useSlots } from 'vue'
import Checkbox from 'primevue/checkbox'
import InputText from 'primevue/inputtext'
import {
    type ColumnDef,
    type Header,
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
    useVueTable,
} from '@tanstack/vue-table'
import { toDateOnly, type DateRangeValue } from './dateFilterUtils'

interface Props<TData> {
    data: TData[]
    columns: ColumnDef<TData, any>[]
    getRowId?: (row: TData, index: number) => string
    enableSorting?: boolean
    enablePagination?: boolean
    pageSize?: number
    emptyText?: string
    loading?: boolean
    selectable?: boolean
    enableGlobalFilter?: boolean
    enableColumnFilters?: boolean
    showStickyHeader?: boolean
    /** Altura máxima del área de scroll (ej: '70vh', '500px'). Si no se define, no hay scroll vertical y el sticky no tiene efecto. */
    scrollMaxHeight?: string
    rowActionsLabel?: string
}

const props = withDefaults(defineProps<Props<TData>>(), {
    enableSorting: false,
    enablePagination: false,
    pageSize: 10,
    emptyText: 'Sin datos',
    loading: false,
    selectable: false,
    enableGlobalFilter: false,
    enableColumnFilters: false,
    showStickyHeader: true,
    scrollMaxHeight: '70vh',
    rowActionsLabel: 'Acciones',
})

const slots = useSlots()

const sorting = ref<SortingState>([])
const pagination = ref<PaginationState>({ pageIndex: 0, pageSize: props.pageSize })
const columnFilters = ref<ColumnFiltersState>([])
const globalFilter = ref('')
const rowSelection = ref<RowSelectionState>({})

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
    },
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
const extraColumns = computed(() => (props.selectable ? 1 : 0) + (hasRowActions.value ? 1 : 0))
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
    () => [props.data, table.getFilteredRowModel().rows.length],
    () => {
        if (props.selectable) table.resetRowSelection?.()
        if (props.enablePagination) {
            const pageCount = Math.max(table.getPageCount(), 1)
            const lastIndex = Math.max(pageCount - 1, 0)
            const current = table.getState().pagination.pageIndex ?? 0
            if (current > lastIndex) table.setPageIndex?.(lastIndex)
        }
    },
    { immediate: true }
)

defineExpose({
    table,
    selectedCount,
    totalRows: computed(() => table.getPreFilteredRowModel().rows.length),
    filteredTotal,
})
</script>
