<template>
    <div class="flex flex-row-reverse gap-4">
        <div v-if="type === 'week'" class="picker-wrapper">
            <a-date-picker :picker="'week'" v-model:value="selectedWeek"
                style="width: 100%; font-family: 'Roboto Condensed'" :placeholder="'Seleccione Semana'"
                :allowClear="false" :locale="locale" :disabled-date="disabledDate" :format="weekFormat"
                @change="updateWeek" />
        </div>

        <div v-if="type === 'custom'" class="picker-wrapper">
            <a-range-picker v-model:value="customRange" style="width: 100%; font-family: 'Roboto Condensed'"
                :placeholder="['Fecha Inicio', 'Fecha Fin']" :format="customFormat" :locale="locale" :allowClear="false"
                :presets="rangePresets" :disabled-date="disabledDate" @change="updateCustomRange" />
        </div>

        <Select v-if="!selectDisabled" v-model="type" :options="optionsType" optionLabel="label" optionValue="value"
            style="margin-bottom: 10px" />
    </div>
</template>

<script setup lang="ts">
import locale from 'ant-design-vue/es/date-picker/locale/es_ES';
import { useDateRange } from '@/composables/useDateRange';
import { watch } from 'vue';
import { Select } from 'primevue';

interface Props {
    selectDisabled?: boolean;
}
defineProps<Props>();
const emit = defineEmits(['update:dates', 'update:formattedRange']);

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

watch(
    [dates, formattedRange],
    () => {
        emit('update:dates', dates.value);
        emit('update:formattedRange', formattedRange.value);
    },
    { immediate: true }
);
</script>
