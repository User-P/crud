<template>
    <BaseEChart :option="chartOption" />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import * as echarts from 'echarts/core';
import { BarChart as EBarChart, LineChart as ELineChart, type BarSeriesOption } from 'echarts/charts';
import {
    GridComponent,
    LegendComponent,
    TitleComponent,
    TooltipComponent,
    ToolboxComponent,
    type GridComponentOption,
    type LegendComponentOption,
    type TitleComponentOption,
    type TooltipComponentOption,
    type ToolboxComponentOption,
} from 'echarts/components';
import type { ComposeOption } from 'echarts/core';
import BaseEChart from './BaseEChart.vue';

echarts.use([
    EBarChart,
    ELineChart,
    GridComponent,
    LegendComponent,
    TitleComponent,
    TooltipComponent,
    ToolboxComponent,
]);

type Option = ComposeOption<
    | BarSeriesOption
    | GridComponentOption
    | LegendComponentOption
    | TitleComponentOption
    | TooltipComponentOption
    | ToolboxComponentOption
>;

type Series = {
    name: string;
    data: number[];
    barGap?: string | number;
};

type LegendPosition = 'top' | 'bottom';
type BarLabelOption = NonNullable<BarSeriesOption['label']>;

const props = withDefaults(
    defineProps<{
        title?: string;
        subtitle?: string;
        categories: string[];
        series: Series[];
        stacked?: boolean;
        barGap?: string | number;
        barWidth?: number | string;
        barMaxWidth?: number | string;
        barCategoryGap?: number | string;
        filterZeroCategories?: boolean;
        zeroThreshold?: number;
        showLegend?: boolean;
        legendPosition?: LegendPosition;
        showValueLabels?: boolean;
        hideZeroValues?: boolean;
        lineLabelOption?: BarLabelOption;
        lineLabelLayout?: Record<string, any>;
        labelOption?: BarLabelOption;
        labelPosition?: BarLabelOption['position'];
        labelDistance?: BarLabelOption['distance'];
        labelAlign?: BarLabelOption['align'];
        labelVerticalAlign?: BarLabelOption['verticalAlign'];
        labelRotate?: BarLabelOption['rotate'];
        labelFontSize?: number;
        labelFormatter?: BarLabelOption['formatter'];
        labelRich?: BarLabelOption['rich'];
        valueDecimals?: number;
        valuePrefix?: string;
        valueSuffix?: string;
        valueFormatter?: (value: number, params: any) => string;
        axisLabelRotate?: number;
        showToolbox?: boolean;
        toolbox?: ToolboxComponentOption;
        extendOption?: Partial<Option>;
    }>(),
    {
        stacked: false,
        barGap: undefined,
        barWidth: undefined,
        barMaxWidth: undefined,
        barCategoryGap: undefined,
        filterZeroCategories: false,
        zeroThreshold: 0,
        showLegend: true,
        legendPosition: 'bottom',
        showValueLabels: false,
        hideZeroValues: false,
        lineLabelOption: undefined,
        lineLabelLayout: undefined,
        labelPosition: 'insideBottom',
        labelDistance: 15,
        labelAlign: 'left',
        labelVerticalAlign: 'middle',
        labelRotate: 90,
        labelFontSize: 12,
        axisLabelRotate: 0,
        showToolbox: false,
        extendOption: () => ({}),
    }
);

const formatValue = (raw: unknown, params?: any) => {
    const numeric = Number(raw);
    if (!Number.isFinite(numeric)) {
        return raw === null || raw === undefined ? '' : String(raw);
    }

    if (props.valueFormatter) {
        return props.valueFormatter(numeric, params);
    }

    const decimals = typeof props.valueDecimals === 'number' ? props.valueDecimals : undefined;
    const formatted =
        decimals === undefined ? String(numeric) : numeric.toFixed(Math.max(0, decimals));

    return `${props.valuePrefix ?? ''}${formatted}${props.valueSuffix ?? ''}`;
};

const formatLabelTemplate = (template: string, params: any, valueLabel: string) =>
    template
        .replace(/\{a\}/g, params?.seriesName ?? '')
        .replace(/\{b\}/g, params?.name ?? '')
        .replace(/\{c\}/g, valueLabel);

