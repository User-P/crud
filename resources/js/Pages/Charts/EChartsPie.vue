<template>
    <AdminLayout title="Gráfica de pastel" subtitle="Ejemplo sencillo de Apache ECharts" :breadcrumbs="[
        { name: 'Dashboard', href: '/dashboard' },
        { name: 'Charts', href: '/charts' },
        { name: 'Pie' },
    ]">
        <div class="mx-auto max-w-5xl space-y-4 p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">Apache ECharts + Vue</h1>
                    <p class="text-sm text-slate-500">
                        Ejemplo basico de grafica de pastel con datos enviados desde Laravel via Inertia.
                    </p>
                </div>
                <a href="https://echarts.apache.org/en/option.html#series-pie" target="_blank" rel="noreferrer"
                    class="text-sm font-medium text-blue-600 hover:underline">
                    Documentacion
                </a>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <div ref="chartRef" class="h-96 w-full"></div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import type { ComposeOption, EChartsType } from "echarts/core";
import * as echarts from "echarts/core";
import { PieChart, type PieSeriesOption } from "echarts/charts";
import {
    LegendComponent,
    TitleComponent,
    TooltipComponent,
    type LegendComponentOption,
    type TitleComponentOption,
    type TooltipComponentOption,
} from "echarts/components";
import { CanvasRenderer } from "echarts/renderers";
import AdminLayout from "@/Layouts/AdminLayout.vue";

type PieOption = ComposeOption<
    TitleComponentOption | TooltipComponentOption | LegendComponentOption | PieSeriesOption
>;

type Slice = {
    value: number;
    name: string;
};

const props = defineProps<{
    data: Slice[];
    title?: string;
}>();

echarts.use([TitleComponent, TooltipComponent, LegendComponent, PieChart, CanvasRenderer]);

const chartRef = ref<HTMLDivElement | null>(null);
let chart: EChartsType | null = null;

const resize = () => chart?.resize();

const renderChart = () => {
    if (!chartRef.value) return;

    if (!chart) {
        chart = echarts.init(chartRef.value);
        window.addEventListener("resize", resize);
    }

    const option: PieOption = {
        title: {
            text: props.title ?? "Distribucion por categoria",
            left: "center",
            textStyle: {
                fontWeight: "600",
            },
        },
        tooltip: {
            trigger: "item",
            formatter: "{b}: {c} ({d}%)",
        },
        legend: {
            orient: "vertical",
            left: "left",
        },
        series: [
            {
                name: "Categorias",
                type: "pie",
                radius: "60%",
                data: props.data,
                emphasis: {
                    itemStyle: {
                        shadowBlur: 8,
                        shadowOffsetX: 0,
                        shadowColor: "rgba(0, 0, 0, 0.2)",
                    },
                },
            },
        ],
    };

    chart.setOption(option);
};

onMounted(renderChart);

watch(
    () => props.data,
    () => renderChart(),
    { deep: true }
);

onBeforeUnmount(() => {
    window.removeEventListener("resize", resize);
    chart?.dispose();
    chart = null;
});
</script>
