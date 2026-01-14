<template>
    <div class="w-full h-full">
        <BaseEChart ref="chartRef" :option="chartOption" />
    </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, nextTick } from 'vue';
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
    filterZeroCategories?: boolean;
    zeroThreshold?: number;
}>(), {
    stacked: false,
    showToolbox: false,
    showValueLabels: false,
    filterZeroCategories: true,
    zeroThreshold: 0,
});

const chartRef = ref<any>(null);

// 1. Estado que rastrea el tipo de gráfico actual (empieza como bar o según props)
const currentChartType = ref<'bar' | 'line'>('bar');

// 2. Escuchar el Toolbox de ECharts para actualizar el estado automáticamente
onMounted(async () => {
    await nextTick();
    const instance = chartRef.value?.getChart();
    if (instance) {
        instance.on('magictypechanged', (params: any) => {
            // params.currentType devuelve 'bar', 'line' o 'stack'
            if (params.currentType === 'line' || params.currentType === 'bar') {
                currentChartType.value = params.currentType;
            } else if (params.currentType === 'stack') {
                currentChartType.value = 'bar'; // Stack usualmente implica barras
            }
        });
    }
});

const resolveFormatter = (formatter?: typeof props.labelFormatter) => {
    if (!formatter) return undefined;
    if (typeof formatter === 'function') return formatter;
    return (params: any) => {
        const v = Array.isArray(params.value) ? params.value[1] : params.value;
        return String(formatter)
            .replace(/\{a\}/g, params?.seriesName ?? '')
            .replace(/\{b\}/g, params?.name ?? '')
            .replace(/\{c\}/g, String(v));
    };
};

const filteredData = computed(() => {
    const categories = props.categories || [];
    const series = props.series || [];
    if (!props.filterZeroCategories || categories.length === 0) return { categories, series };

    const threshold = props.zeroThreshold ?? 0;
    const keep = categories.map((_, idx) =>
        series.some((s) => Math.abs(Number(s?.data?.[idx] ?? 0)) > threshold)
    );
    return {
        categories: categories.filter((_, idx) => keep[idx]),
        series: series.map((s) => ({ ...s, data: (s.data ?? []).filter((_, idx) => keep[idx]) }))
    };
});

const chartOption = computed(() => {
    const { categories, series } = filteredData.value;

    const isBar = currentChartType.value === 'bar';

    return {
        title: props.title ? { text: props.title, subtext: props.subtitle, left: 'center' } : undefined,
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        legend: { bottom: 0 },
        toolbox: props.showToolbox ? {
            show: true,
            feature: {
                magicType: { show: true, type: ['line', 'bar', 'stack'] },
                saveAsImage: { show: true },
            },
        } : undefined,
        grid: { left: 40, right: 20, bottom: 60, top: props.title ? 60 : 30, containLabel: true },
        xAxis: { type: 'category', data: categories },
        yAxis: { type: 'value', show: false },
        series: (series ?? []).map((s) => ({
            name: s.name,
            type: currentChartType.value, // Mantiene el tipo elegido en el toolbox
            stack: props.stacked ? 'total' : undefined,
            label: props.showValueLabels ? {
                show: true,
                rotate: isBar ? 90 : 0,
                position: isBar ? 'inside' : 'top',
                formatter: resolveFormatter(props.labelFormatter),
                rich: props.labelRich,
            } : undefined,
            emphasis: { focus: 'series' },
            data: s.data,
        })),
    };
});
</script>
