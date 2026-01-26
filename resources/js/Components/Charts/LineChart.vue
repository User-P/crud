<template>
    <BaseEChart :option="chartOption" />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import * as echarts from 'echarts/core';
import { LineChart as ELineChart, type LineSeriesOption } from 'echarts/charts';
import {
    GridComponent,
    LegendComponent,
    TitleComponent,
    TooltipComponent,
    type GridComponentOption,
    type LegendComponentOption,
    type TitleComponentOption,
    type TooltipComponentOption,
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';
import type { ComposeOption } from 'echarts/core';
import BaseEChart from './BaseEChart.vue';

echarts.use([
    ELineChart,
    GridComponent,
    LegendComponent,
    TitleComponent,
    TooltipComponent,
    CanvasRenderer,
]);

type Option = ComposeOption<
    | LineSeriesOption
    | GridComponentOption
    | LegendComponentOption
    | TitleComponentOption
    | TooltipComponentOption
>;

type Series = {
    name: string;
    data: number[];
};

type LegendPosition = 'top' | 'bottom';
type LegendType = 'scroll' | 'plain';

interface Props {
    title?: string;
    subtitle?: string;
    labels: string[];
    series: Series[];
    stacked?: boolean;
    smooth?: boolean;
    area?: boolean;
    areaOpacity?: number;
    showSymbols?: boolean;
    symbolSize?: number;
    lineWidth?: number;
    showValueLabels?: boolean;
    hideZeroValues?: boolean;
    valueDecimals?: number;
    valuePrefix?: string;
    valueSuffix?: string;
    valueFormatter?: (value: number, params: any) => string;
    showLegend?: boolean;
    legendType?: LegendType;
    legendPosition?: LegendPosition;
    xAxisLabelRotate?: number;
    showYAxisLabels?: boolean;
    yAxisMin?: number | 'dataMin';
    yAxisMax?: number | 'dataMax';
    connectNulls?: boolean;
    step?:  'start' | 'middle' | 'end';
    extendOption?: Partial<Option>;
}

const props = withDefaults(defineProps<Props>(), {
    stacked: false,
    smooth: false,
    area: false,
    areaOpacity: 0.08,
    showSymbols: true,
    showValueLabels: true,
    hideZeroValues: false,
    showLegend: true,
    legendType: 'scroll',
    legendPosition: 'top',
    xAxisLabelRotate: 0,
    showYAxisLabels: false,
    extendOption: () => ({}),
});

const formatValue = (raw: unknown, params?: any) => {
    const numeric = Number(raw);
    if (!Number.isFinite(numeric)) {
        return raw === null || raw === undefined ? '' : String(raw);
    }

    if (props.valueFormatter) {
        return props.valueFormatter(numeric, params);
    }

    const decimals = typeof props.valueDecimals === 'number' ? props.valueDecimals : undefined;
    const formatted =
        decimals === undefined ? String(numeric) : numeric.toFixed(Math.max(0, decimals));

    return `${props.valuePrefix ?? ''}${formatted}${props.valueSuffix ?? ''}`;
};

const chartOption = computed<Option>(() => {
    const showLegend = props.showLegend && props.series.length > 0;
    const legendAtTop = showLegend && props.legendPosition === 'top';
    const legendAtBottom = showLegend && props.legendPosition === 'bottom';
    const gridTop = (props.title ? 70 : 30) + (legendAtTop ? 20 : 0);
    const gridBottom = 20 + (legendAtBottom ? 40 : 0);

    return {
        title: props.title
            ? {
                text: props.title,
                subtext: props.subtitle,
                left: 'center',
                textStyle: { fontSize: 16, fontWeight: 'bold' },
            }
            : undefined,

        tooltip: {
            trigger: 'axis',
            backgroundColor: 'rgba(255, 255, 255, 0.9)',
            borderWidth: 1,
            valueFormatter: (value: any) => formatValue(value),
        },

        legend: showLegend
            ? {
                type: props.legendType,
                top: legendAtTop ? 0 : undefined,
                bottom: legendAtBottom ? 0 : undefined,
                orient: 'horizontal',
            }
            : undefined,

        grid: {
            left: 10,
            right: 10,
            bottom: gridBottom,
            top: gridTop,
            containLabel: true,
        },

        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: props.labels,
            axisLabel: {
                rotate: props.xAxisLabelRotate,
                hideOverlap: true,
            },
        },

        yAxis: {
            type: 'value',
            min: props.yAxisMin,
            max: props.yAxisMax,
            axisLabel: { show: props.showYAxisLabels },
        },

        series: props.series.map((serie) => {
            const labelFormatter = (params: any) => {
                const rawValue = Array.isArray(params.value) ? params.value[1] : params.value;
                if (props.hideZeroValues && Number(rawValue) === 0) return '';
                return formatValue(rawValue, params);
            };

            const base: LineSeriesOption = {
                name: serie.name,
                type: 'line',
                smooth: props.smooth,
                data: serie.data,
                connectNulls: props.connectNulls,
                step: props.step,
                showSymbol: props.showSymbols,
                symbol: 'circle',
                emphasis: { focus: 'series' },
                label: props.showValueLabels
                    ? {
                        show: true,
                        position: 'top',
                        distance: 6,
                        fontSize: 11,
                        fontWeight: 600,
                        color: '#111827',
                        formatter: labelFormatter,
                    }
                    : { show: false },
                labelLayout: { hideOverlap: true },
                areaStyle: props.area ? { opacity: props.areaOpacity } : undefined,
            };

            if (props.stacked) {
                base.stack = 'total';
            }

            if (typeof props.symbolSize === 'number') {
                base.symbolSize = props.symbolSize;
            }

            if (typeof props.lineWidth === 'number') {
                base.lineStyle = { width: props.lineWidth };
            }

            return base;
        }),

        ...props.extendOption,
    };
});
</script>
