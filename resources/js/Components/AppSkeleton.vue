<template>
    <div
        class="app-skeleton flex flex-col gap-3 rounded-2xl border border-(--th-border) bg-(--th-input-bg)/60 p-5"
        :class="{ 'app-skeleton--card': variant === 'card', 'app-skeleton--row': variant === 'row' }"
        role="status"
        aria-label="Cargando"
    >
        <template v-if="variant === 'card'">
            <div class="flex items-start justify-between gap-3">
                <div class="h-11 w-11 shrink-0 rounded-xl bg-(--th-border) animate-pulse" />
                <div v-if="showBadge" class="h-6 w-14 rounded-full bg-(--th-border) animate-pulse" />
            </div>
            <div class="h-7 w-3/4 max-w-[180px] rounded-lg bg-(--th-border) animate-pulse" />
            <div class="h-4 w-full max-w-[240px] rounded bg-(--th-border) animate-pulse" />
            <div class="h-4 w-1/2 max-w-[120px] rounded bg-(--th-border) animate-pulse" />
        </template>
        <template v-else-if="variant === 'row'">
            <div class="flex gap-4">
                <div class="h-10 w-10 shrink-0 rounded-lg bg-(--th-border) animate-pulse" />
                <div class="min-w-0 flex-1 space-y-2">
                    <div class="h-4 w-2/3 rounded bg-(--th-border) animate-pulse" />
                    <div class="h-3 w-1/2 rounded bg-(--th-border) animate-pulse" />
                </div>
            </div>
        </template>
        <template v-else>
            <div v-for="i in lines" :key="i" class="flex gap-2" :class="i === 1 ? '' : 'mt-2'">
                <div
                    class="h-4 rounded bg-(--th-border) animate-pulse"
                    :style="{ width: lineWidth(i) }"
                />
            </div>
        </template>
    </div>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        /** card = KPI card (icon + título + líneas), row = fila compacta, text = N líneas de texto */
        variant?: 'card' | 'row' | 'text'
        /** Solo variant card: mostrar bloque tipo badge */
        showBadge?: boolean
        /** Solo variant text: número de líneas */
        lines?: number
    }>(),
    { variant: 'text', showBadge: false, lines: 3 }
)

function lineWidth(i: number): string {
    if (i === 1) return '100%'
    if (i === 2) return '85%'
    if (i === 3) return '70%'
    return `${Math.max(40, 90 - i * 8)}%`
}
</script>
