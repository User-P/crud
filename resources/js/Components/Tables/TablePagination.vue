<template>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-2 flex-wrap">
            <Button
                type="button"
                class="p-button-sm"
                label="Primera"
                aria-label="Primera página"
                :disabled="!table.getCanPreviousPage()"
                @click="table.setPageIndex(0)"
            />
            <Button
                type="button"
                class="p-button-sm"
                label="Anterior"
                aria-label="Anterior"
                :disabled="!table.getCanPreviousPage()"
                @click="table.previousPage()"
            />

            <span class="text-sm text-gray-600">Página {{ pageIndex + 1 }} / {{ pageCount }}</span>

            <Button
                type="button"
                class="p-button-sm"
                label="Siguiente"
                aria-label="Siguiente"
                :disabled="!table.getCanNextPage()"
                @click="table.nextPage()"
            />
            <Button
                type="button"
                class="p-button-sm"
                label="Última"
                aria-label="Última página"
                :disabled="!table.getCanNextPage()"
                @click="table.setPageIndex(lastPageIndex)"
            />
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <label class="text-sm text-gray-600">Mostrar</label>
            <Select
                v-model="pageSize"
                :options="pageSizeOptions"
                class="w-full sm:w-28"
                aria-label="Tamaño de página"
            />
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
import Button from 'primevue/button'
import Select from 'primevue/select'

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
