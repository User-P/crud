<template>
    <div class="h-full w-full min-w-[64px]" aria-hidden="true" role="presentation">
        <svg
            class="h-full w-full overflow-visible"
            :viewBox="`0 0 ${width} ${height}`"
            preserveAspectRatio="none"
        >
            <defs v-if="filled && strokeColor">
                <linearGradient :id="gradId" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" :stop-color="strokeColor" stop-opacity="0.28" />
                    <stop offset="85%" :stop-color="strokeColor" stop-opacity="0.04" />
                    <stop offset="100%" :stop-color="strokeColor" stop-opacity="0" />
                </linearGradient>
            </defs>

            <!-- Area fill (under the line) -->
            <path
                v-if="filled && fillD"
                :d="fillD"
                :fill="`url(#${gradId})`"
                stroke="none"
            />

            <!-- Line -->
            <path
                v-if="lineD"
                :d="lineD"
                fill="none"
                :stroke="strokeColor"
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
        </svg>
    </div>
</template>

<script setup lang="ts">
import { computed, useId } from 'vue'

const props = withDefaults(defineProps<{
    /** Data points to plot */
    data: number[]
    /** Line / fill colour theme */
    color?: 'blue' | 'green' | 'red' | 'slate' | 'violet'
    /** Render a gradient fill area under the line */
    filled?: boolean
}>(), {
    color: 'slate',
    filled: false,
})

// Unique gradient ID so multiple sparklines on the same page don't collide
const gradId = `sg-${useId()}`

const width = 80
const height = 32

const strokeColor = computed(() => {
    const map: Record<string, string> = {
        blue:   '#0b4261',
        green:  '#5bb56a',
        red:    '#ef4444',
        slate:  '#64666a',
        violet: '#0b4261',
    }
    return map[props.color] ?? map.slate
})

/** Normalised x,y coordinates for each data point */
const pts = computed(() => {
    const d = props.data
    if (!d.length) return []
    const min = Math.min(...d)
    const max = Math.max(...d)
    const range = max - min || 1
    const pad = 2
    const w = width - pad * 2
    const h = height - pad * 2
    const step = d.length > 1 ? w / (d.length - 1) : 0
    return d.map((v, i) => ({
        x: pad + i * step,
        y: pad + h - ((v - min) / range) * h,
    }))
})

const lineD = computed(() => {
    const p = pts.value
    if (!p.length) return ''
    return `M ${p.map((pt) => `${pt.x},${pt.y}`).join(' L ')}`
})

/** Closed path that traces the line then drops to the baseline, forming a filled area */
const fillD = computed(() => {
    const p = pts.value
    if (p.length < 2) return ''
    const first = p[0]
    const last = p[p.length - 1]
    const baseline = height - 1
    return `M ${p.map((pt) => `${pt.x},${pt.y}`).join(' L ')} L ${last.x},${baseline} L ${first.x},${baseline} Z`
})
</script>
