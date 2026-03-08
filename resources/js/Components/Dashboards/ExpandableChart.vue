<template>
    <div :class="expanded ? 'fixed inset-0 z-[100]' : ''">
        <!-- Overlay -->
        <div
            v-if="expanded"
            class="fixed inset-0 z-0 bg-slate-900/40 backdrop-blur-md"
            aria-hidden="true"
            @click="expanded = false"
        />
        <!-- Card: normal o flotante -->
        <div
            class="surface rounded-2xl transition-all duration-200"
            :class="[
                expanded
                    ? 'fixed inset-4 z-10 flex flex-col p-6 sm:inset-6 md:inset-8'
                    : 'p-6',
            ]"
        >

        <div class="mb-4 flex items-center justify-between gap-2">
            <h3
                class="text-sm font-semibold uppercase tracking-wider text-slate-600"
                :id="expanded ? 'expandable-chart-title' : undefined"
                :role="expanded ? 'heading' : undefined"
            >
                {{ title }}
            </h3>
            <div class="flex items-center gap-1">
                <button
                    type="button"
                    class="rounded-xl p-2 text-slate-500 transition hover:bg-white/50 hover:text-slate-700"
                    :title="expanded ? 'Reducir' : 'Expandir para ver mejor'"
                    :aria-label="expanded ? 'Reducir gráfica' : 'Expandir gráfica'"
                    @click="toggleExpand"
                >
                    <Icon v-if="!expanded" icon="heroicons:arrows-pointing-out" class="h-5 w-5" aria-hidden="true" />
                    <Icon v-else icon="heroicons:arrows-pointing-in" class="h-5 w-5" aria-hidden="true" />
                </button>
                <button
                    v-if="expanded"
                    type="button"
                    class="rounded-xl p-2 text-slate-500 transition hover:bg-white/50 hover:text-slate-700"
                    title="Cerrar"
                    aria-label="Cerrar vista expandida"
                    @click="expanded = false"
                >
                    <Icon icon="heroicons:x-mark" class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>
        </div>

        <!-- Un solo contenedor para el slot: al expandir crece y ResizeObserver en BaseEChart redimensiona el gráfico -->
        <div
            class="w-full flex-1 min-h-0 rounded-lg"
            :class="expanded ? 'h-[calc(100vh-12rem)] min-h-[400px]' : 'h-80'"
        >
            <slot />
        </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { Icon } from '@iconify/vue'

defineProps<{
    title: string
}>()

const expanded = ref(false)

function toggleExpand() {
    expanded.value = !expanded.value
}

watch(expanded, (isExpanded) => {
    document.body.style.overflow = isExpanded ? 'hidden' : ''
})
</script>
