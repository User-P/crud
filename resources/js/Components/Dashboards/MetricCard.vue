<template>
    <button
        type="button"
        class="metric-card glass-lift group relative flex overflow-hidden text-left transition-all duration-400 focus:outline-none focus:ring-2 focus:ring-[var(--p-focus-ring-color)] focus:ring-offset-2 focus:ring-offset-[var(--th-ring-offset)]"
        :class="[featured ? 'metric-card--featured rounded-3xl' : 'rounded-3xl']"
        @click="$emit('click')"
    >
        <!-- Gradient border glow (featured hover) -->
        <span
            v-if="featured"
            class="pointer-events-none absolute -inset-px rounded-3xl opacity-0 transition-opacity duration-500 group-hover:opacity-100"
            :class="v.gradientRing"
            aria-hidden="true"
        />

        <!-- Glass base -->
        <span
            class="absolute inset-0 rounded-3xl border border-white/20 bg-white/70 shadow-lg backdrop-blur-xl transition-all duration-400 dark:border-white/10 dark:bg-white/5 dark:shadow-none group-hover:bg-white/80 dark:group-hover:bg-white/8"
            :class="featured ? 'group-hover:shadow-2xl' : 'group-hover:shadow-xl'"
            aria-hidden="true"
        />

        <!-- Ambient orbs (featured only) -->
        <span
            v-if="featured"
            class="metric-blob absolute -right-12 -top-12 h-40 w-40 rounded-full opacity-25 blur-3xl transition-all duration-500 group-hover:opacity-40 group-hover:scale-110"
            :class="v.blob"
            aria-hidden="true"
        />
        <span
            v-if="featured"
            class="absolute -bottom-8 -left-8 h-28 w-28 rounded-full opacity-15 blur-2xl transition-opacity duration-500 group-hover:opacity-25"
            :class="v.blob"
            aria-hidden="true"
        />

        <!-- Side accent bar (compact only) -->
        <span
            v-if="!featured"
            class="absolute left-0 top-5 bottom-5 w-1.5 rounded-full transition-all duration-300 group-hover:w-2"
            :class="v.bar"
            aria-hidden="true"
        />

        <!--
            Live indicator dot with scoped-group tooltip.
            group/dot ensures the tooltip only shows when hovering this element,
            not the entire card.
        -->
        <span
            v-if="live"
            class="absolute right-3.5 top-3.5 z-20 flex h-5 w-5 cursor-default items-center justify-center group/dot"
            role="status"
            :aria-label="liveLabel"
        >
            <!-- Glass tooltip (shows on dot hover, not card hover) -->
            <span
                class="pointer-events-none absolute right-full top-1/2 mr-2.5 -translate-y-1/2 whitespace-nowrap rounded-xl border border-[var(--th-border)] px-2.5 py-1.5 text-xs font-medium text-[color:var(--th-text-primary)] opacity-0 shadow-xl backdrop-blur-xl transition-opacity duration-200 group-hover/dot:opacity-100 dark:border-white/10"
                style="background: rgba(255,255,255,0.96)"
                aria-hidden="true"
            >
                <!-- Caret arrow pointing right -->
                <span
                    class="absolute left-full top-1/2 -translate-y-1/2 border-4 border-transparent"
                    style="border-left-color: rgba(255,255,255,0.96)"
                    aria-hidden="true"
                />
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true" />
                    {{ liveLabel }}
                </span>
            </span>

            <!-- Pulsing dot -->
            <span class="absolute inline-flex h-2.5 w-2.5 rounded-full">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60" aria-hidden="true" />
                <span class="relative h-2.5 w-2.5 rounded-full bg-emerald-500 dark:bg-emerald-400" aria-hidden="true" />
            </span>
        </span>

        <!-- Content -->
        <div class="relative z-10 flex flex-1 flex-col" :class="featured ? 'p-7' : 'p-5'">

            <!-- Header: icon + trend pill -->
            <div class="flex items-start justify-between gap-2">
                <div
                    class="flex shrink-0 items-center justify-center transition-transform duration-300 group-hover:scale-105"
                    :class="[
                        featured ? 'h-14 w-14 rounded-2xl' : 'h-11 w-11 rounded-xl',
                        v.iconBg,
                    ]"
                >
                    <Icon
                        :icon="icon"
                        :class="[featured ? 'h-7 w-7' : 'h-5 w-5', v.iconColor]"
                        aria-hidden="true"
                    />
                </div>

                <!-- Trend indicator -->
                <div v-if="trend !== undefined">
                    <!-- Featured: pill badge -->
                    <span
                        v-if="featured"
                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 transition-colors duration-300"
                        :class="trendPillClass"
                    >
                        <Icon v-if="trend === 'up'" icon="heroicons:arrow-trending-up" class="h-3.5 w-3.5" aria-hidden="true" />
                        <Icon v-else-if="trend === 'down'" icon="heroicons:arrow-trending-down" class="h-3.5 w-3.5" aria-hidden="true" />
                        <Icon v-else icon="heroicons:minus" class="h-3.5 w-3.5" aria-hidden="true" />
                        <span v-if="trendPercent != null">{{ trendPercent > 0 ? '+' : '' }}{{ trendPercent }}%</span>
                    </span>
                    <!-- Compact: inline icon + text -->
                    <div v-else class="flex shrink-0 items-center gap-0.5">
                        <Icon v-if="trend === 'up'" icon="heroicons:arrow-trending-up" class="h-4 w-4 text-emerald-500 dark:text-emerald-400" aria-hidden="true" />
                        <Icon v-else-if="trend === 'down'" icon="heroicons:arrow-trending-down" class="h-4 w-4 text-rose-500 dark:text-rose-400" aria-hidden="true" />
                        <Icon v-else icon="heroicons:minus" class="h-4 w-4 text-[color:var(--th-text-muted)]" aria-hidden="true" />
                        <span
                            v-if="trendPercent != null"
                            class="text-xs font-medium"
                            :class="trend === 'up' ? 'text-emerald-600 dark:text-emerald-400' : trend === 'down' ? 'text-rose-600 dark:text-rose-400' : 'text-[color:var(--th-text-muted)]'"
                        >{{ trendPercent > 0 ? '+' : '' }}{{ trendPercent }}%</span>
                    </div>
                </div>
            </div>

            <!-- Value: oculto en featured con miniChart (cada bloque lo muestra) -->
            <p
                v-if="!featured || !miniChart"
                class="tabular-nums font-bold tracking-tight text-[color:var(--th-text-primary)]"
                :class="featured ? 'mt-6 text-5xl leading-none' : 'mt-4 text-2xl'"
            >
                {{ displayedValue }}
            </p>

            <!-- Label: oculto en featured con miniChart -->
            <p
                v-if="!featured || !miniChart"
                class="font-medium text-[color:var(--th-text-secondary)]"
                :class="featured ? 'mt-2 text-base' : 'mt-1 text-sm'"
            >
                {{ label }}
            </p>

            <!--
                Data visualisation (featured only):
                Priority: miniChart (donut) con layout en 2 columnas + barras % + sparkline opcional
            -->
            <template v-if="featured && miniChart?.type === 'donut'">
                <div class="mt-5 grid grid-cols-1 gap-5 min-[380px]:grid-cols-[1fr_auto]">
                    <!-- Columna izquierda: valor, label, comparación, barras de % y tendencia -->
                    <div class="flex min-w-0 flex-col justify-center">
                        <p class="tabular-nums font-bold leading-none tracking-tight text-[color:var(--th-text-primary)] text-5xl">
                            {{ displayedValue }}
                        </p>
                        <p class="mt-2 font-medium text-[color:var(--th-text-secondary)] text-base">
                            {{ label }}
                        </p>
                        <p v-if="comparison" class="mt-1 text-xs text-[color:var(--th-text-muted)]">{{ comparison }}</p>
                        <!-- Barras de % (Activos / Inactivos) -->
                        <div v-if="donutPercentages.length" class="mt-4 space-y-2">
                            <div
                                v-for="(p, i) in donutPercentages"
                                :key="p.name"
                                class="flex items-center gap-2"
                            >
                                <span
                                    class="h-2 w-2 shrink-0 rounded-full"
                                    :style="{ background: (miniChart.colors ?? DONUT_DEFAULTS)[i % (miniChart.colors ?? DONUT_DEFAULTS).length] }"
                                    aria-hidden="true"
                                />
                                <span class="min-w-18 text-xs font-medium text-[color:var(--th-text-secondary)]">{{ p.name }}</span>
                                <div class="h-2 flex-1 min-w-0 overflow-hidden rounded-full bg-[var(--th-input-bg)]">
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :style="{ width: `${p.percent}%`, background: (miniChart.colors ?? DONUT_DEFAULTS)[i % (miniChart.colors ?? DONUT_DEFAULTS).length] }"
                                    />
                                </div>
                                <span class="w-11 shrink-0 text-right text-xs font-bold tabular-nums text-[color:var(--th-text-primary)]">{{ p.percent }}%</span>
                            </div>
                        </div>
                        <!-- Tendencia 7 días (sparkline) si se pasa -->
                        <div v-if="sparklineData?.length" class="mt-4">
                            <p class="mb-1.5 text-xs font-medium text-[color:var(--th-text-muted)]">Tendencia (7 días)</p>
                            <div class="h-10 w-full">
                                <Sparkline :data="sparklineData" :color="sparklineColor" :filled="true" />
                            </div>
                        </div>
                    </div>
                    <!-- Columna derecha: donut más grande + leyenda -->
                    <div class="flex flex-col items-center justify-center gap-3">
                        <div class="h-32 w-32 shrink-0 sm:h-36 sm:w-36">
                            <MiniChart :type="miniChart.type" :data="miniChart.data" :colors="miniChart.colors" />
                        </div>
                        <ul class="flex flex-wrap justify-center gap-x-4 gap-y-1 text-xs" aria-label="Leyenda">
                            <li v-for="(item, i) in miniChart.data" :key="item.name" class="flex items-center gap-1.5">
                                <span
                                    class="h-2 w-2 shrink-0 rounded-full"
                                    :style="{ background: (miniChart.colors ?? DONUT_DEFAULTS)[i % (miniChart.colors ?? DONUT_DEFAULTS).length] }"
                                    aria-hidden="true"
                                />
                                <span class="font-medium text-[color:var(--th-text-secondary)]">{{ item.name }}</span>
                                <span class="tabular-nums font-bold text-[color:var(--th-text-primary)]">{{ item.value.toLocaleString('es') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </template>

            <!-- Featured con miniChart tipo bar u otro -->
            <template v-else-if="featured && miniChart">
                <p class="mt-6 text-5xl font-bold tabular-nums leading-none tracking-tight text-[color:var(--th-text-primary)]">{{ displayedValue }}</p>
                <p class="mt-2 font-medium text-[color:var(--th-text-secondary)] text-base">{{ label }}</p>
                <p v-if="comparison" class="mt-1 text-xs text-[color:var(--th-text-muted)]">{{ comparison }}</p>
                <div class="mt-5 flex flex-col gap-4">
                    <div class="h-20 w-full shrink-0">
                        <MiniChart :type="miniChart.type" :data="miniChart.data" :colors="miniChart.colors" />
                    </div>
                </div>
            </template>

            <!-- Featured sin miniChart: solo sparkline si hay -->
            <div v-else-if="featured && sparklineData?.length" class="mt-6 h-14 w-full">
                <Sparkline :data="sparklineData" :color="sparklineColor" :filled="true" />
            </div>

            <!-- Compact sparkline (non-featured) -->
            <div v-else-if="sparklineData?.length" class="mt-4 h-9 w-full">
                <Sparkline :data="sparklineData" :color="sparklineColor" />
            </div>

            <!-- Comparison text (oculto cuando featured+donut o featured+miniChart, ya va dentro del bloque) -->
            <p v-if="comparison && (!featured || !miniChart)" class="mt-2 text-xs text-[color:var(--th-text-muted)]">{{ comparison }}</p>
        </div>
    </button>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import Sparkline from './Sparkline.vue'
import MiniChart from './MiniChart.vue'
import type { MiniChartDataItem } from './MiniChart.vue'
import { useCountUp } from '@/composables/useCountUp'

const DONUT_DEFAULTS = ['#5bb56a', '#0b4261', '#ef4444', '#f59e0b', '#64666a']

interface MiniChartProp {
    type: 'donut' | 'bar'
    data: MiniChartDataItem[]
    colors?: string[]
}

interface Props {
    label: string
    value: string | number
    icon: string
    variant?: 'blue' | 'green' | 'red' | 'violet'
    trend?: 'up' | 'down' | 'neutral'
    trendPercent?: number | null
    sparklineData?: number[]
    comparison?: string
    /** Hero-size card with ambient glows, pill badge, count-up and embedded chart */
    featured?: boolean
    /** Show a pulsing live-data dot */
    live?: boolean
    /** Text shown inside the live dot tooltip, e.g. "Hace 5 min" */
    lastSync?: string
    /** Replaces the sparkline in featured mode with a compact ECharts chart */
    miniChart?: MiniChartProp
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'blue',
    trendPercent: null,
    featured: false,
    live: false,
})

defineEmits<{ (e: 'click'): void }>()

// ── Variant styles ───────────────────────────────────────────────────────────
const variants: Record<
    'blue' | 'green' | 'red' | 'violet',
    { iconBg: string; iconColor: string; bar: string; blob: string; gradientRing: string }
> = {
    blue: {
        iconBg: 'bg-[#0b4261]/15 dark:bg-[#0d5a7a]/25',
        iconColor: 'text-[#0b4261] dark:text-[#5bb56a]',
        bar: 'bg-[#0b4261] dark:bg-[#0d5a7a]',
        blob: 'bg-[#0b4261]',
        gradientRing: 'bg-gradient-to-br from-[#0b4261]/40 via-[#5bb56a]/20 to-transparent',
    },
    green: {
        iconBg: 'bg-[#5bb56a]/15 dark:bg-[#5bb56a]/25',
        iconColor: 'text-[#4a9d58] dark:text-[#6bc67a]',
        bar: 'bg-[#5bb56a]',
        blob: 'bg-emerald-400',
        gradientRing: 'bg-gradient-to-br from-emerald-400/40 via-emerald-300/20 to-transparent',
    },
    red: {
        iconBg: 'bg-rose-500/15 dark:bg-rose-400/25',
        iconColor: 'text-rose-600 dark:text-rose-400',
        bar: 'bg-rose-500 dark:bg-rose-400',
        blob: 'bg-rose-400',
        gradientRing: 'bg-gradient-to-br from-rose-400/40 via-rose-300/20 to-transparent',
    },
    violet: {
        iconBg: 'bg-violet-500/15 dark:bg-violet-400/25',
        iconColor: 'text-violet-600 dark:text-violet-400',
        bar: 'bg-violet-500 dark:bg-violet-400',
        blob: 'bg-violet-400',
        gradientRing: 'bg-gradient-to-br from-violet-400/40 via-violet-300/20 to-transparent',
    },
}

const v = computed(() => variants[props.variant])

const liveLabel = computed(() =>
    props.lastSync ? `Actualizado: ${props.lastSync}` : 'Datos en tiempo real',
)

const trendPillClass = computed(() => {
    if (props.trend === 'up')
        return 'bg-emerald-500/10 text-emerald-700 ring-emerald-500/20 dark:bg-emerald-400/15 dark:text-emerald-400 dark:ring-emerald-400/20'
    if (props.trend === 'down')
        return 'bg-rose-500/10 text-rose-700 ring-rose-500/20 dark:bg-rose-400/15 dark:text-rose-400 dark:ring-rose-400/20'
    return 'bg-white/40 text-[color:var(--th-text-muted)] ring-[var(--th-border)] dark:bg-white/5'
})

const sparklineColor = computed(() => {
    if (props.trend === 'up' && props.variant !== 'red') return 'green'
    if (props.trend === 'down' && props.variant === 'red') return 'red'
    return props.variant === 'violet' ? 'violet' : props.variant
})

const numericValue = computed<number | null>(() => {
    if (typeof props.value === 'number') return props.value
    const n = Number(String(props.value).replace(/[,.\s]/g, ''))
    return isNaN(n) ? null : n
})

const { displayed: countedRaw } = useCountUp(
    () => (props.featured && numericValue.value !== null ? numericValue.value : 0),
)

const displayedValue = computed(() => {
    if (!props.featured || numericValue.value === null) return props.value
    return countedRaw.value.toLocaleString('es')
})

/** Porcentajes del donut para las barras de desglose (solo featured + donut) */
const donutPercentages = computed(() => {
    if (!props.featured || !props.miniChart?.data?.length) return []
    const total = props.miniChart.data.reduce((s, d) => s + d.value, 0)
    if (total === 0) return []
    return props.miniChart.data.map((d) => ({
        name: d.name,
        percent: Math.round((d.value / total) * 100),
    }))
})
</script>
