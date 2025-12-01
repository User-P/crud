<template>
    <AdminLayout
        title="Charts"
        subtitle="Galería de ejemplos con Apache ECharts y datos ficticios"
        :breadcrumbs="[
            { name: 'Dashboard', href: '/dashboard' },
            { name: 'Charts' },
        ]"
    >
        <div class="space-y-6">
            <header class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Apache ECharts</h2>
                    <p class="text-sm text-slate-500">Ejemplos de líneas, barras, pastel, radar y gauge.</p>
                </div>
                <a
                    href="https://echarts.apache.org/en/option.html"
                    target="_blank"
                    rel="noreferrer"
                    class="inline-flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    <i class="pi pi-external-link text-xs" />
                    Documentación
                </a>
            </header>

            <div class="grid gap-6 lg:grid-cols-2">
                <ChartCard title="Tráfico mensual" description="Comparativa web vs app móvil por mes">
                    <div ref="lineRef" class="h-80 w-full"></div>
                </ChartCard>

                <ChartCard title="Ventas vs objetivo" description="Barras apiladas por trimestre">
                    <div ref="barRef" class="h-80 w-full"></div>
                </ChartCard>

                <ChartCard title="Participación por canal" description="Gráfico de pastel clásico">
                    <div ref="pieRef" class="h-80 w-full"></div>
                </ChartCard>

                <ChartCard title="Participación con dona" description="Variación tipo doughnut">
                    <div ref="donutRef" class="h-80 w-full"></div>
                </ChartCard>

                <ChartCard title="Salud operacional" description="Radar de KPIs por planta">
                    <div ref="radarRef" class="h-96 w-full"></div>
                </ChartCard>

                <ChartCard title="Disponibilidad promedio" description="Gauge de uptime global">
                    <div ref="gaugeRef" class="h-96 w-full"></div>
                </ChartCard>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { defineComponent, h, onBeforeUnmount, onMounted, ref } from 'vue'
import { type ComposeOption, type EChartsType } from 'echarts/core'
import * as echarts from 'echarts/core'
import {
    LineChart,
    BarChart,
    PieChart,
    RadarChart,
    GaugeChart,
    type LineSeriesOption,
    type BarSeriesOption,
    type PieSeriesOption,
    type RadarSeriesOption,
    type GaugeSeriesOption,
} from 'echarts/charts'
import {
    TitleComponent,
    TooltipComponent,
    LegendComponent,
    GridComponent,
    type TitleComponentOption,
    type TooltipComponentOption,
    type LegendComponentOption,
    type GridComponentOption,
} from 'echarts/components'
import { CanvasRenderer } from 'echarts/renderers'
import AdminLayout from '@/Layouts/AdminLayout.vue'

type ChartOption = ComposeOption<
    | TitleComponentOption
    | TooltipComponentOption
    | LegendComponentOption
    | GridComponentOption
    | LineSeriesOption
    | BarSeriesOption
    | PieSeriesOption
    | RadarSeriesOption
    | GaugeSeriesOption
>

type Series = { name: string; data: number[] }
type RadarIndicator = { name: string; max: number }
type RadarPoint = { name: string; value: number[] }

const props = defineProps<{
    pieData: { value: number; name: string }[]
    months: string[]
    lineSeries: Series[]
    barCategories: string[]
    barSeries: Series[]
    radarIndicators: RadarIndicator[]
    radarData: RadarPoint[]
    gaugeValue: number
}>()

echarts.use([
    LineChart,
    BarChart,
    PieChart,
    RadarChart,
    GaugeChart,
    TitleComponent,
    TooltipComponent,
    LegendComponent,
    GridComponent,
    CanvasRenderer,
])

const lineRef = ref<HTMLDivElement | null>(null)
const barRef = ref<HTMLDivElement | null>(null)
const pieRef = ref<HTMLDivElement | null>(null)
const donutRef = ref<HTMLDivElement | null>(null)
const radarRef = ref<HTMLDivElement | null>(null)
const gaugeRef = ref<HTMLDivElement | null>(null)

const refsMap: Record<string, typeof lineRef> = {
    line: lineRef,
    bar: barRef,
    pie: pieRef,
    donut: donutRef,
    radar: radarRef,
    gauge: gaugeRef,
}

