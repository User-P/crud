<template>
    <button
        type="button"
        class="metric-card group relative flex overflow-hidden rounded-3xl text-left transition-all duration-400 focus:outline-none focus:ring-2 focus:ring-(--p-focus-ring-color) focus:ring-offset-2 focus:ring-offset-(--th-ring-offset)"
        @click="$emit('click')"
    >
        <!-- Glass -->
        <span
            class="absolute inset-0 rounded-3xl border border-white/20 bg-white/70 shadow-lg backdrop-blur-xl transition-all duration-400 dark:border-white/10 dark:bg-white/5 dark:shadow-none group-hover:bg-white/80 dark:group-hover:bg-white/8 group-hover:shadow-xl"
            aria-hidden="true"
        />
        <!-- Barra lateral de variante -->
        <span
            class="absolute left-0 top-5 bottom-5 w-1.5 rounded-full transition-all duration-300 group-hover:w-2"
            :class="v.bar"
            aria-hidden="true"
        />

        <div class="relative z-10 flex flex-1 flex-col p-5">
            <div class="flex items-start justify-between gap-2">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-105"
                    :class="v.iconBg"
                >
                    <Icon :icon="icon" class="h-5 w-5" :class="v.iconColor" aria-hidden="true" />
                </div>
                <div v-if="trend !== undefined" class="flex shrink-0 items-center gap-0.5">
                    <Icon v-if="trend === 'up'" icon="heroicons:arrow-trending-up" class="h-4 w-4 text-emerald-500 dark:text-emerald-400" aria-hidden="true" />
                    <Icon v-else-if="trend === 'down'" icon="heroicons:arrow-trending-down" class="h-4 w-4 text-rose-500 dark:text-rose-400" aria-hidden="true" />
                    <Icon v-else-if="trend === 'neutral'" icon="heroicons:minus" class="h-4 w-4 text-(--th-text-muted)" aria-hidden="true" />
                    <span v-if="trendPercent != null" class="text-xs font-medium"
                        :class="trend === 'up' ? 'text-emerald-600 dark:text-emerald-400' : trend === 'down' ? 'text-rose-600 dark:text-rose-400' : 'text-(--th-text-muted)'">
                        {{ trendPercent > 0 ? '+' : '' }}{{ trendPercent }}%
                    </span>
                </div>
            </div>

            <p class="mt-4 text-2xl font-bold tabular-nums tracking-tight text-(--th-text-primary)">
                {{ value }}
            </p>
            <p class="mt-1 text-sm font-medium text-(--th-text-secondary)">
                {{ label }}
            </p>

            <div v-if="sparklineData?.length" class="mt-4 h-9 w-full">
                <Sparkline :data="sparklineData" :color="sparklineColor" />
            </div>

            <p v-if="comparison" class="mt-2 text-xs text-(--th-text-muted)">
                {{ comparison }}
            </p>
        </div>
    </button>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import Sparkline from './Sparkline.vue'

interface Props {
    label: string
    value: string | number
    icon: string
    variant?: 'blue' | 'green' | 'red' | 'violet'
    trend?: 'up' | 'down' | 'neutral'
    trendPercent?: number | null
    sparklineData?: number[]
    comparison?: string
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'blue',
    trendPercent: null,
})

defineEmits<{
    (e: 'click'): void
}>()

const variants: Record<'blue' | 'green' | 'red' | 'violet', { iconBg: string; iconColor: string; bar: string }> = {
    blue: {
        iconBg: 'bg-[#0b4261]/15 dark:bg-[#0d5a7a]/25',
        iconColor: 'text-[#0b4261] dark:text-[#5bb56a]',
        bar: 'bg-[#0b4261] dark:bg-[#0d5a7a]',
    },
    green: {
        iconBg: 'bg-[#5bb56a]/15 dark:bg-[#5bb56a]/25',
        iconColor: 'text-[#4a9d58] dark:text-[#6bc67a]',
        bar: 'bg-[#5bb56a] dark:bg-[#5bb56a]',
    },
    red: {
        iconBg: 'bg-rose-500/15 dark:bg-rose-400/25',
        iconColor: 'text-rose-600 dark:text-rose-400',
        bar: 'bg-rose-500 dark:bg-rose-400',
    },
    violet: {
        iconBg: 'bg-[#0b4261]/15 dark:bg-[#5bb56a]/25',
        iconColor: 'text-[#0b4261] dark:text-[#5bb56a]',
        bar: 'bg-[#0b4261] dark:bg-[#5bb56a]',
    },
}

const v = computed(() => variants[props.variant])

const sparklineColor = computed(() => {
    if (props.trend === 'up' && (props.variant === 'green' || props.variant === 'blue' || props.variant === 'violet')) return props.variant === 'violet' ? 'violet' : 'green'
    if (props.trend === 'down' && props.variant === 'red') return 'red'
    return props.variant === 'violet' ? 'violet' : props.variant
})
</script>
