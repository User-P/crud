import dayjs from 'dayjs';
import isoWeek from 'dayjs/plugin/isoWeek';

dayjs.extend(isoWeek);

export interface DateRangeResult {
    start: string;
    end: string;
}

/**
 * Composable con helpers para calcular rangos de fechas habituales.
 * Útil para inicializar CustomPicker, filtros o peticiones API.
 */
export function useDateRangePresets() {
    const now = () => dayjs();

    /** Última semana (lunes a domingo ISO). */
    function getLastWeek(): DateRangeResult {
        const end = now().subtract(1, 'week').endOf('isoWeek');
        const start = end.subtract(6, 'day');
        return { start: start.format('YYYY-MM-DD'), end: end.format('YYYY-MM-DD') };
    }

    /** Primer día de la última semana (para usar con initialWeek en modo semanal). */
    function getLastWeekStart(): string {
        return now().subtract(1, 'week').startOf('isoWeek').format('YYYY-MM-DD');
    }

    /** Últimos 7 días (hoy incluido). */
    function getLast7Days(): DateRangeResult {
        const end = now();
        const start = end.subtract(6, 'day');
        return { start: start.format('YYYY-MM-DD'), end: end.format('YYYY-MM-DD') };
    }

    /** Últimos 14 días. */
    function getLast14Days(): DateRangeResult {
        const end = now();
        const start = end.subtract(13, 'day');
        return { start: start.format('YYYY-MM-DD'), end: end.format('YYYY-MM-DD') };
    }

    /** Último mes (30 días). */
    function getLastMonth(): DateRangeResult {
        const end = now();
        const start = end.subtract(29, 'day');
        return { start: start.format('YYYY-MM-DD'), end: end.format('YYYY-MM-DD') };
    }

    /** Últimos 3 meses (~90 días). */
    function getLast3Months(): DateRangeResult {
        const end = now();
        const start = end.subtract(89, 'day');
        return { start: start.format('YYYY-MM-DD'), end: end.format('YYYY-MM-DD') };
    }

    /** Últimos 6 meses (~180 días). */
    function getLast6Months(): DateRangeResult {
        const end = now();
        const start = end.subtract(179, 'day');
        return { start: start.format('YYYY-MM-DD'), end: end.format('YYYY-MM-DD') };
    }

    /** Último año (365 días). */
    function getLastYear(): DateRangeResult {
        const end = now();
        const start = end.subtract(364, 'day');
        return { start: start.format('YYYY-MM-DD'), end: end.format('YYYY-MM-DD') };
    }

    /** Mes actual (del día 1 al último día). */
    function getCurrentMonth(): DateRangeResult {
        const start = now().startOf('month');
        const end = now().endOf('month');
        return { start: start.format('YYYY-MM-DD'), end: end.format('YYYY-MM-DD') };
    }

    return {
        getLastWeek,
        getLastWeekStart,
        getLast7Days,
        getLast14Days,
        getLastMonth,
        getLast3Months,
        getLast6Months,
        getLastYear,
        getCurrentMonth,
    };
}

/** Nombres de presets que puede aceptar CustomPicker en initialPreset. */
export type DateRangePresetKey =
    | 'lastWeek'
    | 'last7Days'
    | 'last14Days'
    | 'lastMonth'
    | 'last3Months'
    | 'last6Months'
    | 'lastYear'
    | 'currentMonth';

/** Resuelve un preset por nombre a opciones para useDateRange (para uso interno del componente). */
export function resolveDateRangePreset(
    key: DateRangePresetKey
): { initialType: 'week' | 'custom'; initialWeek?: string; initialRange?: DateRangeResult } {
    const presets = useDateRangePresets();
    switch (key) {
        case 'lastWeek':
            return { initialType: 'week', initialWeek: presets.getLastWeekStart() };
        case 'last7Days':
            return { initialType: 'custom', initialRange: presets.getLast7Days() };
        case 'last14Days':
            return { initialType: 'custom', initialRange: presets.getLast14Days() };
        case 'lastMonth':
            return { initialType: 'custom', initialRange: presets.getLastMonth() };
        case 'last3Months':
            return { initialType: 'custom', initialRange: presets.getLast3Months() };
        case 'last6Months':
            return { initialType: 'custom', initialRange: presets.getLast6Months() };
        case 'lastYear':
            return { initialType: 'custom', initialRange: presets.getLastYear() };
        case 'currentMonth':
            return { initialType: 'custom', initialRange: presets.getCurrentMonth() };
        default:
            return { initialType: 'week' };
    }
}
