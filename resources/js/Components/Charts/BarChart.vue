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
}>(), {
    stacked: false,
    showToolbox: false,
    showValueLabels: false,
    axisLabelRotate: 0,
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

const chartOption = computed(() => {
    const legendVisible = (props.series?.length ?? 0) > 0;

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
        xAxis: { type: 'category', data: props.categories, axisLabel: { rotate: props.axisLabelRotate } },
        yAxis: { type: 'value', show: false },
        series: (props.series ?? []).map((s) => ({
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
