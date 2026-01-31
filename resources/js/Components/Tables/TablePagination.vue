<template>
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <button class="px-2 py-1 rounded border" type="button" :disabled="!table.getCanPreviousPage()"
                @click="table.previousPage()">Anterior</button>
            <button class="px-2 py-1 rounded border" type="button" :disabled="!table.getCanNextPage()"
                @click="table.nextPage()">Siguiente</button>
            <span class="text-sm text-gray-600">Página {{ table.getState().pagination.pageIndex + 1 }} / {{
                table.getPageCount() }}</span>
        </div>

        <div class="flex items-center gap-3">
            <label class="text-sm text-gray-600">Mostrar</label>
            <select class="border rounded px-2 py-1" v-model.number="pageSize" @change="onPageSizeChange">
                <option v-for="opt in pageSizeOptions" :key="opt" :value="opt">{{ opt }}</option>
            </select>
            <span class="text-sm text-gray-600">Total: {{ table.getPreFilteredRowModel().rows.length }}</span>
        </div>
    </div>
</template>

<script setup lang="ts" generic="TData extends Record<string, any>">
import { ref, watch } from 'vue'
import type { Table } from '@tanstack/vue-table'

interface Props<TData> {
    table: Table<TData>
    pageSizeOptions?: number[]
}

const props = withDefaults(defineProps<Props<any>>(), {
    pageSizeOptions: () => [10, 25, 50],
})

const pageSize = ref(props.table.getState().pagination.pageSize || props.pageSizeOptions[0])

const onPageSizeChange = () => {
    props.table.setPageSize?.(pageSize.value)
}

watch(() => props.table.getState().pagination.pageSize, (v) => {
    pageSize.value = v
})
</script>
