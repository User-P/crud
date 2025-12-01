<template>
    <BaseEChart :option="chartOption" />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import * as echarts from 'echarts/core'
import { LineChart as ELineChart, type LineSeriesOption } from 'echarts/charts'
import {
    GridComponent,
    LegendComponent,
    TitleComponent,
    TooltipComponent,
    type GridComponentOption,
    type LegendComponentOption,
    type TitleComponentOption,
    type TooltipComponentOption,
} from 'echarts/components'
import type { ComposeOption } from 'echarts/core'
import BaseEChart from './BaseEChart.vue'

echarts.use([ELineChart, GridComponent, LegendComponent, TitleComponent, TooltipComponent])

type Option = ComposeOption<
    LineSeriesOption | GridComponentOption | LegendComponentOption | TitleComponentOption | TooltipComponentOption
>

type Series = {
    name: string
    data: number[]
}

const props = withDefaults(defineProps<{
    title?: string
    subtitle?: string
    labels: string[]
    series: Series[]
    stacked?: boolean
    smooth?: boolean
    area?: boolean
    extendOption?: Partial<Option>
}>(), {
    stacked: false,
    smooth: true,
    area: false,
    extendOption: () => ({}),
})

const chartOption = computed<Option>(() => ({
    title: props.title ? { text: props.title, subtext: props.subtitle, left: 'center' } : undefined,
    tooltip: { trigger: 'axis' },
    legend: { bottom: 0, data: props.series.map((s) => s.name) },
    grid: { left: 40, right: 20, bottom: 50, top: props.title ? 60 : 40 },
    xAxis: { type: 'category', boundaryGap: false, data: props.labels },
    yAxis: { type: 'value' },
    series: props.series.map((serie) => ({
        name: serie.name,
        type: 'line',
        smooth: props.smooth,
        stack: props.stacked ? 'total' : undefined,
        areaStyle: props.area ? { opacity: 0.08 } : undefined,
        emphasis: { focus: 'series' },
        data: serie.data,
    })),
    ...props.extendOption,
}))
</script>
