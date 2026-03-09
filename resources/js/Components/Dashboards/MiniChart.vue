<template>
    <!--
        Non-interactive mini ECharts chart for embedding inside cards.
        pointer-events-none ensures mouse events pass through to the parent card.
    -->
    <div class="pointer-events-none h-full w-full">
        <BaseEChart :option="chartOption" />
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseEChart from '@/Components/Charts/BaseEChart.vue'

export interface MiniChartDataItem {
    name: string
    value: number
}

interface Props {
    /** Chart type: donut (pie) or horizontal bar */
    type: 'donut' | 'bar'
    data: MiniChartDataItem[]
    /** Optional colour overrides — falls back to Cosmos palette */
    colors?: string[]
}

const props = defineProps<Props>()

// Default brand colours (light: blue → green → red; dark handled by ECharts theme)
const DEFAULT_COLORS = ['#5bb56a', '#0b4261', '#ef4444', '#f59e0b', '#64666a']

const resolvedColors = computed(() => props.colors ?? DEFAULT_COLORS)

// ── Donut (pie) option ────────────────────────────────────────────────────────
const donutOption = computed(() => ({
    animation: true,
    animationDuration: 900,
    animationEasing: 'cubicOut' as const,
    color: resolvedColors.value,
    tooltip: { show: false },
    series: [
        {
            type: 'pie',
            radius: ['52%', '80%'],
            center: ['50%', '50%'],
            startAngle: 90,
            avoidLabelOverlap: false,
            data: props.data.map((d) => ({ name: d.name, value: d.value })),
            label: { show: false },
            labelLine: { show: false },
            emphasis: { scale: false },
            itemStyle: {
                borderRadius: 5,
                borderWidth: 2,
                borderColor: 'transparent',
            },
        },
    ],
}))

// ── Horizontal bar option ─────────────────────────────────────────────────────
const barOption = computed(() => {
    const max = Math.max(...props.data.map((d) => d.value)) || 1
    return {
        animation: true,
        animationDuration: 900,
        animationEasing: 'cubicOut' as const,
        color: resolvedColors.value,
        tooltip: { show: false },
        grid: { top: 2, bottom: 2, left: 2, right: 2, containLabel: false },
        xAxis: { show: false, type: 'value' as const, max },
        yAxis: {
            show: false,
            type: 'category' as const,
            data: props.data.map((d) => d.name),
        },
        series: [
            {
                type: 'bar',
                data: props.data.map((d, i) => ({
                    value: d.value,
                    itemStyle: { color: resolvedColors.value[i % resolvedColors.value.length] },
                })),
                barMaxWidth: 10,
                itemStyle: { borderRadius: [0, 6, 6, 0] },
            },
        ],
    }
})

const chartOption = computed(() =>
    props.type === 'donut' ? donutOption.value : barOption.value,
)
</script>
