<template>
    <div class="h-8 w-full min-w-[64px]" aria-hidden="true">
        <svg
            class="h-full w-full"
            :viewBox="`0 0 ${width} ${height}`"
            preserveAspectRatio="none"
        >
            <path
                :d="pathD"
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
import { computed } from 'vue'

const props = withDefaults(defineProps<{
    /** Valores para la línea (ej. últimos 7 días) */
    data: number[]
    /** Color de la línea (violet = acento del tema Cosmos) */
    color?: 'blue' | 'green' | 'red' | 'slate' | 'violet'
}>(), {
    color: 'slate',
})

const width = 80
const height = 32

const strokeColor = computed(() => {
    const map: Record<string, string> = {
        blue: '#0b4261',
        green: '#5bb56a',
        red: '#ef4444',
        slate: '#64666a',
        violet: '#0b4261',
    }
    return map[props.color] ?? map.slate
})

const pathD = computed(() => {
    const d = props.data
    if (!d.length) return ''
    const min = Math.min(...d)
    const max = Math.max(...d)
    const range = max - min || 1
    const pad = 2
    const w = width - pad * 2
    const h = height - pad * 2
    const step = d.length > 1 ? w / (d.length - 1) : 0
    const points = d.map((v, i) => {
        const x = pad + i * step
        const y = pad + h - ((v - min) / range) * h
        return `${x},${y}`
    })
    return `M ${points.join(' L ')}`
})
</script>
