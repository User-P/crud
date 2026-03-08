<template>
    <a
        :href="href"
        class="dashboard-tile group relative flex overflow-hidden rounded-3xl transition-all duration-400 focus:outline-none focus:ring-2 focus:ring-(--p-focus-ring-color) focus:ring-offset-2 focus:ring-offset-(--th-ring-offset)"
        :class="[
            featured
                ? 'dashboard-tile--featured col-span-1 row-span-2 flex-col justify-between p-8 min-h-[280px]'
                : 'dashboard-tile--compact flex-col p-5 min-h-[140px]',
        ]"
        @click.prevent="onNavigate(href)"
    >
        <!-- Glass base -->
        <span
            class="absolute inset-0 rounded-3xl border border-white/20 bg-white/70 shadow-lg backdrop-blur-xl transition-all duration-400 dark:border-white/10 dark:bg-white/5 dark:shadow-none group-hover:bg-white/80 dark:group-hover:bg-white/8 group-hover:shadow-xl"
            aria-hidden="true"
        />
        <!-- Gradient blob (featured) or left accent (compact) -->
        <span
            v-if="featured"
            class="absolute -right-16 -top-16 h-40 w-40 rounded-full opacity-40 blur-2xl transition-opacity group-hover:opacity-60"
            :class="blobClass"
            aria-hidden="true"
        />
        <span
            v-else
            class="absolute left-0 top-4 bottom-4 w-1 rounded-full transition-all duration-300 group-hover:w-1.5"
            :class="accentBarClass"
            aria-hidden="true"
        />

        <div class="relative z-10 flex flex-1 flex-col">
            <div class="flex items-start justify-between gap-3">
                <div
                    class="flex shrink-0 items-center justify-center rounded-2xl transition-transform duration-300 group-hover:scale-105"
                    :class="[
                        featured ? 'h-14 w-14' : 'h-11 w-11 rounded-xl',
                        iconBg,
                    ]"
                >
                    <Icon :icon="icon" :class="[featured ? 'h-7 w-7' : 'h-5 w-5', iconColor]" aria-hidden="true" />
                </div>
                <span
                    class="rounded-full px-2.5 py-1 text-xs font-semibold tracking-wide text-(--th-text-secondary) ring-1 ring-(--th-border) transition-colors group-hover:text-(--th-text-primary) group-hover:ring-(--th-input-focus-border)"
                >
                    {{ badge }}
                </span>
            </div>

            <h3
                class="mt-4 font-semibold tracking-tight text-(--th-text-primary)"
                :class="featured ? 'text-xl' : 'text-base'"
            >
                {{ name }}
            </h3>
            <p
                class="mt-1.5 flex-1 text-(--th-text-secondary)"
                :class="featured ? 'text-sm leading-relaxed' : 'text-sm line-clamp-2'"
            >
                {{ description }}
            </p>
        </div>

        <div class="relative z-10 mt-4 flex items-center gap-2 text-(--th-item-active-color)">
            <span class="text-sm font-semibold">{{ featured ? 'Abrir vista' : 'Abrir' }}</span>
            <Icon
                icon="heroicons:arrow-right"
                class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                aria-hidden="true"
            />
        </div>
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
    iconBg?: string
    iconColor?: string
    accentBar?: string
    /** Card destacada (bento): más grande, con blob de gradiente */
    featured?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    iconBg: 'bg-[#0b4261]/20 dark:bg-[#5bb56a]/25',
    iconColor: 'text-[#0b4261] dark:text-[#5bb56a]',
    accentBar: 'bg-[#0b4261] dark:bg-[#5bb56a]',
    featured: false,
})

const emit = defineEmits<{
    (e: 'navigate', href: string): void
}>()

const accentBarClass = computed(() => props.accentBar || 'bg-[#0b4261] dark:bg-[#5bb56a]')

const blobClass = computed(() => {
    const a = (props.accentBar || '').toLowerCase()
    if (a.includes('emerald')) return 'bg-emerald-400'
    if (a.includes('amber')) return 'bg-amber-400'
    if (a.includes('blue')) return 'bg-blue-400'
    return 'bg-[#0b4261]'
})

function onNavigate(href: string) {
    emit('navigate', href)
}
</script>
