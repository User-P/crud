<template>
    <div class="text-slate-900">
        <!-- Toolbar: stacks on small screens -->
        <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-3 w-full sm:flex-row sm:items-center sm:w-auto">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <slot name="toolbar" :table="table">
                        <!-- default slot left intentionally blank -->
                    </slot>
                </div>
                <div class="hidden sm:flex items-center gap-3 ml-2 text-sm text-gray-600">
                    <div v-if="props.enableGlobalFilter">
                        <div class="text-sm text-gray-500">Resultados: <span class="font-medium">{{ filteredTotal
                        }}</span></div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-2 w-full sm:flex-row sm:items-center sm:w-auto">
                <template v-if="props.enableGlobalFilter">
                    <label for="table-search" class="sr-only">Buscar</label>
                    <InputText id="table-search" v-model="globalSearch" type="search" class="w-full sm:w-64"
                        placeholder="Buscar..." />
                </template>

                <template v-if="props.selectable">
                    <div class="text-sm text-gray-600">{{ selectedCount }} seleccionados</div>
                </template>
            </div>
        </div>

        <div class="overflow-auto bg-white rounded-md shadow-sm">
            <table class="min-w-full table-auto divide-y divide-gray-200" role="table" :aria-busy="loading">
                <thead :class="props.showStickyHeader ? 'sticky top-0 z-10 bg-white/95 backdrop-blur-sm' : ''">
                    <tr v-for="(headerGroup, headerGroupIndex) in table.getHeaderGroups()" :key="headerGroup.id">
                        <th v-if="props.selectable" class="py-3 px-3 text-left w-12">
                            <Checkbox :model-value="table.getIsAllPageRowsSelected()" binary
                                :indeterminate="table.getIsSomePageRowsSelected()"
                                :disabled="loading || table.getRowModel().rows.length === 0"
                                aria-label="Seleccionar todas las filas visibles"
                                @update:model-value="(value) => table.toggleAllPageRowsSelected(!!value)" />
                        </th>

                        <th v-for="header in headerGroup.headers" :key="header.id" :colspan="header.colSpan"
                            class="py-3 px-4 text-left text-sm font-semibold text-gray-700 whitespace-nowrap"
                            :class="header.column.getCanSort() ? 'cursor-pointer' : ''" @click="onHeaderClick(header)">
                            <div class="flex items-center gap-2">
                                <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
                                <span v-if="header.column.getCanSort()" class="ml-1 text-xs text-gray-400">{{
                                    sortIndicator(header.column.getIsSorted()) }}</span>
                            </div>
                        </th>

                        <th v-if="hasRowActions && headerGroupIndex === table.getHeaderGroups().length - 1"
                            class="py-3 px-4 text-left text-sm font-semibold text-gray-700 w-28">
                            {{ rowActionsLabel }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <tr v-if="loading">
                        <td :colspan="table.getAllLeafColumns().length + extraColumns"
                            class="py-6 px-4 text-center text-sm text-gray-500">Cargando...</td>
                    </tr>
                    <tr v-else-if="table.getRowModel().rows.length === 0">
                        <td :colspan="table.getAllLeafColumns().length + extraColumns"
                            class="py-6 px-4 text-center text-sm text-gray-500">
                            <slot name="empty">{{ emptyText }}</slot>
                        </td>
                    </tr>
                    <tr v-for="row in table.getRowModel().rows" :key="row.id" class="hover:bg-gray-50">
                        <td v-if="props.selectable" class="py-3 px-3 align-top">
                            <Checkbox :model-value="row.getIsSelected()" binary :disabled="loading"
                                :aria-label="`Seleccionar fila ${row.id}`"
                                @update:model-value="(value) => row.toggleSelected(!!value)" />
                        </td>
                        <td v-for="cell in row.getVisibleCells()" :key="cell.id"
                            class="py-3 px-4 align-top text-sm text-gray-700">
                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                        </td>
                        <td v-if="hasRowActions" class="py-3 px-4 align-top text-sm text-gray-700">
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

/**
 * TanStackTable: responsive, accessible table wrapper for TanStack Vue Table
 * - Responsive toolbar and table container
 * - Sticky header support
 * - Selection, global & column filters, sorting, pagination
 */
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
    rowActionsLabel: 'Acciones',
})

const slots = useSlots()

// Local reactive state used by useVueTable
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

const toDate = (input?: string | Date | number | null) => {
    if (input === undefined || input === null || input === '') return undefined
    if (input instanceof Date) return input
    if (typeof input === 'number') {
        const parsed = new Date(input)
        return Number.isNaN(parsed.getTime()) ? undefined : parsed
    }
    if (typeof input === 'string') {
        const match = input.match(/^(\d{4})-(\d{2})-(\d{2})$/)
        if (match) {
            const [, y, m, d] = match
            return new Date(Number(y), Number(m) - 1, Number(d))
        }
        const parsed = new Date(input)
        return Number.isNaN(parsed.getTime()) ? undefined : parsed
    }
    return undefined
}

