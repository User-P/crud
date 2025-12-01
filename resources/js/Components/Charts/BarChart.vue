<template>
    <BaseEChart :option="chartOption" />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import * as echarts from 'echarts/core'
import { BarChart as EBarChart, type BarSeriesOption } from 'echarts/charts'
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

echarts.use([EBarChart, GridComponent, LegendComponent, TitleComponent, TooltipComponent])

type Option = ComposeOption<
    BarSeriesOption | GridComponentOption | LegendComponentOption | TitleComponentOption | TooltipComponentOption
>

type Series = {
    name: string
    data: number[]
}

const props = withDefaults(defineProps<{
    title?: string
    subtitle?: string
    categories: string[]
    series: Series[]
    stacked?: boolean
    extendOption?: Partial<Option>
}>(), {
    stacked: false,
    extendOption: () => ({}),
})

const chartOption = computed<Option>(() => ({
    title: props.title ? { text: props.title, subtext: props.subtitle, left: 'center' } : undefined,
    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
    legend: { bottom: 0 },
    grid: { left: 40, right: 20, bottom: 50, top: props.title ? 60 : 40 },
    xAxis: { type: 'category', data: props.categories, axisTick: { alignWithLabel: true } },
    yAxis: { type: 'value' },
    series: props.series.map((serie) => ({
        name: serie.name,
        type: 'bar',
        stack: props.stacked ? 'total' : undefined,
        emphasis: { focus: 'series' },
        data: serie.data,
    })),
    ...props.extendOption,
}))
</script>
