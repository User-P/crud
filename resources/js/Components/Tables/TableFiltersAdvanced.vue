<template>
    <div class="w-full">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            <div v-for="column in filterableColumns" :key="column.id" class="flex flex-col gap-1 min-w-0">
                <label class="text-xs text-gray-600">{{ getColumnLabel(column) }}</label>

                <template v-if="getFilterType(column) === 'select'">
                    <Select
                        :model-value="getSelectValue(column)"
                        :options="getSelectOptions(column)"
                        optionLabel="label"
                        optionValue="value"
                        class="w-full"
                        placeholder="Todos"
                        show-clear
                        @update:model-value="(value) => updateSelectValue(column, value)"
                    />
                </template>

                <template v-else-if="getFilterType(column) === 'numberRange'">
                    <div class="grid grid-cols-2 gap-2">
                        <InputNumber
                            class="w-full"
                            inputClass="w-full"
                            :placeholder="getMinPlaceholder(column)"
                            :model-value="getNumberRangeValue(column).min ?? null"
                            @update:model-value="(value) => updateNumberRange(column, { min: value })"
                        />
                        <InputNumber
                            class="w-full"
                            inputClass="w-full"
                            :placeholder="getMaxPlaceholder(column)"
                            :model-value="getNumberRangeValue(column).max ?? null"
                            @update:model-value="(value) => updateNumberRange(column, { max: value })"
                        />
                    </div>
                </template>

                <template v-else-if="getFilterType(column) === 'dateRange'">
                    <DatePicker
                        class="w-full"
                        inputClass="w-full"
                        :model-value="getDateRangeValueArray(column)"
                        selectionMode="range"
                        updateModelType="date"
                        :manualInput="false"
                        date-format="yy-mm-dd"
                        show-clear
                        :placeholder="getRangePlaceholder(column)"
                        @update:model-value="(value) => updateDateRangeFromPicker(column, value)"
                    />
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
        </div>

        <div class="mt-3 flex justify-end">
            <Button
                v-if="filterableColumns.length > 0"
                type="button"
                class="p-button-sm p-button-secondary"
                label="Limpiar filtros"
                :disabled="!hasActiveFilters"
                @click="clearAll"
            />
        </div>
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

const getRangePlaceholder = (column: Column<TData, unknown>) => {
    const meta = getColumnMeta(column)
    if (meta.filterPlaceholder) return meta.filterPlaceholder
    const from = meta.filterFromPlaceholder ?? 'Desde'
    const to = meta.filterToPlaceholder ?? 'Hasta'
    return `${from} - ${to}`
}

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
    if (typeof value === 'string') {
        const match = value.match(/^(\d{4})-(\d{2})-(\d{2})$/)
        if (match) {
            const [, y, m, d] = match
            return new Date(Number(y), Number(m) - 1, Number(d))
        }
    }
    const parsed = new Date(value)
    return Number.isNaN(parsed.getTime()) ? undefined : parsed
}

const getDateRangeValue = (column: Column<TData, unknown>) => {
    const current = (column.getFilterValue() as { from?: string | Date; to?: string | Date }) || {}
    return { from: toDate(current.from), to: toDate(current.to) }
}

const normalizeDate = (value?: Date | null) => {
    if (!value) return undefined
    return new Date(value.getFullYear(), value.getMonth(), value.getDate())
}

const updateDateRange = (
    column: Column<TData, unknown>,
    partial: { from?: Date | null; to?: Date | null }
) => {
    const current = getDateRangeValue(column)
    const next = {
        from: partial.from !== undefined ? normalizeDate(partial.from) : current.from,
        to: partial.to !== undefined ? normalizeDate(partial.to) : current.to,
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
    if (!from && !to) return null
    if (from && !to) return [from, null]
    if (!from && to) return [to, null]
    return [from as Date, to as Date]
}

const updateDateRangeFromPicker = (column: Column<TData, unknown>, value: unknown) => {
    // Value can be: null, Date, [Date, Date], or undefined depending on the DatePicker
    if (!value) {
        column.setFilterValue(undefined)
        return
    }

    if (Array.isArray(value)) {
        const [from, to] = value as (Date | null | undefined)[]
        if (!from && !to) {
            column.setFilterValue(undefined)
            return
        }
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
