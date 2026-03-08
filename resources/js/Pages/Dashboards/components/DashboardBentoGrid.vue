<template>
    <div
        class="dashboard-bento-grid grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:grid-rows-[1fr_1fr] lg:gap-5">
        <div v-for="(card, i) in cards" :key="card.name" :class="i === 0 ? 'lg:row-span-2 lg:min-h-0 lg:h-full' : ''">
            <component :is="getTag(card)" :href="getHref(card)"
                class="dashboard-tile group relative flex overflow-hidden rounded-3xl transition-all duration-400 focus:outline-none focus:ring-2 focus:ring-(--p-focus-ring-color) focus:ring-offset-2 focus:ring-offset-(--th-ring-offset)"
                :class="[
                    i === 0
                        ? 'dashboard-tile--featured col-span-1 row-span-2 flex-col justify-between p-8 min-h-[280px] h-full'
                        : 'dashboard-tile--compact flex-col p-5 min-h-[140px]',
                ]" @click.prevent="(e: Event) => onClick(e, card)">
                <!-- Glass base -->
                <span
                    class="absolute inset-0 rounded-3xl border border-white/20 bg-white/70 shadow-lg backdrop-blur-xl transition-all duration-400 dark:border-white/10 dark:bg-white/5 dark:shadow-none group-hover:bg-white/80 dark:group-hover:bg-white/8 group-hover:shadow-xl"
                    aria-hidden="true" />
                <!-- Gradient blob (featured) or left accent (compact) -->
                <span v-if="i === 0"
                    class="absolute -right-16 -top-16 h-40 w-40 rounded-full opacity-40 blur-2xl transition-opacity group-hover:opacity-60"
                    :class="blobClass(card)" aria-hidden="true" />
                <span v-else
                    class="absolute left-0 top-4 bottom-4 w-1 rounded-full transition-all duration-300 group-hover:w-1.5"
                    :class="accentBarClass(card)" aria-hidden="true" />

                <div class="relative z-10 flex flex-1 flex-col">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex shrink-0 items-center justify-center rounded-2xl transition-transform duration-300 group-hover:scale-105"
                            :class="[
                                i === 0 ? 'h-14 w-14' : 'h-11 w-11 rounded-xl',
                                card.iconBg ?? 'bg-[#0b4261]/20 dark:bg-[#5bb56a]/25',
                            ]">
                            <Icon :icon="card.icon"
                                :class="[i === 0 ? 'h-7 w-7' : 'h-5 w-5', card.iconColor ?? 'text-[#0b4261] dark:text-[#5bb56a]']"
                                aria-hidden="true" />
                        </div>
                        <span
                            class="rounded-full px-2.5 py-1 text-xs font-semibold tracking-wide text-(--th-text-secondary) ring-1 ring-(--th-border) transition-colors group-hover:text-(--th-text-primary) group-hover:ring-(--th-input-focus-border)">
                            {{ card.badge }}
                        </span>
                    </div>

                    <h3 class="mt-4 font-semibold tracking-tight text-(--th-text-primary)"
                        :class="i === 0 ? 'text-xl' : 'text-base'">
                        {{ card.name }}
                    </h3>
                    <p class="mt-1.5 flex-1 text-(--th-text-secondary)"
                        :class="i === 0 ? 'text-sm leading-relaxed' : 'text-sm line-clamp-2'">
                        {{ card.description }}
                    </p>
                </div>

                <div class="relative z-10 mt-4 flex items-center gap-2 text-(--th-item-active-color)">
                    <span class="text-sm font-semibold">{{ ctaLabel(card) }}</span>
                    <Icon :icon="isScrollMode(card) ? 'heroicons:arrow-down' : 'heroicons:arrow-right'"
                        class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1 group-hover:translate-y-0.5"
                        aria-hidden="true" />
                </div>
            </component>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Icon } from '@iconify/vue'

export interface DashboardCardItem {
    name: string
    description: string
    href: string
    icon: string
    iconBg?: string
    iconColor?: string
    accentBar?: string
    badge: string | number
}

export interface CardWithSection extends DashboardCardItem {
    sectionId?: string
}

const props = defineProps<{
    cards: CardWithSection[]
    /** navigate = enlaces a otras páginas; scroll = botones que hacen scroll en esta página */
    mode: 'navigate' | 'scroll'
}>()

const emit = defineEmits<{
    (e: 'navigate', href: string): void
    (e: 'scroll-to', sectionId: string): void
}>()

function isScrollMode(card: CardWithSection): boolean {
    return props.mode === 'scroll' && !!card.sectionId
}

function getTag(card: CardWithSection): 'a' | 'button' {
    return isScrollMode(card) ? 'button' : 'a'
}

function getHref(card: CardWithSection): string | undefined {
    return isScrollMode(card) ? undefined : card.href
}

function ctaLabel(card: CardWithSection): string {
    if (isScrollMode(card)) return 'Ver en esta página'
    return props.cards.indexOf(card) === 0 ? 'Abrir vista' : 'Abrir'
}

function accentBarClass(card: CardWithSection): string {
    return card.accentBar ?? 'bg-[#0b4261] dark:bg-[#5bb56a]'
}

function blobClass(card: CardWithSection): string {
    const a = (card.accentBar ?? '').toLowerCase()
    if (a.includes('emerald')) return 'bg-emerald-400'
    if (a.includes('amber')) return 'bg-amber-400'
    if (a.includes('blue')) return 'bg-blue-400'
    return 'bg-[#0b4261]'
}

function onClick(_e: Event, card: CardWithSection) {
    if (isScrollMode(card) && card.sectionId) {
        emit('scroll-to', card.sectionId)
    } else {
        emit('navigate', card.href)
    }
}
</script>
