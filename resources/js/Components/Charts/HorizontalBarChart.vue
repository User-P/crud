<template>
    <BaseEChart ref="chartRef" :option="chartOption" />
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import * as echarts from 'echarts/core';
import { BarChart as EBarChart } from 'echarts/charts';
import { GridComponent, LegendComponent, TooltipComponent, TitleComponent } from 'echarts/components';
import BaseEChart from './BaseEChart.vue';

echarts.use([EBarChart, GridComponent, LegendComponent, TooltipComponent, TitleComponent]);

const getContrastColor = (hexColor: string): string => {
    const r = parseInt(hexColor.slice(1, 3), 16);
    const g = parseInt(hexColor.slice(3, 5), 16);
    const b = parseInt(hexColor.slice(5, 7), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance > 0.5 ? '#000000' : '#ffffff';
};

type SeriesItem = {
    name: string;
    data: Array<number | null>;
};

interface Props {
    title?: string;
    subtitle?: string;
    categories: string[];
    series: SeriesItem[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (event: 'bar-click', params: any): void;
}>();

const chartRef = ref<InstanceType<typeof BaseEChart> | null>(null);
let clickHandler: ((params: any) => void) | null = null;

const chartOption = computed(() => {
    const categories = Array.isArray(props.categories) ? props.categories : [];
    const series = Array.isArray(props.series) ? props.series : [];

    const seriesWithTotal = series.map((s) => ({
        ...s,
        total: s.data.reduce((a, b) => a + (b || 0), 0),
    }));

    return {
        title: props.title ? { text: props.title, subtext: props.subtitle, left: 'center' } : undefined,
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        legend: {
            top: 0,
            formatter: (name: string) => {
                const s = seriesWithTotal.find((s) => s.name === name);
                return s ? `${s.name}: (${s.total})` : name;
            },
        },
        grid: { left: 10, right: 10, bottom: 0, top: 90, containLabel: true },
        xAxis: { type: 'value' },
        yAxis: {
            type: 'category',
            data: categories,
            axisLabel: { show: false },
        },
        series: seriesWithTotal.map((s) => ({
            name: s.name,
            type: 'bar',
            stack: 'total',
            emphasis: { focus: 'series' },
            label: {
                show: true,
                valueAnimation: true,
                position: 'insideLeft',
                formatter: '{a}: {c}',
                color: '#ffffff',
                fontSize: 12,
                fontWeight: 600,
                backgroundColor: 'rgba(0, 0, 0, 0.35)',
                borderRadius: 4,
                padding: [2, 6],
                overflow: 'truncate',
            },
            data: s.data,
        })),
    };
});

const bindClick = () => {
    const chart = chartRef.value?.getChart?.();
    if (!chart || clickHandler) return;

    clickHandler = (params: any) => {
        emit('bar-click', params);
    };

    chart.on('click', clickHandler);
};

onMounted(async () => {
    await nextTick();
    bindClick();
});

watch(chartOption, () => {
    bindClick();
});

onBeforeUnmount(() => {
    const chart = chartRef.value?.getChart?.();
    if (chart && clickHandler) {
        chart.off('click', clickHandler);
    }
    clickHandler = null;
});
</script>
