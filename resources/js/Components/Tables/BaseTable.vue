<template>
    <div class="text-black">
        <!-- Toolbar: custom slot or default global search + summary -->
        <div class="mb-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <slot name="toolbar" :table="table">
                    <!-- default: nothing, keep slot if provided by parent -->
                </slot>
            </div>

            <div class="flex items-center gap-3">
                <template v-if="props.enableGlobalFilter">
                    <input v-model="globalFilter" type="search" class="rounded border px-2 py-1 text-sm"
                        placeholder="Buscar..." />
                </template>

                <template v-if="props.selectable">
                    <div class="text-sm text-gray-600">{{ selectedCount }} seleccionados</div>
                </template>
            </div>
        </div>

        <div class="overflow-auto">
            <table class="min-w-full border-collapse">
                <thead :class="props.showStickyHeader ? 'sticky top-0 bg-white' : ''">
                    <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <th v-if="props.selectable" class="py-2 px-3">
                            <input type="checkbox" :checked="allPageSelected" :indeterminate.prop="isIndeterminate"
                                @change="toggleSelectAll" aria-label="Seleccionar todas las filas visibles" />
                        </th>

                        <th v-for="header in headerGroup.headers" :key="header.id" :colspan="header.colSpan"
                            class="py-2 px-3 text-left text-sm font-semibold text-gray-900 cursor-pointer select-none"
                            @click="onHeaderClick(header)">
                            <span v-if="!header.isPlaceholder">
                                <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
                                <span v-if="header.column.getCanSort()" class="ml-2 text-xs text-gray-500">{{
                                    sortIndicator(header.column.getIsSorted()) }}</span>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td :colspan="(props.selectable ? 1 : 0) + table.getAllLeafColumns().length"
                            class="py-4 px-3 text-center text-sm text-gray-500">
                            Cargando...
                        </td>
                    </tr>
                    <tr v-else-if="table.getRowModel().rows.length === 0">
                        <td :colspan="(props.selectable ? 1 : 0) + table.getAllLeafColumns().length"
                            class="py-4 px-3 text-center text-sm text-gray-500">
                            <slot name="empty">{{ emptyText }}</slot>
                        </td>
                    </tr>
                    <tr v-for="row in table.getRowModel().rows" :key="row.id" class="hover:bg-gray-50">
                        <td v-if="props.selectable" class="py-2 px-3">
                            <input type="checkbox" :checked="isRowSelected(row)" @change="toggleRow(row)"
                                :aria-label="`Seleccionar fila ${row.id}`" />
                        </td>
                        <td v-for="cell in row.getVisibleCells()" :key="cell.id" class="py-2 px-3 align-top">
                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <slot name="pagination" :table="table" />
    </div>
</template>

<script setup lang="ts" generic="TData extends Record<string, any>">
import { computed, ref, watch } from 'vue'
import {
    type ColumnDef,
    type Header,
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
    showStickyHeader?: boolean
}

const props = withDefaults(defineProps<Props<TData>>(), {
    enableSorting: false,
    enablePagination: false,
    pageSize: 10,
    emptyText: 'Sin datos',
    loading: false,
    selectable: false,
    enableGlobalFilter: false,
    showStickyHeader: true,
})

const sorting = ref<SortingState>([])
const pagination = ref<PaginationState>({
    pageIndex: 0,
    pageSize: props.pageSize,
})
const columnFilters = ref<ColumnFiltersState>([])
const globalFilter = ref('')
const rowSelection = ref<RowSelectionState>({})

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
    enableSorting: props.enableSorting,
})

const loading = computed(() => props.loading)
const emptyText = computed(() => props.emptyText)

const allPageSelected = computed(() => {
    const ids = table.getRowModel().rows.map(r => r.id)
    if (ids.length === 0) return false
    return ids.every(id => !!rowSelection.value[id])
})

const anyPageSelected = computed(() => {
    const ids = table.getRowModel().rows.map(r => r.id)
    return ids.some(id => !!rowSelection.value[id])
})

const isIndeterminate = computed(() => anyPageSelected.value && !allPageSelected.value)

const toggleSelectAll = () => {
    const ids = table.getRowModel().rows.map(r => r.id)
    if (allPageSelected.value) {
        // deselect visible
        const next = { ...rowSelection.value }
        ids.forEach(id => { delete next[id] })
        rowSelection.value = next
    } else {
        const next = { ...rowSelection.value }
        ids.forEach(id => { next[id] = true })
        rowSelection.value = next
    }
}

const isRowSelected = (row: any) => {
    return !!rowSelection.value[row.id]
}

const toggleRow = (row: any) => {
    rowSelection.value = { ...rowSelection.value, [row.id]: !rowSelection.value[row.id] }
}

const selectedCount = computed(() => Object.values(rowSelection.value).filter(Boolean).length)

const onHeaderClick = (header: Header<TData, unknown>) => {
    if (!props.enableSorting || !header.column.getCanSort()) return
    header.column.toggleSorting()
}

const sortIndicator = (state: false | 'asc' | 'desc') => {
    if (state === 'asc') return 'ASC'
    if (state === 'desc') return 'DESC'
    return ''
}

watch(() => props.pageSize, (v) => {
    if (typeof v === 'number' && table.getState().pagination.pageSize !== v) {
        table.setPageSize?.(v)
    }
})

defineExpose({
    table,
    selectedCount,
})
</script>
