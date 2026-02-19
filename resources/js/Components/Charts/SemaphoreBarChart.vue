<template>
    <BaseEChart :option="chartOption" />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import * as echarts from 'echarts/core'
import { BarChart as EBarChart } from 'echarts/charts'
import {
    GridComponent,
    TooltipComponent,
    TitleComponent,
} from 'echarts/components'
import BaseEChart from './BaseEChart.vue'

echarts.use([EBarChart, GridComponent, TooltipComponent, TitleComponent])

interface Props {
    title?: string
    categories: string[]
    values: number[]
    /** Colores por categoría (en orden). Por defecto: amarillo, naranja, rojo */
    colors?: string[]
}

const props = withDefaults(defineProps<Props>(), {
    colors: () => ['#eab308', '#f97316', '#ef4444'],
})

const chartOption = computed(() => {
    const data = props.categories.map((name, i) => ({
        name,
        value: props.values[i] ?? 0,
        itemStyle: {
            color: props.colors[i] ?? props.colors[props.colors.length - 1],
        },
    }))

    return {
        title: props.title ? { text: props.title, left: 'center' } : undefined,
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        grid: { left: 80, right: 80, bottom: 20, top: props.title ? 50 : 20, containLabel: true },
        xAxis: { type: 'value', splitLine: { lineStyle: { type: 'dashed', color: '#e2e8f0' } } },
        yAxis: {
            type: 'category',
            data: props.categories,
            axisLabel: { fontSize: 12, fontWeight: 500 },
        },
        series: [
            {
                type: 'bar',
                data,
                barWidth: '50%',
                label: {
                    show: true,
                    position: 'right',
                    formatter: '{c}',
                    fontSize: 12,
                    fontWeight: 600,
                },
                emphasis: { focus: 'self' },
            },
        ],
    }
})
</script>
