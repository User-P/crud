<template>
    <div ref="chartRef" class="w-full h-full"></div>
</template>
<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch, nextTick } from 'vue';
import * as echarts from 'echarts/core';
import type { EChartsType } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([CanvasRenderer]);

interface Props {
    option: Record<string, any>;
    theme?: string | object;
}

const props = defineProps<Props>();

const chartRef = ref<HTMLDivElement | null>(null);
let chart: EChartsType | null = null;
let observer: ResizeObserver | null = null;

function makeWheelPassiveForChart(option: Record<string, any>) {
    const safe = { ...option };

    const dz = safe.dataZoom
        ? Array.isArray(safe.dataZoom)
            ? safe.dataZoom
            : [safe.dataZoom]
        : [];

    const patched = dz.map((z) => {
        if (z && (z.type === 'inside' || !z.type)) {
            return {
                ...z,
                zoomLock: true,
                zoomOnMouseWheel: false,
                moveOnMouseWheel: false,
            };
        }
        return z;
    });

    if (patched.length) {
        safe.dataZoom = patched;
    }

    return safe;
}

const renderChart = () => {
    if (!chartRef.value) return;

    const { clientWidth, clientHeight } = chartRef.value;
    if (clientWidth === 0 || clientHeight === 0) return;

    if (!chart) {
        chart = echarts.init(chartRef.value, props.theme);
    }

    const safeOption = makeWheelPassiveForChart(props.option);
    chart.setOption(safeOption, true);
};

watch(
    () => props.option,
    () => renderChart(),
    { deep: true }
);

onMounted(async () => {
    await nextTick();
    renderChart();

    if (chartRef.value) {
        observer = new ResizeObserver(() => {
            chart?.resize();
        });
        observer.observe(chartRef.value);
    }
});

onBeforeUnmount(() => {
    observer?.disconnect();
    chart?.dispose();
    chart = null;
});

defineExpose({
    getChart: () => chart,
});
</script>