const chartInstances: Record<string, EChartsType> = {}

const ChartCard = defineComponent({
    name: 'ChartCard',
    props: {
        title: { type: String, required: true },
        description: { type: String, required: true },
    },
    setup(props, { slots }) {
        return () =>
            h('section', { class: 'rounded-lg border border-slate-200 bg-white p-5 shadow-sm' }, [
                h('div', { class: 'mb-3 space-y-1' }, [
                    h('h3', { class: 'text-lg font-semibold text-slate-900' }, props.title),
                    h('p', { class: 'text-sm text-slate-500' }, props.description),
                ]),
                slots.default ? slots.default() : null,
            ])
    },
})

const buildOptions = (): Record<string, ChartOption> => ({
    line: {
        title: { text: 'Visitas por mes', left: 'center' },
        tooltip: { trigger: 'axis' },
        legend: { bottom: 0, data: props.lineSeries.map((s) => s.name) },
        grid: { left: 40, right: 20, bottom: 50, top: 50 },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: props.months,
        },
        yAxis: { type: 'value' },
        series: props.lineSeries.map((serie) => ({
            name: serie.name,
            type: 'line',
            smooth: true,
            data: serie.data,
            areaStyle: { opacity: 0.08 },
        })),
    },
    bar: {
        title: { text: 'Ventas vs objetivo', left: 'center' },
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        legend: { bottom: 0 },
        grid: { left: 40, right: 20, bottom: 50, top: 50 },
        xAxis: { type: 'category', data: props.barCategories, axisTick: { alignWithLabel: true } },
        yAxis: { type: 'value' },
        series: props.barSeries.map((serie) => ({
            name: serie.name,
            type: 'bar',
            stack: 'total',
            emphasis: { focus: 'series' },
            data: serie.data,
        })),
    },
    pie: {
        title: { text: 'Participación de canales', left: 'center' },
        tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
        legend: { orient: 'vertical', left: 'left' },
        series: [
            {
                name: 'Canales',
                type: 'pie',
                radius: '60%',
                data: props.pieData,
                emphasis: {
                    itemStyle: {
                        shadowBlur: 8,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.2)',
                    },
                },
            },
        ],
    },
    donut: {
        title: { text: 'Participación (dona)', left: 'center' },
        tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
        legend: { orient: 'vertical', left: 'left' },
        series: [
            {
                name: 'Canales',
                type: 'pie',
                radius: ['40%', '70%'],
                avoidLabelOverlap: false,
                label: { show: false, position: 'center' },
                emphasis: { label: { show: true, fontSize: 18, fontWeight: 'bold' } },
                labelLine: { show: false },
                data: props.pieData,
            },
        ],
    },
    radar: {
        title: { text: 'KPIs operativos', left: 'center' },
        tooltip: {},
        legend: { bottom: 0 },
        radar: { indicator: props.radarIndicators, radius: '60%' },
        series: [
            {
                type: 'radar',
                data: props.radarData,
                areaStyle: { opacity: 0.15 },
            },
        ],
    },
    gauge: {
        title: { text: 'Disponibilidad', left: 'center' },
        series: [
            {
                name: 'Uptime',
                type: 'gauge',
                min: 80,
                max: 100,
                splitNumber: 4,
                axisLine: {
                    lineStyle: {
                        width: 14,
                        color: [
                            [0.33, '#ef4444'],
                            [0.66, '#f97316'],
                            [1, '#22c55e'],
                        ],
                    },
                },
                pointer: { width: 6 },
                detail: {
                    formatter: '{value}%',
                    fontSize: 18,
                },
                data: [{ value: props.gaugeValue, name: 'Uptime' }],
            },
        ],
    },
})

const initCharts = () => {
    const options = buildOptions()

    Object.entries(refsMap).forEach(([key, elementRef]) => {
        if (!elementRef.value) return
        const instance = echarts.init(elementRef.value)
        instance.setOption(options[key])
        chartInstances[key] = instance
    })

    window.addEventListener('resize', handleResize)
}

const handleResize = () => {
    Object.values(chartInstances).forEach((chart) => chart?.resize())
}

onMounted(initCharts)

onBeforeUnmount(() => {
    window.removeEventListener('resize', handleResize)
    Object.values(chartInstances).forEach((chart) => chart?.dispose())
})
</script>
