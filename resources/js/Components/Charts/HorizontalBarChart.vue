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
            barBorderRadius: [0, 6, 6, 0],
            emphasis: { focus: 'series' },
            label: {
                show: true,
                valueAnimation: true,
                position: 'insideLeft',
                formatter: '{a}: {c}',
                color: '#ffffff',
                fontSize: 12,
                fontWeight: 600,
                backgroundColor: 'rgba(0, 0, 0, 0.4)',
                borderRadius: 6,
                padding: [4, 8],
                overflow: 'truncate',
            },
            itemStyle: {
                shadowBlur: 4,
                shadowColor: 'rgba(0, 0, 0, 0.15)',
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
