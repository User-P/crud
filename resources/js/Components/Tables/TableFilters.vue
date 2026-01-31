<template>
    <div class="flex flex-wrap items-end gap-3 w-full">
        <div
            v-for="column in filterableColumns"
            :key="column.id"
            class="flex flex-col gap-1 w-full sm:w-48"
        >
            <label class="text-xs text-gray-600">{{ getColumnLabel(column) }}</label>
            <InputText
                :model-value="String(column.getFilterValue() ?? '')"
                type="search"
                class="w-full"
                :placeholder="getColumnPlaceholder(column)"
                @update:model-value="(value) => column.setFilterValue(value || undefined)"
            />
        </div>

        <Button
            v-if="filterableColumns.length > 0"
            type="button"
            class="p-button-sm p-button-secondary"
            label="Limpiar filtros"
            :disabled="!hasActiveFilters"
            @click="clearAll"
        />
    </div>
</template>

<script setup lang="ts" generic="TData extends Record<string, any>">
import { computed } from 'vue'
import type { Column, Table } from '@tanstack/vue-table'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'

interface Props<TData> {
    table: Table<TData>
}

const props = defineProps<Props<TData>>()

const filterableColumns = computed(() =>
    props.table.getAllLeafColumns().filter((column) => column.getCanFilter())
)

const hasActiveFilters = computed(() => props.table.getState().columnFilters.length > 0)

const getColumnLabel = (column: Column<TData, unknown>) => {
    const header = column.columnDef.header
    if (typeof header === 'string') return header
    return column.id
}

const getColumnPlaceholder = (column: Column<TData, unknown>) => {
    const meta = column.columnDef.meta as { filterPlaceholder?: string } | undefined
    return meta?.filterPlaceholder ?? 'Filtrar...'
}

const clearAll = () => {
    props.table.resetColumnFilters?.()
}
</script>
