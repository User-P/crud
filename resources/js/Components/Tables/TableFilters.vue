<template>
    <div class="flex flex-wrap items-end gap-3">
        <div v-for="column in filterableColumns" :key="column.id" class="flex flex-col gap-1">
            <label class="text-xs text-gray-600">{{ getColumnLabel(column) }}</label>
            <input
                :value="String(column.getFilterValue() ?? '')"
                type="search"
                class="rounded border px-2 py-1 text-sm"
                :placeholder="getColumnPlaceholder(column)"
                @input="onInput(column, $event)"
            />
        </div>

        <button
            v-if="filterableColumns.length > 0"
            type="button"
            class="px-2 py-1 rounded border text-sm"
            @click="clearAll"
        >
            Limpiar filtros
        </button>
    </div>
</template>

<script setup lang="ts" generic="TData extends Record<string, any>">
import { computed } from 'vue'
import type { Column, Table } from '@tanstack/vue-table'

interface Props<TData> {
    table: Table<TData>
}

const props = defineProps<Props<TData>>()

const filterableColumns = computed(() =>
    props.table.getAllLeafColumns().filter((column) => column.getCanFilter())
)

const getColumnLabel = (column: Column<TData, unknown>) => {
    const header = column.columnDef.header
    if (typeof header === 'string') return header
    return column.id
}

const getColumnPlaceholder = (column: Column<TData, unknown>) => {
    const meta = column.columnDef.meta as { filterPlaceholder?: string } | undefined
    return meta?.filterPlaceholder ?? 'Filtrar...'
}

const onInput = (column: Column<TData, unknown>, event: Event) => {
    const target = event.target as HTMLInputElement
    column.setFilterValue(target.value)
}

const clearAll = () => {
    props.table.resetColumnFilters?.()
}
</script>