const resolveLabelText = (params: any, formatter?: BarLabelOption['formatter']) => {
    const rawValue = Array.isArray(params.value) ? params.value[1] : params.value;
    if (props.hideZeroValues && Number(rawValue) === 0) return '';

    if (typeof formatter === 'function') {
        return formatter(params);
    }

    const valueLabel = formatValue(rawValue, params);

    if (typeof formatter === 'string') {
        return formatLabelTemplate(formatter, params, valueLabel);
    }

    return valueLabel;
};

const defaultLabelOption = computed<BarLabelOption | undefined>(() => {
    if (!props.showValueLabels) return undefined;

    const formatter = props.hideZeroValues
        ? (params: any) => resolveLabelText(params, props.labelFormatter)
        : props.labelFormatter ?? '{c}';

    return {
        show: true,
        position: props.labelPosition,
        distance: props.labelDistance,
        align: props.labelAlign,
        verticalAlign: props.labelVerticalAlign,
        rotate: props.labelRotate,
        formatter,
        fontSize: props.labelFontSize,
        rich: props.labelRich,
    };
});

const resolvedLabelOption = computed<BarLabelOption | undefined>(() => {
    if (!props.labelOption) return defaultLabelOption.value;
    if (!props.showValueLabels) return undefined;
    if (!props.hideZeroValues) return props.labelOption;

    const { formatter, ...rest } = props.labelOption;

    return {
        ...rest,
        formatter: (params: any) => resolveLabelText(params, formatter),
    };
});

const defaultToolbox: ToolboxComponentOption = {
    show: true,
    orient: 'vertical',
    left: 'left',
    top: 'center',
    feature: {
        mark: { show: true },
        magicType: { show: true, type: ['line', 'bar', 'stack'] },
    },
};

const filteredData = computed(() => {
    const categories = Array.isArray(props.categories) ? props.categories : [];
    const series = Array.isArray(props.series) ? props.series : [];

    if (!props.filterZeroCategories || categories.length === 0) {
        return { categories, series };
    }

    const threshold = typeof props.zeroThreshold === 'number' ? props.zeroThreshold : 0;
    const keep = categories.map((_, idx) =>
        series.some((serie) => {
            const value = Number(serie?.data?.[idx] ?? 0);
            if (!Number.isFinite(value)) return false;
            return Math.abs(value) > threshold;
        })
    );

    const filteredCategories = categories.filter((_, idx) => keep[idx]);
    const filteredSeries = series.map((serie) => ({
        ...serie,
        data: (serie?.data ?? []).filter((_, idx) => keep[idx]),
    }));

    return { categories: filteredCategories, series: filteredSeries };
});

const chartOption = computed<Option>(() => {
    const { categories, series } = filteredData.value;
    const legendVisible = props.showLegend && series.length > 0;
    const legendAtTop = legendVisible && props.legendPosition === 'top';
    const legendAtBottom = legendVisible && props.legendPosition === 'bottom';
    const gridTop = (props.title ? 60 : 30) + (legendAtTop ? 20 : 0);
    const gridBottom = 20 + (legendAtBottom ? 40 : 0);

    return {
        title: props.title ? { text: props.title, subtext: props.subtitle, left: 'center' } : undefined,
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' },
            valueFormatter: (value: any) => formatValue(value),
        },
        legend: legendVisible
            ? {
                bottom: legendAtBottom ? 0 : undefined,
                top: legendAtTop ? 0 : undefined,
            }
            : undefined,
        toolbox: props.showToolbox ? props.toolbox ?? defaultToolbox : undefined,
        grid: { left: 40, right: 20, bottom: gridBottom, top: gridTop, containLabel: true },
        xAxis: {
            type: 'category',
            data: categories,
            axisTick: { alignWithLabel: true },
            axisLabel: { rotate: props.axisLabelRotate, hideOverlap: true },
        },
        yAxis: { type: 'value', show: false },
        series: series.map((serie) => ({
            name: serie.name,
            type: 'bar',
            stack: props.stacked ? 'total' : undefined,
            barGap: serie.barGap ?? props.barGap,
            barWidth: props.barWidth,
            barMaxWidth: props.barMaxWidth,
            barCategoryGap: props.barCategoryGap,
            label: resolvedLabelOption.value,
            emphasis: { focus: 'series' },
            data: serie.data,
        })),
        ...props.extendOption,
    };
});
</script>
