<template>
    <BaseEChart :option="chartOption" />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import * as echarts from 'echarts/core'
import { PieChart as EPieChart, type PieSeriesOption } from 'echarts/charts'
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

echarts.use([EPieChart, LegendComponent, TitleComponent, TooltipComponent])

type Option = ComposeOption<PieSeriesOption | LegendComponentOption | TitleComponentOption | TooltipComponentOption>

type Slice = { name: string; value: number }

const props = withDefaults(defineProps<{
    title?: string
    subtitle?: string
    data: Slice[]
    donut?: boolean
    legendPosition?: 'left' | 'right' | 'bottom'
    extendOption?: Partial<Option>
}>(), {
    donut: false,
    legendPosition: 'left',
    extendOption: () => ({}),
})

const chartOption = computed<Option>(() => ({
    title: props.title ? { text: props.title, subtext: props.subtitle, left: 'center' } : undefined,
    tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
    legend: props.legendPosition === 'bottom'
        ? { bottom: 0 }
        : { orient: 'vertical', left: props.legendPosition },
    series: [
        {
            name: 'Distribución',
            type: 'pie',
            radius: props.donut ? ['40%', '70%'] : '60%',
            data: props.data,
            avoidLabelOverlap: props.donut,
            label: props.donut ? { show: false, position: 'center' } : undefined,
            emphasis: {
                ...(props.donut ? { label: { show: true, fontSize: 18, fontWeight: 'bold' } } : {}),
                itemStyle: { shadowBlur: 12, shadowColor: 'rgba(11, 66, 97, 0.35)' },
            },
            labelLine: props.donut ? { show: false } : undefined,
            itemStyle: {
                borderWidth: 1,
                borderColor: 'rgba(255,255,255,0.4)',
                shadowBlur: 8,
                shadowColor: 'rgba(11, 66, 97, 0.2)',
            },
        },
    ],
    ...props.extendOption,
}))
</script>
