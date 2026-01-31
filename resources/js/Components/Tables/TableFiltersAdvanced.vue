<template>
    <div class="w-full min-w-0">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3">
            <div v-for="column in filterableColumns" :key="column.id" class="flex flex-col gap-1 min-w-0">
                <label class="text-xs font-medium text-gray-600 truncate">{{ getColumnLabel(column) }}</label>

                <template v-if="getFilterType(column) === 'select'">
                    <Select :model-value="getSelectValue(column)" :options="getSelectOptions(column)"
                        option-label="label" option-value="value" class="w-full min-w-0" placeholder="Todos" show-clear
                        @update:model-value="(v) => updateSelectValue(column, v)" />
                </template>

                <template v-else-if="getFilterType(column) === 'numberRange'">
                    <div class="grid grid-cols-2 gap-1.5 sm:gap-2 min-w-0">
                        <InputNumber class="w-full min-w-0" input-class="w-full min-w-0"
                            :placeholder="getMinPlaceholder(column)"
                            :model-value="getNumberRangeValue(column).min ?? null"
                            @update:model-value="(v) => updateNumberRange(column, { min: v })" />
                        <InputNumber class="w-full min-w-0" input-class="w-full min-w-0"
                            :placeholder="getMaxPlaceholder(column)"
                            :model-value="getNumberRangeValue(column).max ?? null"
                            @update:model-value="(v) => updateNumberRange(column, { max: v })" />
                    </div>
                </template>

                <template v-else-if="getFilterType(column) === 'dateRange'">
                    <DatePicker :model-value="getDateRangeForPicker(column)" class="w-full min-w-0"
                        input-class="w-full min-w-0" selection-mode="range" date-format="yy-mm-dd" show-clear
                        :placeholder="getRangePlaceholder(column)"
                        @update:model-value="(v: Date | Date[] | (Date | null)[] | null | undefined) => onDateRangeChange(column, v)" />
                </template>

                <template v-else>
                    <InputText type="search" class="w-full min-w-0" :placeholder="getColumnPlaceholder(column)"
                        :model-value="String(column.getFilterValue() ?? '')"
                        @update:model-value="(v) => column.setFilterValue(v || undefined)" />
                </template>
            </div>
        </div>

        <div v-if="filterableColumns.length > 0" class="mt-2 sm:mt-3 flex justify-end">
            <Button type="button" class="p-button-sm p-button-secondary w-full sm:w-auto" label="Limpiar filtros"
                :disabled="!hasActiveFilters" @click="clearAll" />
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
import { toYYYYMMDD, fromYYYYMMDD, type DateRangeValue } from './dateFilterUtils'

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

const props = defineProps<{ table: Table<TData> }>()

const filterableColumns = computed(() =>
    props.table.getAllLeafColumns().filter((col) => col.getCanFilter())
)
const hasActiveFilters = computed(() => props.table.getState().columnFilters.length > 0)

const getColumnMeta = (column: Column<TData, unknown>): ColumnMeta =>
    (column.columnDef.meta as ColumnMeta) || {}
const getFilterType = (column: Column<TData, unknown>) => getColumnMeta(column).filterType ?? 'text'

const getColumnLabel = (column: Column<TData, unknown>) => {
    const header = column.columnDef.header
    return typeof header === 'string' ? header : column.id
}

const getColumnPlaceholder = (column: Column<TData, unknown>) =>
    getColumnMeta(column).filterPlaceholder ?? 'Filtrar...'
const getMinPlaceholder = (column: Column<TData, unknown>) =>
    getColumnMeta(column).filterMinPlaceholder ?? 'Mín'
const getMaxPlaceholder = (column: Column<TData, unknown>) =>
    getColumnMeta(column).filterMaxPlaceholder ?? 'Máx'
const getRangePlaceholder = (column: Column<TData, unknown>) => {
    const meta = getColumnMeta(column)
    if (meta.filterPlaceholder) return meta.filterPlaceholder
    const from = meta.filterFromPlaceholder ?? 'Desde'
    const to = meta.filterToPlaceholder ?? 'Hasta'
    return `${from} - ${to}`
}

const getSelectOptions = (column: Column<TData, unknown>) => getColumnMeta(column).filterOptions ?? []

function getNumberRangeValue(column: Column<TData, unknown>) {
    const current = (column.getFilterValue() as { min?: number; max?: number }) ?? {}
    return { min: current.min, max: current.max }
}

function updateNumberRange(
    column: Column<TData, unknown>,
    partial: { min?: number | null; max?: number | null }
) {
    const current = getNumberRangeValue(column)
    const min = partial.min !== undefined ? (partial.min === null ? undefined : Number(partial.min)) : current.min
    const max = partial.max !== undefined ? (partial.max === null ? undefined : Number(partial.max)) : current.max
    if (min === undefined && max === undefined) {
        column.setFilterValue(undefined)
        return
    }
    column.setFilterValue({ min, max })
}

function getDateRangeValue(column: Column<TData, unknown>): DateRangeValue {
    const current = (column.getFilterValue() as DateRangeValue) ?? {}
    return { from: current.from, to: current.to }
}

function setDateRangeValue(column: Column<TData, unknown>, value: DateRangeValue | undefined) {
    if (!value?.from && !value?.to) {
        column.setFilterValue(undefined)
        return
    }
    column.setFilterValue({ from: value.from, to: value.to })
}

function getDateRangeForPicker(column: Column<TData, unknown>): Date | [Date, Date] | [Date, null] | null {
    const { from, to } = getDateRangeValue(column)
    if (!from && !to) return null
    const fromDate = from ? fromYYYYMMDD(from) : undefined
    const toDate = to ? fromYYYYMMDD(to) : undefined
    if (fromDate && toDate) return [fromDate, toDate]
    if (fromDate) return [fromDate, null as unknown as Date]
    if (toDate) return [toDate, null as unknown as Date]
    return null
}

function onDateRangeChange(
    column: Column<TData, unknown>,
    value: Date | Date[] | (Date | null)[] | null | undefined
) {
    if (value == null || value === undefined) {
        setDateRangeValue(column, undefined)
        return
    }
    if (Array.isArray(value)) {
        const [a, b] = value
        const from = a ? toYYYYMMDD(a) : undefined
        const to = b ? toYYYYMMDD(b) : undefined
        setDateRangeValue(column, { from, to })
        return
    }
    setDateRangeValue(column, { from: toYYYYMMDD(value), to: undefined })
}

const getSelectValue = (column: Column<TData, unknown>) =>
    (column.getFilterValue() as string | number | null | undefined) ?? null

function updateSelectValue(column: Column<TData, unknown>, value: string | number | null) {
    if (value === null || value === '') {
        column.setFilterValue(undefined)
        return
    }
    column.setFilterValue(value)
}

function clearAll() {
    props.table.resetColumnFilters?.()
}
</script>
