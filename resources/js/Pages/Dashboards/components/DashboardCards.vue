<template>
    <a
        :href="href"
        class="dashboard-card group relative flex flex-col overflow-hidden rounded-2xl border border-(--th-border) bg-(--th-input-bg) p-6 transition-all duration-300 hover:-translate-y-1 hover:border-(--th-input-focus-border) hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-(--p-focus-ring-color) focus:ring-offset-2 focus:ring-offset-(--th-ring-offset) dark:focus:ring-offset-(--th-ring-offset)"
        @click.prevent="onNavigate(href)"
    >
        <!-- Acento superior sutil (premium) -->
        <div
            class="absolute left-0 right-0 top-0 h-0.5 rounded-t-2xl opacity-0 transition-opacity duration-300 group-hover:opacity-100"
            :class="accentBarClass"
            aria-hidden="true"
        />

        <div class="flex items-center justify-between">
            <div
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl shadow-sm transition-all duration-300 group-hover:scale-105 group-hover:shadow-md"
                :class="iconBg"
            >
                <Icon :icon="icon" :class="iconColor" height="24px" aria-hidden="true" />
            </div>
            <span
                class="rounded-full border border-(--th-input-border) bg-(--th-input-bg) px-2.5 py-0.5 text-xs font-semibold tracking-wide text-(--th-text-secondary) backdrop-blur transition-colors duration-200 group-hover:border-(--th-input-focus-border) group-hover:text-(--th-text-primary)"
            >
                {{ badge }}
            </span>
        </div>

        <h3 class="mt-4 text-lg font-semibold tracking-tight text-(--th-text-primary)">
            {{ name }}
        </h3>

        <p class="mt-2 flex-1 text-sm leading-relaxed text-(--th-text-secondary)">
            {{ description }}
        </p>

        <span
            class="mt-4 inline-flex items-center text-sm font-medium text-(--th-item-active-color) transition-all duration-200 group-hover:gap-2 dark:text-(--th-item-active-color)"
        >
            Abrir dashboard
            <Icon
                icon="heroicons:arrow-right"
                class="ml-1 h-4 w-4 shrink-0 transition-transform duration-200 group-hover:translate-x-0.5"
                aria-hidden="true"
            />
        </span>
    </a>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '@iconify/vue'

interface Props {
    href: string
    name: string
    description: string
    badge: string | number
    icon: string
    /** Clases para el contenedor del icono (incluir dark: para tema oscuro) */
    iconBg?: string
    /** Clases para el color del icono (incluir dark: para que destaque en oscuro) */
    iconColor?: string
    /** Clase para la barra de acento superior (opcional, por variante) */
    accentBar?: string
}

const props = withDefaults(defineProps<Props>(), {
    iconBg: 'bg-indigo-500/15 dark:bg-indigo-400/25',
    iconColor: 'text-indigo-600 dark:text-indigo-400',
    accentBar: 'bg-indigo-500 dark:bg-indigo-400',
})

const emit = defineEmits<{
    (e: 'navigate', href: string): void
}>()

const accentBarClass = computed(() => props.accentBar || 'bg-indigo-500 dark:bg-indigo-400')

function onNavigate(href: string) {
    emit('navigate', href)
}
</script>
