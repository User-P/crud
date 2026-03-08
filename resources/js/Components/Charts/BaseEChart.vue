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
    /** Si no se pasa, se usa cosmos-light / cosmos-dark según data-theme */
    theme?: string;
}

const props = defineProps<Props>();

const chartRef = ref<HTMLDivElement | null>(null);
let chart: EChartsType | null = null;
let observer: ResizeObserver | null = null;
let themeObserver: MutationObserver | null = null;

function getThemeName(): string {
    if (typeof document === 'undefined') return 'cosmos-light';
    return document.documentElement.getAttribute('data-theme') === 'dark' ? 'cosmos-dark' : 'cosmos-light';
}

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

    const themeName = props.theme ?? getThemeName();

    if (!chart) {
        chart = echarts.init(chartRef.value, themeName);
    } else {
        const currentTheme = getThemeName();
        if (currentTheme !== themeName) {
            chart.dispose();
            chart = echarts.init(chartRef.value, currentTheme);
        }
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

    themeObserver = new MutationObserver(() => {
        if (props.theme) return;
        const el = chartRef.value;
        if (!el || !chart) return;
        const next = getThemeName();
        chart.dispose();
        chart = echarts.init(el, next);
        chart.setOption(makeWheelPassiveForChart(props.option), true);
    });
    themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
});

onBeforeUnmount(() => {
    themeObserver?.disconnect();
    themeObserver = null;
    observer?.disconnect();
    chart?.dispose();
    chart = null;
});

defineExpose({
    getChart: () => chart,
});
</script>
