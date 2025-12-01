<template>
    <BaseEChart :option="chartOption" />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import * as echarts from 'echarts/core'
import { GaugeChart as EGaugeChart, type GaugeSeriesOption } from 'echarts/charts'
import { TitleComponent, type TitleComponentOption } from 'echarts/components'
import type { ComposeOption } from 'echarts/core'
import BaseEChart from './BaseEChart.vue'

echarts.use([EGaugeChart, TitleComponent])

type Option = ComposeOption<GaugeSeriesOption | TitleComponentOption>

const props = withDefaults(defineProps<{
    title?: string
    value: number
    min?: number
    max?: number
    suffix?: string
    extendOption?: Partial<Option>
}>(), {
    min: 0,
    max: 100,
    suffix: '%',
    extendOption: () => ({}),
})

const chartOption = computed<Option>(() => ({
    title: props.title ? { text: props.title, left: 'center' } : undefined,
    series: [
        {
            type: 'gauge',
            min: props.min,
            max: props.max,
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
                formatter: `{value}${props.suffix}`,
                fontSize: 18,
            },
            data: [{ value: props.value }],
        },
    ],
    ...props.extendOption,
}))
</script>
