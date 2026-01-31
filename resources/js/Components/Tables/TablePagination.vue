<template>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between min-w-0">
        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-1.5 sm:gap-2">
            <Button type="button" class="p-button-sm p-button-outlined min-w-[2.5rem]" aria-label="Primera página"
                :disabled="!table.getCanPreviousPage()" @click="table.setPageIndex(0)">
                <span class="sm:hidden">1ª</span>
                <span class="hidden sm:inline">Primera</span>
            </Button>
            <Button type="button" class="p-button-sm p-button-outlined min-w-[2.5rem]" aria-label="Anterior"
                :disabled="!table.getCanPreviousPage()" @click="table.previousPage()">
                <span class="sm:hidden">‹</span>
                <span class="hidden sm:inline">Anterior</span>
            </Button>

            <span class="text-xs sm:text-sm text-gray-600 px-2 shrink-0 whitespace-nowrap">
                {{ pageIndex + 1 }} / {{ pageCount }}
            </span>

            <Button type="button" class="p-button-sm p-button-outlined min-w-[2.5rem]" aria-label="Siguiente"
                :disabled="!table.getCanNextPage()" @click="table.nextPage()">
                <span class="sm:hidden">›</span>
                <span class="hidden sm:inline">Siguiente</span>
            </Button>
            <Button type="button" class="p-button-sm p-button-outlined min-w-[2.5rem]" aria-label="Última página"
                :disabled="!table.getCanNextPage()" @click="table.setPageIndex(lastPageIndex)">
                <span class="sm:hidden">Últ.</span>
                <span class="hidden sm:inline">Última</span>
            </Button>
        </div>

        <div class="flex flex-wrap items-center justify-center sm:justify-end gap-2 sm:gap-3 min-w-0">
            <label class="text-xs sm:text-sm text-gray-600 shrink-0">Mostrar</label>
            <Select v-model="pageSize" :options="pageSizeOptions" class="w-20 sm:w-28 shrink-0"
                aria-label="Tamaño de página" />
            <span class="text-xs sm:text-sm text-gray-600 shrink-0 whitespace-nowrap">
                {{ rangeStart }}–{{ rangeEnd }} de {{ filteredTotal }}
                <template v-if="filteredTotal !== totalRows"> ({{ totalRows }})</template>
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