// Helpers to normalize to local date-only values (strip time) for consistent comparisons
const toDateOnly = (input?: string | Date | number | null) => {
    const d = toDate(input)
    if (!d) return undefined
    return new Date(d.getFullYear(), d.getMonth(), d.getDate())
}

const dateRangeFilter: FilterFn<any> = (row, columnId, value) => {
    const raw = row.getValue(columnId)
    const rowRawDate = toDate(raw as string | Date | number | null)
    if (!rowRawDate) return false

    // Convert row date to date-only (local) to avoid timezone/time-part surprises
    const rowDate = new Date(rowRawDate.getFullYear(), rowRawDate.getMonth(), rowRawDate.getDate())

    let from = value?.from ?? value?.[0]
    let to = value?.to ?? value?.[1]
    let fromDate = toDateOnly(from)
    let toDateValue = toDateOnly(to)

    // If bounds are inverted (user or picker returned [to, from]), swap them
    if (fromDate && toDateValue && fromDate.getTime() > toDateValue.getTime()) {
        const tmp = fromDate
        fromDate = toDateValue
        toDateValue = tmp
    }

    if (fromDate && rowDate.getTime() < fromDate.getTime()) return false
    if (toDateValue && rowDate.getTime() > toDateValue.getTime()) return false
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
        // String includes (case-insensitive) for global & column filters
        includesString: (row, columnId, filterValue: string) => {
            if (filterValue === undefined || filterValue === null || filterValue === '') return true
            const rowValue = String(row.getValue(columnId) ?? '')
            return rowValue.toLowerCase().includes(String(filterValue).toLowerCase())
        },
        // Equality match
        equalsString: (row, columnId, filterValue) => {
            if (filterValue === undefined || filterValue === null || filterValue === '') return true
            const rowValue = row.getValue(columnId)
            return String(rowValue) === String(filterValue)
        },
        // Number range (alias inNumberRange for convenience)
        inNumberRange: numberRangeFilter,
        numberRange: numberRangeFilter,
        // Date range
        dateRange: dateRangeFilter,
    },
    enableGlobalFilter: props.enableGlobalFilter,
    globalFilterFn: 'includesString',
    enableSorting: props.enableSorting,
    enableColumnFilters: props.enableColumnFilters,
    enableRowSelection: props.selectable,
})

// Helpers / computed values for UI
const loading = computed(() => props.loading)
const emptyText = computed(() => props.emptyText)
const rowActionsLabel = computed(() => props.rowActionsLabel)

// Debounced global search input to reduce filter churn
const globalSearch = ref(globalFilter.value)
const debounce = (fn: (...args: any[]) => void, wait = 300) => {
    let t: number | undefined
    return (...args: any[]) => {
        if (t) window.clearTimeout(t)
        t = window.setTimeout(() => fn(...args), wait)
    }
}

watch(
    () => globalSearch.value,
    debounce((v: string) => {
        // TanStack expects a string for globalFilter, use empty string to clear filters
        globalFilter.value = v || ''
    }, 250)
)

const hasRowActions = computed(() => !!slots['row-actions'])
const extraColumns = computed(() => (props.selectable ? 1 : 0) + (hasRowActions.value ? 1 : 0))

const selectedCount = computed(() => Object.values(rowSelection.value).filter(Boolean).length)

const totalRows = computed(() => table.getPreFilteredRowModel().rows.length)
const filteredTotal = computed(() => table.getFilteredRowModel().rows.length)

const onHeaderClick = (header: Header<TData, unknown>) => {
    if (!props.enableSorting || !header.column.getCanSort()) return
    header.column.toggleSorting()
}

const sortIndicator = (state: false | 'asc' | 'desc') => {
    if (state === 'asc') return 'ASC'
    if (state === 'desc') return 'DESC'
    return ''
}

// Apply initial page size and reset to first page when it changes
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

// When the global filter changes, reset pagination to the first page (UX-friendly)
watch(globalFilter, () => {
    if (props.enablePagination) {
        table.setPageIndex?.(0)
    }
})

// Watch data and filtered length to keep pageIndex within valid bounds and reset selection
watch(
    () => [props.data, table.getFilteredRowModel().rows.length],
    () => {
        if (props.selectable) {
            table.resetRowSelection?.()
        }

        if (props.enablePagination) {
            const pageCount = Math.max(table.getPageCount(), 1)
            const lastIndex = Math.max(pageCount - 1, 0)
            const current = table.getState().pagination.pageIndex || 0
            if (current > lastIndex) {
                table.setPageIndex?.(lastIndex)
            }
        }
    },
    { immediate: true }
)

defineExpose({
    table,
    selectedCount,
    totalRows,
    filteredTotal,
})
</script>
