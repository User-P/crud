<template>
    <div class="flex flex-wrap items-end gap-3 w-full">
        <div
            v-for="column in filterableColumns"
            :key="column.id"
            class="flex flex-col gap-1 w-full sm:w-48"
        >
            <label class="text-xs text-gray-600">{{ getColumnLabel(column) }}</label>

            <template v-if="getFilterType(column) === 'select'">
                <select
                    class="border rounded px-2 py-1 text-sm w-full"
                    :value="String(column.getFilterValue() ?? '')"
                    @change="onSelectChange(column, $event)"
                >
                    <option value="">Todos</option>
                    <option
                        v-for="opt in getSelectOptions(column)"
                        :key="String(opt.value)"
                        :value="String(opt.value)"
                    >
                        {{ opt.label }}
                    </option>
                </select>
            </template>

            <template v-else-if="getFilterType(column) === 'numberRange'">
                <div class="grid grid-cols-2 gap-2">
                    <InputText
                        type="number"
                        class="w-full"
                        :placeholder="getMinPlaceholder(column)"
                        :model-value="String(getNumberRangeValue(column).min ?? '')"
                        @update:model-value="(value) => updateNumberRange(column, { min: value })"
                    />
                    <InputText
                        type="number"
                        class="w-full"
                        :placeholder="getMaxPlaceholder(column)"
                        :model-value="String(getNumberRangeValue(column).max ?? '')"
                        @update:model-value="(value) => updateNumberRange(column, { max: value })"
                    />
                </div>
            </template>

            <template v-else-if="getFilterType(column) === 'dateRange'">
                <div class="grid grid-cols-2 gap-2">
                    <InputText
                        type="date"
                        class="w-full"
                        :placeholder="getFromPlaceholder(column)"
                        :model-value="getDateRangeValue(column).from ?? ''"
                        @update:model-value="(value) => updateDateRange(column, { from: value })"
                    />
                    <InputText
                        type="date"
                        class="w-full"
                        :placeholder="getToPlaceholder(column)"
                        :model-value="getDateRangeValue(column).to ?? ''"
                        @update:model-value="(value) => updateDateRange(column, { to: value })"
                    />
                </div>
            </template>

            <template v-else>
                <InputText
                    type="search"
                    class="w-full"
                    :placeholder="getColumnPlaceholder(column)"
                    :model-value="String(column.getFilterValue() ?? '')"
                    @update:model-value="(value) => column.setFilterValue(value || undefined)"
                />
            </template>
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

interface FilterOption {
    label: string
    value: string | number
}

interface ColumnMeta {
    filterType?: 'text' | 'select' | 'numberRange' | 'dateRange'
    filterPlaceholder?: string
    filterMinPlaceholder?: string
    filterMaxPlaceholder?: string
    filterFromPlaceholder?: string
    filterToPlaceholder?: string
    filterOptions?: FilterOption[]
}

interface Props<TData> {
    table: Table<TData>
}

const props = defineProps<Props<TData>>()

const filterableColumns = computed(() =>
    props.table.getAllLeafColumns().filter((column) => column.getCanFilter())
)

const hasActiveFilters = computed(() => props.table.getState().columnFilters.length > 0)

const getColumnMeta = (column: Column<TData, unknown>): ColumnMeta =>
    (column.columnDef.meta as ColumnMeta) || {}

const getFilterType = (column: Column<TData, unknown>) =>
    getColumnMeta(column).filterType ?? 'text'

const getColumnLabel = (column: Column<TData, unknown>) => {
    const header = column.columnDef.header
    if (typeof header === 'string') return header
    return column.id
}

const getColumnPlaceholder = (column: Column<TData, unknown>) =>
    getColumnMeta(column).filterPlaceholder ?? 'Filtrar...'

const getMinPlaceholder = (column: Column<TData, unknown>) =>
    getColumnMeta(column).filterMinPlaceholder ?? 'Min'

const getMaxPlaceholder = (column: Column<TData, unknown>) =>
    getColumnMeta(column).filterMaxPlaceholder ?? 'Max'

const getFromPlaceholder = (column: Column<TData, unknown>) =>
    getColumnMeta(column).filterFromPlaceholder ?? 'Desde'

const getToPlaceholder = (column: Column<TData, unknown>) =>
    getColumnMeta(column).filterToPlaceholder ?? 'Hasta'

const getSelectOptions = (column: Column<TData, unknown>) =>
    getColumnMeta(column).filterOptions ?? []

const parseNumber = (value: string | number | undefined) => {
    if (value === '' || value === undefined || value === null) return undefined
    const num = typeof value === 'number' ? value : Number(value)
    return Number.isFinite(num) ? num : undefined
}

const getNumberRangeValue = (column: Column<TData, unknown>) => {
    const current = (column.getFilterValue() as { min?: number; max?: number }) || {}
    return { min: current.min, max: current.max }
}

const updateNumberRange = (
    column: Column<TData, unknown>,
    partial: { min?: string | number; max?: string | number }
) => {
    const current = getNumberRangeValue(column)
    const next = {
        min: partial.min !== undefined ? parseNumber(partial.min) : current.min,
        max: partial.max !== undefined ? parseNumber(partial.max) : current.max,
    }

    if (next.min === undefined && next.max === undefined) {
        column.setFilterValue(undefined)
        return
    }

    column.setFilterValue(next)
}

const getDateRangeValue = (column: Column<TData, unknown>) => {
    const current = (column.getFilterValue() as { from?: string; to?: string }) || {}
    return { from: current.from, to: current.to }
}

const updateDateRange = (
    column: Column<TData, unknown>,
    partial: { from?: string; to?: string }
) => {
    const current = getDateRangeValue(column)
    const next = {
        from: partial.from !== undefined ? partial.from || undefined : current.from,
        to: partial.to !== undefined ? partial.to || undefined : current.to,
    }

    if (!next.from && !next.to) {
        column.setFilterValue(undefined)
        return
    }

    column.setFilterValue(next)
}

const onSelectChange = (column: Column<TData, unknown>, event: Event) => {
    const target = event.target as HTMLSelectElement
    const raw = target.value
    if (raw === '') {
        column.setFilterValue(undefined)
        return
    }

    const options = getSelectOptions(column)
    const firstValue = options[0]?.value
    const parsed = typeof firstValue === 'number' ? Number(raw) : raw
    column.setFilterValue(parsed)
}

const clearAll = () => {
    props.table.resetColumnFilters?.()
}
</script>
