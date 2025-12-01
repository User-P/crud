<template>
    <div ref="chartRef" class="w-full h-full"></div>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import * as echarts from 'echarts/core'
import type { EChartsType } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'

echarts.use([CanvasRenderer])

interface Props {
    option: Record<string, any>
    theme?: string | object
    autoresize?: boolean

}

const props = defineProps<Props>()

const chartRef = ref<HTMLDivElement | null>(null)
let chart: EChartsType | null = null

const resize = () => chart?.resize()

const renderChart = () => {
    if (!chartRef.value) return

    if (!chart) {
        chart = echarts.init(chartRef.value, props.theme)
        if (props.autoresize ?? true) {
            window.addEventListener('resize', resize)
        }
    }

    chart.setOption(props.option, true)
}

watch(
    () => props.option,
    () => renderChart(),
    { deep: true }
)

onMounted(renderChart)

onBeforeUnmount(() => {
    if (props.autoresize ?? true) {
        window.removeEventListener('resize', resize)
    }
    chart?.dispose()
    chart = null
})
</script>
