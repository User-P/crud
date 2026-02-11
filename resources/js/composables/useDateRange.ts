import dayjs, { Dayjs } from 'dayjs';
import isoWeek from 'dayjs/plugin/isoWeek';
import localeData from 'dayjs/plugin/localeData';
import 'dayjs/locale/es';
import { ref, computed, watch } from 'vue';

dayjs.extend(isoWeek);
dayjs.extend(localeData);
dayjs.locale('es');

interface Dates {
    start: string;
    end: string;
}

export interface UseDateRangeOptions {
    /** Tipo inicial: 'week' o 'custom'. Por defecto 'week'. */
    initialType?: 'week' | 'custom';
    /** Fecha inicial para modo semanal (cualquier día de la semana, ej. '2025-02-10'). Por defecto semana actual. */
    initialWeek?: string;
    /** Rango inicial para modo personalizado ({ start, end } en 'YYYY-MM-DD'). Por defecto mes actual. */
    initialRange?: { start: string; end: string };
}

function parseWeekOption(initialWeek: string | undefined): Dayjs {
    if (!initialWeek || !initialWeek.trim()) return dayjs().startOf('isoWeek');
    const parsed = dayjs(initialWeek);
    return parsed.isValid() ? parsed.startOf('isoWeek') : dayjs().startOf('isoWeek');
}

function parseRangeOption(initialRange: { start: string; end: string } | undefined): [Dayjs, Dayjs] {
    if (!initialRange?.start || !initialRange?.end) {
        return [dayjs().startOf('month'), dayjs().endOf('month')];
    }
    const start = dayjs(initialRange.start);
    const end = dayjs(initialRange.end);
    const valid = start.isValid() && end.isValid() && !start.isAfter(end);
    return valid ? [start, end] : [dayjs().startOf('month'), dayjs().endOf('month')];
}

export const useDateRange = (options: UseDateRangeOptions = {}) => {
    const { initialType = 'week', initialWeek, initialRange } = options;

    const type = ref<'week' | 'custom'>(initialType);
    const selectedWeek = ref<Dayjs>(parseWeekOption(initialWeek));
    const customRange = ref<[Dayjs, Dayjs]>(parseRangeOption(initialRange));

    const dates = computed<Dates>(() => {
        if (type.value === 'week') {
            return {
                start: selectedWeek.value.startOf('isoWeek').format('YYYY-MM-DD'),
                end: selectedWeek.value.endOf('isoWeek').format('YYYY-MM-DD'),
            };
        } else {
            return {
                start: customRange.value[0].format('YYYY-MM-DD'),
                end: customRange.value[1].format('YYYY-MM-DD'),
            };
        }
    });

    const rangePresets = [
        { label: 'Últimos 7 Días', value: [dayjs().add(-7, 'd'), dayjs()] },
        { label: 'Últimos 14 Días', value: [dayjs().add(-14, 'd'), dayjs()] },
        { label: 'Último Mes', value: [dayjs().add(-30, 'd'), dayjs()] },
        { label: 'Últimos 2 Meses', value: [dayjs().add(-60, 'd'), dayjs()] },
        { label: 'Últimos 3 Meses', value: [dayjs().add(-90, 'd'), dayjs()] },
        { label: 'Últimos 6 Meses ', value: [dayjs().add(-180, 'd'), dayjs()] },
        { label: 'Últimos 9 Meses ', value: [dayjs().add(-270, 'd'), dayjs()] },
        { label: 'Último año', value: [dayjs().add(-365, 'd'), dayjs()] },
    ];

    const disabledDate = (current: Dayjs) => current && current > dayjs().endOf('day');

    const updateWeek = (date: Dayjs) => {
        if (date) selectedWeek.value = date.startOf('isoWeek');
    };

    const updateCustomRange = (range: [Dayjs, Dayjs]) => {
        if (Array.isArray(range) && range.length === 2) {
            customRange.value = range;
        }
    };

    const weekFormat = (value: Dayjs) => {
        if (!value) return '';
        const week = value.isoWeek();
        const start = value.startOf('isoWeek').format('DD MMMM YYYY');
        const end = value.endOf('isoWeek').format('DD MMMM YYYY');
        return `Semana ${week} (${start} - ${end})`;
    };


    const customFormat = (value: Dayjs) => {
        if (!value) return '';
        return value.format('DD MMMM YYYY');
    };


    const formattedDateEs = (date: Dayjs) =>
        date.format('dddd, DD [de] MMMM [de] YYYY');

    const formattedRange = ref('');

    watch([type, selectedWeek, customRange], () => {
        if (type.value === 'week') {
            formattedRange.value = `Del ${formattedDateEs(selectedWeek.value.startOf('isoWeek'))} al ${formattedDateEs(selectedWeek.value.endOf('isoWeek'))}`;
        } else {
            formattedRange.value = `Del ${formattedDateEs(customRange.value[0])} al ${formattedDateEs(customRange.value[1])}`;
        }
    }, { immediate: true });

    return {
        type,
        dates,
        selectedWeek,
        customRange,
        rangePresets,
        formattedRange,
        customFormat,
        disabledDate,
        updateWeek,
        updateCustomRange,
        weekFormat,
    };
};
