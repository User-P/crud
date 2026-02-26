<template>
    <button
        type="button"
        class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 text-left shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:ring-offset-2"
        @click="$emit('click')"
    >
        <div class="flex items-start justify-between gap-2">
            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-105"
                :class="v.iconBg"
            >
                <Icon :icon="icon" class="h-5 w-5" :class="v.iconColor" aria-hidden="true" />
            </div>
            <div v-if="trend !== undefined" class="flex shrink-0 items-center gap-0.5">
                <Icon
                    v-if="trend === 'up'"
                    icon="heroicons:arrow-trending-up"
                    class="h-4 w-4 text-emerald-500"
                    aria-hidden="true"
                />
                <Icon
                    v-else-if="trend === 'down'"
                    icon="heroicons:arrow-trending-down"
                    class="h-4 w-4 text-rose-500"
                    aria-hidden="true"
                />
                <Icon
                    v-else-if="trend === 'neutral'"
                    icon="heroicons:minus"
                    class="h-4 w-4 text-slate-400"
                    aria-hidden="true"
                />
                <span
                    v-if="trendPercent != null"
                    class="text-xs font-medium"
                    :class="trend === 'up' ? 'text-emerald-600' : trend === 'down' ? 'text-rose-600' : 'text-slate-500'"
                >
                    {{ trendPercent > 0 ? '+' : '' }}{{ trendPercent }}%
                </span>
            </div>
        </div>

        <p class="mt-3 text-2xl font-bold tabular-nums tracking-tight text-slate-900">
            {{ value }}
        </p>
        <p class="mt-0.5 text-sm font-medium text-slate-500">
            {{ label }}
        </p>

        <!-- Sparkline (tendencia reciente) -->
        <div v-if="sparklineData?.length" class="mt-3 h-8 w-full">
            <Sparkline :data="sparklineData" :color="sparklineColor" />
        </div>

        <!-- Comparación (vs. período anterior) -->
        <p v-if="comparison" class="mt-2 text-xs text-slate-400">
            {{ comparison }}
        </p>

        <div
            class="absolute bottom-0 left-0 right-0 h-0.5 rounded-b-2xl transition-all duration-300 group-hover:h-1"
            :class="v.bar"
        />
    </button>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import Sparkline from './Sparkline.vue'

interface Props {
    label: string
    value: string | number
    /** Iconify icon id (ej. heroicons:chart-bar) */
    icon: string
    variant?: 'blue' | 'green' | 'red'
    /** Mini tendencia: up | down | neutral */
    trend?: 'up' | 'down' | 'neutral'
    /** Porcentaje de cambio (ej. 3.2 para +3.2%) */
    trendPercent?: number | null
    /** Datos para sparkline (ej. últimos 7 puntos) */
    sparklineData?: number[]
    /** Comparación textual: "vs. mes anterior" */
    comparison?: string
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'blue',
    trendPercent: null,
})

defineEmits<{
    (e: 'click'): void
}>()

const variants: Record<'blue' | 'green' | 'red', { iconBg: string; iconColor: string; bar: string }> = {
    blue: {
        iconBg: 'bg-blue-50',
        iconColor: 'text-blue-600',
        bar: 'bg-blue-500',
    },
    green: {
        iconBg: 'bg-emerald-50',
        iconColor: 'text-emerald-600',
        bar: 'bg-emerald-500',
    },
    red: {
        iconBg: 'bg-rose-50',
        iconColor: 'text-rose-600',
        bar: 'bg-rose-500',
    },
}

const v = computed(() => variants[props.variant])

const sparklineColor = computed(() => {
    if (props.trend === 'up' && (props.variant === 'green' || props.variant === 'blue')) return 'green'
    if (props.trend === 'down' && props.variant === 'red') return 'red'
    return props.variant
})
</script>
