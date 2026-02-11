<template>
    <div class="custom-picker flex flex-wrap items-center gap-3">
        <!-- Selector de tipo: Semanal / Personalizado -->
        <Select
            v-if="!selectDisabled"
            v-model="type"
            :options="optionsType"
            option-label="label"
            option-value="value"
            placeholder="Tipo de rango"
            class="min-w-[140px]"
        />

        <!-- Picker de semana -->
        <div v-if="type === 'week'" class="picker-wrapper min-w-[280px]">
            <DatePicker
                v-model:value="selectedWeek"
                picker="week"
                style="width: 100%; font-family: 'Roboto Condensed'"
                placeholder="Seleccione semana"
                :allow-clear="false"
                :locale="locale"
                :disabled-date="disabledDate"
                :format="weekFormat"
                @change="onWeekChange"
            />
        </div>

        <!-- Picker de rango personalizado -->
        <div v-if="type === 'custom'" class="picker-wrapper min-w-[280px]">
            <RangePicker
                v-model:value="customRange"
                style="width: 100%; font-family: 'Roboto Condensed'"
                :placeholder="['Fecha inicio', 'Fecha fin']"
                :format="customFormat"
                :locale="locale"
                :allow-clear="false"
                :presets="rangePresets"
                :disabled-date="disabledDate"
                @change="onCustomRangeChange"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import DatePicker, { RangePicker } from 'ant-design-vue/es/date-picker';
import locale from 'ant-design-vue/es/date-picker/locale/es_ES';
import Select from 'primevue/select';
import type { Dayjs } from 'dayjs';
import { watch } from 'vue';
import { useDateRange } from '@/composables/useDateRange';

interface Props {
    selectDisabled?: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
    (e: 'update:dates', value: { start: string; end: string }): void;
    (e: 'update:formattedRange', value: string): void;
}>();

const {
    type,
    dates,
    selectedWeek,
    customRange,
    rangePresets,
    disabledDate,
    weekFormat,
    customFormat,
    updateWeek,
    updateCustomRange,
    formattedRange,
} = useDateRange();

const optionsType = [
    { label: 'Semanal', value: 'week' },
    { label: 'Personalizado', value: 'custom' },
];

function onWeekChange(value: string | Dayjs) {
    if (value != null && typeof value !== 'string') {
        updateWeek(value);
    }
}

function onCustomRangeChange(value: [Dayjs, Dayjs] | [string, string]) {
    if (Array.isArray(value) && value.length === 2 && value[0] != null && value[1] != null && typeof value[0] !== 'string') {
        updateCustomRange(value as [Dayjs, Dayjs]);
    }
}

watch(
    [dates, formattedRange],
    () => {
        emit('update:dates', dates.value);
        emit('update:formattedRange', formattedRange.value);
    },
    { immediate: true }
);
</script>

<style scoped>
.picker-wrapper :deep(.ant-picker) {
    width: 100%;
}
</style>
