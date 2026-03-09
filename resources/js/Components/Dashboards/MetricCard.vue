<template>
    <button
        type="button"
        class="metric-card cosmos-lift group relative flex overflow-hidden text-left transition-all duration-400 focus:outline-none focus:ring-2 focus:ring-(--p-focus-ring-color) focus:ring-offset-2 focus:ring-offset-(--th-ring-offset)"
        :class="[featured ? 'metric-card--featured rounded-3xl' : 'rounded-3xl']"
        @click="$emit('click')"
    >
        <!-- Gradient border glow (featured only, animates on hover) -->
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

        <!-- Corner ambient glow orb (featured) -->
        <span
            v-if="featured"
            class="absolute -right-12 -top-12 h-40 w-40 rounded-full opacity-25 blur-3xl transition-all duration-500 group-hover:opacity-40 group-hover:scale-110"
            :class="v.blob"
            aria-hidden="true"
        />
        <!-- Secondary corner orb bottom-left (featured) -->
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

        <!-- Content -->
        <div class="relative z-10 flex flex-1 flex-col" :class="featured ? 'p-7' : 'p-5'">

            <!-- Header row: icon + trend badge -->
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

                <!-- Trend pill (featured = pill badge, compact = plain) -->
                <div v-if="trend !== undefined">
                    <span
                        v-if="featured"
                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 transition-colors duration-300"
                        :class="trendPillClass"
                    >
                        <Icon
                            v-if="trend === 'up'" icon="heroicons:arrow-trending-up"
                            class="h-3.5 w-3.5" aria-hidden="true"
                        />
                        <Icon
                            v-else-if="trend === 'down'" icon="heroicons:arrow-trending-down"
                            class="h-3.5 w-3.5" aria-hidden="true"
                        />
                        <Icon v-else icon="heroicons:minus" class="h-3.5 w-3.5" aria-hidden="true" />
                        <span v-if="trendPercent != null">
                            {{ trendPercent > 0 ? '+' : '' }}{{ trendPercent }}%
                        </span>
                    </span>
                    <!-- compact inline trend -->
                    <div v-else class="flex shrink-0 items-center gap-0.5">
                        <Icon
                            v-if="trend === 'up'" icon="heroicons:arrow-trending-up"
                            class="h-4 w-4 text-emerald-500 dark:text-emerald-400" aria-hidden="true"
                        />
                        <Icon
                            v-else-if="trend === 'down'" icon="heroicons:arrow-trending-down"
                            class="h-4 w-4 text-rose-500 dark:text-rose-400" aria-hidden="true"
                        />
                        <Icon
                            v-else icon="heroicons:minus"
                            class="h-4 w-4 text-(--th-text-muted)" aria-hidden="true"
                        />
                        <span
                            v-if="trendPercent != null"
                            class="text-xs font-medium"
                            :class="trend === 'up' ? 'text-emerald-600 dark:text-emerald-400' : trend === 'down' ? 'text-rose-600 dark:text-rose-400' : 'text-(--th-text-muted)'"
                        >
                            {{ trendPercent > 0 ? '+' : '' }}{{ trendPercent }}%
                        </span>
                    </div>
                </div>
            </div>

            <!-- Value -->
            <p
                class="tabular-nums font-bold tracking-tight text-(--th-text-primary)"
                :class="featured ? 'mt-6 text-5xl leading-none' : 'mt-4 text-2xl'"
            >
                {{ value }}
            </p>

            <!-- Label -->
            <p
                class="font-medium text-(--th-text-secondary)"
                :class="featured ? 'mt-2 text-base' : 'mt-1 text-sm'"
            >
                {{ label }}
            </p>

            <!-- Sparkline -->
            <div
                v-if="sparklineData?.length"
                class="w-full"
                :class="featured ? 'mt-6 h-14' : 'mt-4 h-9'"
            >
                <Sparkline :data="sparklineData" :color="sparklineColor" />
            </div>

            <!-- Comparison text -->
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
    featured?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'blue',
    trendPercent: null,
    featured: false,
})

defineEmits<{
    (e: 'click'): void
}>()

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
        bar: 'bg-[#5bb56a] dark:bg-[#5bb56a]',
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

const trendPillClass = computed(() => {
    if (props.trend === 'up')
        return 'bg-emerald-500/10 text-emerald-700 ring-emerald-500/20 dark:bg-emerald-400/15 dark:text-emerald-400 dark:ring-emerald-400/20'
    if (props.trend === 'down')
        return 'bg-rose-500/10 text-rose-700 ring-rose-500/20 dark:bg-rose-400/15 dark:text-rose-400 dark:ring-rose-400/20'
    return 'bg-white/40 text-(--th-text-muted) ring-(--th-border) dark:bg-white/5'
})

const sparklineColor = computed(() => {
    if (props.trend === 'up' && (props.variant === 'green' || props.variant === 'blue' || props.variant === 'violet'))
        return props.variant === 'violet' ? 'violet' : 'green'
    if (props.trend === 'down' && props.variant === 'red') return 'red'
    return props.variant === 'violet' ? 'violet' : props.variant
})
</script>
