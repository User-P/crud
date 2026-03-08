/**
 * Temas ECharts alineados con la paleta DSI (logo).
 * Verde #5bb56a, azul #0b4261, gris #64666a.
 */

import type { EChartsOption } from 'echarts'

const cosmosLight = {
    color: [
        '#0b4261', /* azul DSI (primary) */
        '#5bb56a', /* verde DSI */
        '#0d5a7a', /* azul hover */
        '#4a9d58', /* verde oscuro */
        '#64666a', /* gris DSI */
        '#6bc67a', /* verde claro */
        '#787a7e', /* gris medio */
        '#156b8f', /* azul medio */
    ],
    backgroundColor: 'transparent',
    textStyle: {
        color: '#1e293b',
        fontFamily: 'inherit',
    },
    title: {
        textStyle: { color: '#1e293b', fontWeight: 600 },
        subtextStyle: { color: '#64666a', fontSize: 12 },
    },
    legend: {
        textStyle: { color: '#64666a', fontSize: 12 },
        itemGap: 12,
        itemWidth: 10,
        itemHeight: 10,
    },
    tooltip: {
        backgroundColor: 'rgba(255, 255, 255, 0.96)',
        borderColor: 'rgba(11, 66, 97, 0.2)',
        borderWidth: 1,
        textStyle: { color: '#1e293b', fontSize: 12 },
        padding: [10, 14],
        extraCssText: 'backdrop-filter: blur(12px); border-radius: 10px; box-shadow: 0 12px 40px rgba(11, 66, 97, 0.1);',
    },
    categoryAxis: {
        axisLine: { lineStyle: { color: 'rgba(100, 102, 106, 0.35)' } },
        axisLabel: { color: '#64666a', fontSize: 11 },
        splitLine: { show: false },
    },
    valueAxis: {
        axisLine: { show: false },
        axisLabel: { color: '#64666a', fontSize: 11 },
        splitLine: { lineStyle: { color: 'rgba(100, 102, 106, 0.2)', type: 'dashed' } },
    },
    line: {
        smooth: true,
        symbol: 'circle',
        symbolSize: 4,
        lineStyle: { width: 2 },
    },
    bar: {
        barBorderRadius: [0, 6, 6, 0],
        itemStyle: {
            borderColor: 'rgba(255,255,255,0.2)',
            borderWidth: 0,
        },
    },
    pie: {
        itemStyle: {
            borderColor: 'rgba(255, 255, 255, 0.5)',
            borderWidth: 1,
            shadowBlur: 8,
            shadowColor: 'rgba(11, 66, 97, 0.15)',
        },
        emphasis: {
            itemStyle: {
                shadowBlur: 12,
                shadowColor: 'rgba(11, 66, 97, 0.25)',
            },
        },
    },
} as const

const cosmosDark = {
    color: [
        '#5bb56a', /* verde DSI (primary en dark) */
        '#0d5a7a', /* azul claro */
        '#6bc67a', /* verde claro */
        '#156b8f', /* azul medio */
        '#8e9094', /* gris claro */
        '#3d9db5', /* azul cyan */
        '#787a7e', /* gris */
        '#4a9d58', /* verde oscuro */
    ],
    backgroundColor: 'transparent',
    textStyle: {
        color: '#e2e8f0',
        fontFamily: 'inherit',
    },
    title: {
        textStyle: { color: '#e2e8f0', fontWeight: 600 },
        subtextStyle: { color: '#8e9094', fontSize: 12 },
    },
    legend: {
        textStyle: { color: '#8e9094', fontSize: 12 },
        itemGap: 12,
        itemWidth: 10,
        itemHeight: 10,
    },
    tooltip: {
        backgroundColor: 'rgba(6, 10, 13, 0.95)',
        borderColor: 'rgba(91, 181, 106, 0.25)',
        borderWidth: 1,
        textStyle: { color: '#e2e8f0', fontSize: 12 },
        padding: [10, 14],
        extraCssText: 'backdrop-filter: blur(16px); border-radius: 10px; box-shadow: 0 24px 60px rgba(0,0,0,0.5);',
    },
    categoryAxis: {
        axisLine: { lineStyle: { color: 'rgba(255, 255, 255, 0.08)' } },
        axisLabel: { color: '#8e9094', fontSize: 11 },
        splitLine: { show: false },
    },
    valueAxis: {
        axisLine: { show: false },
        axisLabel: { color: '#8e9094', fontSize: 11 },
        splitLine: { lineStyle: { color: 'rgba(255, 255, 255, 0.06)', type: 'dashed' } },
    },
    line: {
        smooth: true,
        symbol: 'circle',
        symbolSize: 4,
        lineStyle: { width: 2 },
    },
    bar: {
        barBorderRadius: [0, 6, 6, 0],
        itemStyle: {
            borderColor: 'rgba(0,0,0,0.15)',
            borderWidth: 0,
            shadowBlur: 6,
            shadowColor: 'rgba(0, 0, 0, 0.2)',
        },
    },
    pie: {
        itemStyle: {
            borderColor: 'rgba(0, 0, 0, 0.2)',
            borderWidth: 1,
            shadowBlur: 10,
            shadowColor: 'rgba(0, 0, 0, 0.35)',
        },
        emphasis: {
            itemStyle: {
                shadowBlur: 14,
                shadowColor: 'rgba(91, 181, 106, 0.4)',
            },
        },
    },
} as const

export type CosmosChartTheme = {
    color: readonly string[]
    backgroundColor: string
    textStyle: { color: string; fontFamily: string }
    title?: { textStyle?: { color: string; fontWeight?: number }; subtextStyle?: { color: string; fontSize?: number } }
    legend?: { textStyle?: { color: string; fontSize?: number }; itemGap?: number; itemWidth?: number; itemHeight?: number }
    tooltip?: Record<string, unknown>
    categoryAxis?: Record<string, unknown>
    valueAxis?: Record<string, unknown>
    line?: Record<string, unknown>
    bar?: Record<string, unknown>
    pie?: Record<string, unknown>
}

export function getCosmosChartTheme(isDark: boolean): CosmosChartTheme {
    return isDark ? cosmosDark : cosmosLight
}

/** Opciones base para grid/axis que dependen del tema (para merge en cada chart) */
export function getCosmosChartBaseOption(isDark: boolean): Partial<EChartsOption> {
    const axisColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(100, 102, 106, 0.35)'
    const splitLineColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(100, 102, 106, 0.18)'
    return {
        grid: {
            left: 12,
            right: 12,
            top: 40,
            bottom: 12,
            containLabel: true,
            borderColor: 'transparent',
        },
        xAxis: {
            axisLine: { lineStyle: { color: axisColor } },
            axisLabel: { color: isDark ? '#8e9094' : '#64666a', fontSize: 11 },
            splitLine: { show: true, lineStyle: { color: splitLineColor, type: 'dashed' } },
        },
        yAxis: {
            axisLine: { show: false },
            axisLabel: { color: isDark ? '#8e9094' : '#64666a', fontSize: 11 },
            splitLine: { lineStyle: { color: splitLineColor, type: 'dashed' } },
        },
    }
}

export { cosmosLight, cosmosDark }
