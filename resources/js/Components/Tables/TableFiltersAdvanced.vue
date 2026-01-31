<template>
    <div class="flex flex-wrap items-end gap-3 w-full">
        <div v-for="column in filterableColumns" :key="column.id" class="flex flex-col gap-1 w-full sm:w-48">
            <label class="text-xs text-gray-600">{{ getColumnLabel(column) }}</label>

            <template v-if="getFilterType(column) === 'select'">
                <Select :model-value="getSelectValue(column)" :options="getSelectOptions(column)" optionLabel="label"
                    optionValue="value" class="w-full" placeholder="Todos" show-clear
                    @update:model-value="(value) => updateSelectValue(column, value)" />
            </template>

            <template v-else-if="getFilterType(column) === 'numberRange'">
                <div class="grid grid-cols-2 gap-2">
                    <InputNumber class="w-full" :placeholder="getMinPlaceholder(column)"
                        :model-value="getNumberRangeValue(column).min ?? null"
                        @update:model-value="(value) => updateNumberRange(column, { min: value })" />
                    <InputNumber class="w-full" :placeholder="getMaxPlaceholder(column)"
                        :model-value="getNumberRangeValue(column).max ?? null"
                        @update:model-value="(value) => updateNumberRange(column, { max: value })" />
                </div>
            </template>

            <template v-else-if="getFilterType(column) === 'dateRange'">
                <div>
                    <!-- Single date range picker (PrimeVue DatePicker with selectionMode="range") -->
                    <DatePicker class="w-full" :model-value="getDateRangeValueArray(column)" selectionMode="range"
                        :manualInput="false" date-format="yy-mm-dd" show-clear
                        @update:model-value="(value) => updateDateRangeFromPicker(column, value)" />
                </div>
            </template>

            <template v-else>
                <InputText type="search" class="w-full" :placeholder="getColumnPlaceholder(column)"
                    :model-value="String(column.getFilterValue() ?? '')"
                    @update:model-value="(value) => column.setFilterValue(value || undefined)" />
            </template>
        </div>

        <Button v-if="filterableColumns.length > 0" type="button" class="p-button-sm p-button-secondary"
            label="Limpiar filtros" :disabled="!hasActiveFilters" @click="clearAll" />
    </div>
</template>

<script setup lang="ts" generic="TData extends Record<string, any>">
import { computed } from 'vue'
import type { Column, Table } from '@tanstack/vue-table'
import Button from 'primevue/button'
import DatePicker from 'primevue/datepicker'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'

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



const getSelectOptions = (column: Column<TData, unknown>) =>
    getColumnMeta(column).filterOptions ?? []

const parseNumber = (value: string | number | null | undefined) => {
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
    partial: { min?: string | number | null; max?: string | number | null }
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

const toDate = (value?: string | Date | null) => {
    if (!value) return undefined
    if (value instanceof Date) return value
    const parsed = new Date(value)
    return Number.isNaN(parsed.getTime()) ? undefined : parsed
}

const getDateRangeValue = (column: Column<TData, unknown>) => {
    const current = (column.getFilterValue() as { from?: string | Date; to?: string | Date }) || {}
    return { from: toDate(current.from), to: toDate(current.to) }
}

const updateDateRange = (
    column: Column<TData, unknown>,
    partial: { from?: Date | null; to?: Date | null }
) => {
    const current = getDateRangeValue(column)
    const next = {
        from: partial.from !== undefined ? (partial.from || undefined) : current.from,
        to: partial.to !== undefined ? (partial.to || undefined) : current.to,
    }

    if (!next.from && !next.to) {
        column.setFilterValue(undefined)
        return
    }

    // Store Date objects (filters accept Date or ISO strings in our table implementation)
    column.setFilterValue({ from: next.from, to: next.to })
}

const getDateRangeValueArray = (column: Column<TData, unknown>) => {
    const { from, to } = getDateRangeValue(column)
    return [from ?? null, to ?? null]
}

const updateDateRangeFromPicker = (column: Column<TData, unknown>, value: unknown) => {
    // Value can be: null, Date, [Date, Date], or undefined depending on the DatePicker
    if (!value) {
        column.setFilterValue(undefined)
        return
    }

    if (Array.isArray(value)) {
        const [from, to] = value as (Date | null | undefined)[]
        updateDateRange(column, { from: (from as Date) ?? undefined, to: (to as Date) ?? undefined })
        return
    }

    // Single date selection fallback
    if (value instanceof Date) {
        updateDateRange(column, { from: value, to: undefined })
        return
    }

    // Unknown value -> clear
    column.setFilterValue(undefined)
}

const getSelectValue = (column: Column<TData, unknown>) => {
    return (column.getFilterValue() as string | number | null | undefined) ?? null
}

const updateSelectValue = (column: Column<TData, unknown>, value: string | number | null) => {
    if (value === null || value === '') {
        column.setFilterValue(undefined)
        return
    }

    column.setFilterValue(value)
}

const clearAll = () => {
    props.table.resetColumnFilters?.()
}
</script>
