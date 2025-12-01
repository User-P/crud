<template>
    <BaseEChart :option="chartOption" />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import * as echarts from 'echarts/core'
import { RadarChart as ERadarChart, type RadarSeriesOption } from 'echarts/charts'
import {
    LegendComponent,
    TitleComponent,
    TooltipComponent,
    type LegendComponentOption,
    type TitleComponentOption,
    type TooltipComponentOption,
} from 'echarts/components'
import type { ComposeOption } from 'echarts/core'
import BaseEChart from './BaseEChart.vue'

echarts.use([ERadarChart, LegendComponent, TitleComponent, TooltipComponent])

type Option = ComposeOption<RadarSeriesOption | LegendComponentOption | TitleComponentOption | TooltipComponentOption>

type Indicator = { name: string; max: number }
type Dataset = { name: string; value: number[] }

const props = withDefaults(defineProps<{
    title?: string
    subtitle?: string
    indicators: Indicator[]
    data: Dataset[]
    extendOption?: Partial<Option>
}>(), {
    extendOption: () => ({}),
})

const chartOption = computed<Option>(() => ({
    title: props.title ? { text: props.title, subtext: props.subtitle, left: 'center' } : undefined,
    tooltip: {},
    legend: { bottom: 0 },
    radar: { indicator: props.indicators, radius: '60%' },
    series: [
        {
            type: 'radar',
            data: props.data,
            areaStyle: { opacity: 0.15 },
        },
    ],
    ...props.extendOption,
}))
</script>
