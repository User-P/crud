<template>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-2 flex-wrap">
            <button aria-label="Primera página" class="px-2 py-1 rounded border" type="button"
                :disabled="!table.getCanPreviousPage()" @click="table.setPageIndex(0)">Primera</button>
            <button aria-label="Anterior" class="px-2 py-1 rounded border" type="button"
                :disabled="!table.getCanPreviousPage()" @click="table.previousPage()">Anterior</button>

            <span class="text-sm text-gray-600">Página {{ pageIndex + 1 }} / {{ pageCount }}</span>

            <button aria-label="Siguiente" class="px-2 py-1 rounded border" type="button"
                :disabled="!table.getCanNextPage()" @click="table.nextPage()">Siguiente</button>
            <button aria-label="Última página" class="px-2 py-1 rounded border" type="button"
                :disabled="!table.getCanNextPage()" @click="table.setPageIndex(lastPageIndex)">Última</button>
        </div>

        <div class="flex items-center gap-3">
            <label class="text-sm text-gray-600">Mostrar</label>
            <select class="border rounded px-2 py-1" v-model.number="pageSize">
                <option v-for="opt in pageSizeOptions" :key="opt" :value="opt">{{ opt }}</option>
            </select>
            <span class="text-sm text-gray-600">
                Mostrando {{ rangeStart }}–{{ rangeEnd }} de {{ filteredTotal }}
                <template v-if="filteredTotal !== totalRows"> (total {{ totalRows }})</template>
            </span>
        </div>
    </div>
</template>

<script setup lang="ts" generic="TData extends Record<string, any>">
import { computed, watch } from 'vue'
import type { Table } from '@tanstack/vue-table'

interface Props<TData> {
    table: Table<TData>
    pageSizeOptions?: number[]
}

const props = withDefaults(defineProps<Props<TData>>(), {
    pageSizeOptions: () => [10, 25, 50],
})

const pageIndex = computed(() => props.table.getState().pagination.pageIndex)
const pageCount = computed(() => Math.max(props.table.getPageCount(), 1))
const lastPageIndex = computed(() => Math.max(pageCount.value - 1, 0))
const pageSize = computed({
    get: () => props.table.getState().pagination.pageSize || props.pageSizeOptions[0],
    set: (value) => props.table.setPageSize?.(value),
})

// When user changes page size, jump back to the first page to avoid invalid page index
watch(() => pageSize.value, (v, old) => {
    if (v !== old) {
        props.table.setPageIndex?.(0)
    }
})

const totalRows = computed(() => props.table.getPreFilteredRowModel().rows.length)
const filteredTotal = computed(() => props.table.getFilteredRowModel().rows.length)
const rangeStart = computed(() => {
    if (filteredTotal.value === 0) return 0
    return pageIndex.value * pageSize.value + 1
})
const rangeEnd = computed(() => {
    if (filteredTotal.value === 0) return 0
    return Math.min(filteredTotal.value, (pageIndex.value + 1) * pageSize.value)
})
</script>
