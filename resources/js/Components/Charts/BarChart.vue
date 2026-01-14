<template>
    <div class="w-full h-full">
        <div class="mb-2 flex gap-2">
            <Button v-if="showRotationToggle" type="button" @click="toggleRotate" size="small">
                {{ axisRotate === 90 ? 'Label Horizontal' : 'Label Vertical (90°)' }}
            </Button>
        </div>
        <BaseEChart ref="chartRef" :option="chartOption" />
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted, nextTick } from 'vue';
import * as echarts from 'echarts/core';
import { BarChart as EBarChart, LineChart as ELineChart } from 'echarts/charts';
import { GridComponent, LegendComponent, TooltipComponent, ToolboxComponent, TitleComponent } from 'echarts/components';
import BaseEChart from './BaseEChart.vue';
import Button from 'primevue/button';

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
    showRotationToggle?: boolean; // Nueva prop para controlar el botón
    filterZeroCategories?: boolean;
    zeroThreshold?: number;
}>(), {
    stacked: false,
    showToolbox: false,
    showValueLabels: false,
    axisLabelRotate: 0,
    showRotationToggle: true,
    filterZeroCategories: true,
    zeroThreshold: 0,
});

const emit = defineEmits(['update:axisLabelRotate', 'rotation-changed']);

const chartRef = ref<any>(null);
const axisRotate = ref<number>(props.axisLabelRotate ?? 0);
const currentChartType = ref<'bar' | 'line'>(props.stacked ? 'bar' : 'bar');

const toggleRotate = () => {
    axisRotate.value = axisRotate.value === 90 ? 0 : 90;
    emit('update:axisLabelRotate', axisRotate.value);
    emit('rotation-changed', axisRotate.value);
};

onMounted(async () => {
    await nextTick();
    const instance = chartRef.value?.getChart();
    if (instance) {
        instance.on('magictypechanged', (params: any) => {
            if (params.currentType === 'line' || params.currentType === 'bar') {
                currentChartType.value = params.currentType;
            }
        });
    }
});

// (Tus funciones formatValue, resolveFormatter y filteredData se mantienen igual...)
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
    if (!props.filterZeroCategories || categories.length === 0) return { categories, series };
    const threshold = typeof props.zeroThreshold === 'number' ? props.zeroThreshold : 0;
    const keep = categories.map((_, idx) =>
        series.some((s) => {
            const v = Number(s?.data?.[idx] ?? 0);
            return Number.isFinite(v) && Math.abs(v) > threshold;
        })
    );
    return {
        categories: categories.filter((_, idx) => keep[idx]),
        series: series.map((s) => ({ ...s, data: (s.data ?? []).filter((_, idx) => keep[idx]) }))
    };
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
        xAxis: { type: 'category', data: categories },
        yAxis: { type: 'value', show: false },
        series: (series ?? []).map((s) => ({
            name: s.name,
            type: currentChartType.value,
            stack: props.stacked ? 'total' : undefined,
            label: props.showValueLabels ? {
                show: true,
                formatter: resolveFormatter(props.labelFormatter),
                rich: props.labelRich,
                rotate: axisRotate.value,
                position: axisRotate.value === 90 ? 'inside' : 'top'
            } : undefined,
            emphasis: { focus: 'series' },
            data: s.data,
        })),
    };
});
</script>
