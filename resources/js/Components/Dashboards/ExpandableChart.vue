<template>
    <div :class="expanded ? 'fixed inset-0 z-[100]' : ''">
        <!-- Overlay: clic cierra; detrás de la card -->
        <div
            v-if="expanded"
            class="fixed inset-0 z-0 bg-slate-900/50 backdrop-blur-sm"
            aria-hidden="true"
            @click="expanded = false"
        />
        <!-- Card: normal o flotante sobre el overlay -->
        <div
            class="rounded-2xl border border-slate-200/80 bg-white shadow-sm transition-all duration-200"
            :class="[
                expanded
                    ? 'fixed inset-4 z-10 flex flex-col rounded-2xl border-slate-200 bg-white p-6 shadow-2xl sm:inset-6 md:inset-8'
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
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    :title="expanded ? 'Reducir' : 'Expandir para ver mejor'"
                    :aria-label="expanded ? 'Reducir gráfica' : 'Expandir gráfica'"
                    @click="toggleExpand"
                >
                    <ArrowsPointingOutIcon v-if="!expanded" class="h-5 w-5" aria-hidden="true" />
                    <ArrowsPointingInIcon v-else class="h-5 w-5" aria-hidden="true" />
                </button>
                <button
                    v-if="expanded"
                    type="button"
                    class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-600"
                    title="Cerrar"
                    aria-label="Cerrar vista expandida"
                    @click="expanded = false"
                >
                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
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
import { ArrowsPointingOutIcon, ArrowsPointingInIcon, XMarkIcon } from '@heroicons/vue/24/outline'

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
