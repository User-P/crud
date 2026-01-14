<template>
    <BaseEChart :option="chartOption" />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import * as echarts from 'echarts/core';
import { BarChart as EBarChart, LineChart as ELineChart } from 'echarts/charts';
import { GridComponent, LegendComponent, TooltipComponent, ToolboxComponent, TitleComponent } from 'echarts/components';
import BaseEChart from './BaseEChart.vue';

echarts.use([EBarChart, ELineChart, GridComponent, LegendComponent, TooltipComponent, ToolboxComponent, TitleComponent]);

const props = withDefaults(defineProps<{
    title?: string;
    subtitle?: string;
    categories: string[];
    series: { name: string; data: number[] }[];
    stacked?: boolean;
    showToolbox?: boolean;
    showValueLabels?: boolean;
    labelFormatter?: string | ((params: any) => string);
    labelRich?: Record<string, any>;
    axisLabelRotate?: number;
    filterZeroCategories?: boolean;
    zeroThreshold?: number;
}>(), {
    stacked: false,
    showToolbox: false,
    showValueLabels: false,
    axisLabelRotate: 0,
    filterZeroCategories: true,
    zeroThreshold: 0,
});

const formatValue = (raw: unknown) => {
    const n = Number(raw);
    if (!Number.isFinite(n)) return raw == null ? '' : String(raw);
    return String(n);
};

const resolveFormatter = (formatter?: typeof props.labelFormatter) => {
    if (!formatter) return undefined;
    if (typeof formatter === 'function') return formatter;
    return (params: any) => {
        const a = params?.seriesName ?? '';
        const b = params?.name ?? '';
        const c = formatValue(Array.isArray(params.value) ? params.value[1] : params.value);
        return String(formatter).replace(/\{a\}/g, a).replace(/\{b\}/g, b).replace(/\{c\}/g, c);
    };
};

const filteredData = computed(() => {
    const categories = Array.isArray(props.categories) ? props.categories : [];
    const series = Array.isArray(props.series) ? props.series : [];

    if (!props.filterZeroCategories || categories.length === 0) {
        return { categories, series };
    }

    const threshold = typeof props.zeroThreshold === 'number' ? props.zeroThreshold : 0;
    const keep = categories.map((_, idx) =>
        series.some((s) => {
            const v = Number(s?.data?.[idx] ?? 0);
            if (!Number.isFinite(v)) return false;
            return Math.abs(v) > threshold;
        })
    );

    const filteredCategories = categories.filter((_, idx) => keep[idx]);
    const filteredSeries = series.map((s) => ({ ...s, data: (s.data ?? []).filter((_, idx) => keep[idx]) }));

    return { categories: filteredCategories, series: filteredSeries };
});

const chartOption = computed(() => {
    const { categories, series } = filteredData.value;
    const legendVisible = (series?.length ?? 0) > 0;

    return {
        title: props.title ? { text: props.title, subtext: props.subtitle, left: 'center' } : undefined,
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        legend: legendVisible ? { bottom: 0 } : undefined,
        toolbox: props.showToolbox
            ? {
                show: true,
                feature: {
                    magicType: { show: true, type: ['line', 'bar', 'stack'] },
                    saveAsImage: { show: true },
                },
            }
            : undefined,
        grid: { left: 40, right: 20, bottom: 60, top: props.title ? 60 : 30, containLabel: true },
        xAxis: { type: 'category', data: categories, axisLabel: { rotate: props.axisLabelRotate } },
        yAxis: { type: 'value', show: false },
        series: (series ?? []).map((s) => ({
            name: s.name,
            type: 'bar',
            stack: props.stacked ? 'total' : undefined,
            label: props.showValueLabels ? { show: true, formatter: resolveFormatter(props.labelFormatter), rich: props.labelRich } : undefined,
            emphasis: { focus: 'series' },
            data: s.data,
        })),
    };
});
</script>
