<template>
    <button type="button"
        class="metric-card glass-lift group relative flex overflow-hidden text-left transition-all duration-400 focus:outline-none focus:ring-2 focus:ring-[var(--p-focus-ring-color)] focus:ring-offset-2 focus:ring-offset-[var(--th-ring-offset)]"
        :class="[featured ? 'metric-card--featured rounded-3xl' : 'rounded-3xl']" @click="$emit('click')">
        <span v-if="featured"
            class="pointer-events-none absolute -inset-px rounded-3xl opacity-0 transition-opacity duration-500 group-hover:opacity-100"
            :style="gradientRingStyle" aria-hidden="true" />

        <span class="glass-panel absolute inset-0 rounded-3xl transition-all duration-400"
            :class="featured ? 'group-hover:shadow-2xl' : 'group-hover:shadow-xl'" aria-hidden="true" />

        <span v-if="featured"
            class="metric-blob absolute -right-12 -top-12 h-40 w-40 rounded-full opacity-25 blur-3xl transition-all duration-500 group-hover:opacity-40 group-hover:scale-110"
            :style="blobStyle" aria-hidden="true" />

        <span v-else
            class="absolute left-0 top-5 bottom-5 w-1.5 rounded-full transition-all duration-300 group-hover:w-2"
            :style="barStyle" aria-hidden="true" />

        <span v-if="live"
            class="absolute right-3.5 top-3.5 z-20 flex h-5 w-5 cursor-default items-center justify-center group/dot"
            role="status">
            <span class="absolute inline-flex h-2.5 w-2.5 rounded-full">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-60" :style="pingStyle"
                    aria-hidden="true" />
                <span class="relative h-2.5 w-2.5 rounded-full" :style="dotStyle" aria-hidden="true" />
            </span>
        </span>

        <!-- Tooltip overlay (opcional): vive DENTRO de la card (no se recorta) -->
        <span
            class="pointer-events-none absolute inset-0 z-30 flex items-end opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-focus-visible:opacity-100"
            aria-hidden="true">
            <span class="absolute inset-0 rounded-3xl" :style="tooltipBackdropStyle" />
            <span
                class="relative z-10 w-full translate-y-1 px-5 pb-5 pt-8 text-sm font-medium leading-snug text-white transition-transform duration-300 group-hover:translate-y-0 group-focus-visible:translate-y-0"
                :class="featured ? 'px-7 pb-7 pt-10 text-base' : 'px-5 pb-5 pt-8 text-sm'" :style="tooltipTextStyle">
                prueba tooltip
            </span>
        </span>

        <div class="relative z-10 flex flex-1 flex-col" :class="featured ? 'p-7' : 'p-5'">
            <div class="flex items-start justify-between gap-2">
                <div class="flex shrink-0 items-center justify-center border transition-transform duration-300 group-hover:scale-105"
                    :class="[featured ? 'h-14 w-14 rounded-2xl' : 'h-11 w-11 rounded-xl']" :style="badgeStyle">
                    <Icon :icon="icon" :class="[featured ? 'h-7 w-7' : 'h-5 w-5']" :style="iconStyle"
                        aria-hidden="true" />
                </div>
            </div>

            <p class="tabular-nums font-bold tracking-tight text-[color:var(--th-text-primary)]"
                :class="featured ? 'mt-6 text-5xl leading-none' : 'mt-4 text-2xl'">
                {{ displayedValue }}
            </p>

            <p class="font-medium text-[color:var(--th-text-secondary)]"
                :class="featured ? 'mt-2 text-base' : 'mt-1 text-sm'">
                {{ label }}
            </p>
        </div>
    </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Icon } from '@iconify/vue';
import { useCountUp } from '@/composables/useCountUp';
import { usePrimeColorStyles } from '@/composables/usePrimeColorStyles';

interface Props {
    label: string;
    value: string | number;
    icon: string;
    color?: string;
    featured?: boolean;
    live?: boolean;
    tooltip?: string;
}

const props = defineProps<Props>();

defineEmits<{ (e: 'click'): void }>();

const resolvedColor = computed(() => props.color ?? 'blue');
const {
    iconStyle,
    dotStyle,
    badgeStyle,
    barStyle,
    blobStyle,
    gradientRingStyle,
    pingStyle,
} = usePrimeColorStyles(resolvedColor);

const tooltipBackdropStyle = computed(() => ({
    background: `linear-gradient(
        to top,
        color-mix(in srgb, var(--p-${resolvedColor.value}-500) 85%, transparent) 0%,
        color-mix(in srgb, var(--p-${resolvedColor.value}-500) 40%, transparent) 55%,
        transparent 100%
    )`,
}));

const tooltipTextStyle = computed(() => ({
    textShadow: '0 1px 3px rgba(0,0,0,0.35)',
}));

const numericValue = computed<number | null>(() => {
    if (typeof props.value === 'number') return props.value;
    const n = Number(String(props.value).replace(/[,.\s]/g, ''));
    return isNaN(n) ? null : n;
});

const { displayed: countedRaw } = useCountUp(() =>
    props.featured && numericValue.value !== null ? numericValue.value : 0
);

const displayedValue = computed(() => {
    if (!props.featured || numericValue.value === null) return props.value;
    return countedRaw.value.toLocaleString('es');
});
</script>
