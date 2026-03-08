<template>
    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-10">
            <DashboardHeader :title="title" :subtitle="subtitle" icon="heroicons:user-group">
                <template #actions>
                    <CustomPicker :initial-range="{ start: '2025-01-01', end: '2025-01-31' }" initial-type="custom"
                        select-disabled class="rounded-xl" />
                </template>
            </DashboardHeader>

            <div v-if="isLoading" class="flex justify-center py-16">
                <p class="text-sm text-(--th-text-muted)">Cargando indicadores…</p>
            </div>

            <template v-else>
                <!-- Billboard hero: banda full-width, número gigante -->
                <button
                    v-if="heroCard"
                    type="button"
                    class="billboard-hero group relative w-full overflow-hidden rounded-3xl px-8 py-10 text-left transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-(--p-focus-ring-color) focus:ring-offset-2 focus:ring-offset-(--th-ring-offset) sm:px-10 sm:py-12"
                    @click="openDetail(heroCard)"
                >
                    <span class="billboard-hero__bg absolute inset-0 rounded-3xl" aria-hidden="true" />
                    <span class="billboard-hero__blob absolute -right-20 -top-20 h-64 w-64 rounded-full opacity-30 blur-3xl" aria-hidden="true" />
                    <div class="relative z-10 flex flex-wrap items-end justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <div class="icon-box flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl shadow-lg">
                                <Icon :icon="heroCard.icon" class="h-8 w-8" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-wider text-(--th-text-secondary)">
                                    Métrica principal
                                </p>
                                <p class="mt-1 text-4xl font-bold tabular-nums tracking-tight text-(--th-text-primary) sm:text-5xl">
                                    {{ heroCard.value }}
                                </p>
                                <p class="mt-1 text-lg font-medium text-(--th-text-secondary)">{{ heroCard.label }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-medium text-(--th-text-muted) group-hover:text-(--th-item-active-color)">
                            Clic para detalle →
                        </span>
                    </div>
                </button>

                <!-- Dos KPIs principales: glass con barra lateral -->
                <div class="grid gap-5 sm:grid-cols-2">
                    <MetricCard
                        v-for="card in primaryCards"
                        :key="card.id"
                        :label="card.label"
                        :value="card.value"
                        :icon="card.icon"
                        :variant="card.variant"
                        @click="openDetail(card)"
                    />
                </div>

                <!-- Otras métricas: lista compacta clickeable -->
                <section>
                    <p class="mb-4 text-xs font-semibold uppercase tracking-widest text-(--th-group-label)">
                        Otras métricas
                    </p>
                    <div ref="gridRef" class="metric-strip flex flex-col overflow-hidden rounded-2xl border border-(--th-border) bg-(--th-input-bg) shadow-sm">
                        <button
                            v-for="card in secondaryCards"
                            :key="card.id"
                            type="button"
                            class="metric-strip__row group flex w-full items-center gap-4 border-b border-(--th-border) px-5 py-4 text-left last:border-b-0 transition-colors hover:bg-(--th-item-hover-bg) focus:outline-none focus:ring-2 focus:ring-inset focus:ring-(--p-focus-ring-color)"
                            @click="openDetail(card)"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-transform group-hover:scale-105"
                                :class="variantStyles[card.variant].iconBg"
                            >
                                <Icon :icon="card.icon" class="h-5 w-5" :class="variantStyles[card.variant].iconColor" aria-hidden="true" />
                            </div>
                            <span class="min-w-0 flex-1 font-medium text-(--th-text-primary)">{{ card.label }}</span>
                            <span class="tabular-nums font-semibold text-(--th-text-primary)">{{ card.value }}</span>
                            <Icon icon="heroicons:chevron-right" class="h-5 w-5 shrink-0 text-(--th-text-muted) group-hover:text-(--th-item-active-color)" aria-hidden="true" />
                        </button>
                    </div>
                </section>

                <p class="text-xs text-(--th-text-muted)">
                    Clic en cualquier elemento para ver detalle (gráficas + tabla)
                </p>
            </template>
        </div>

        <DetailDrawer :visible="!!selectedCard" :title="selectedCard ? selectedCard.label : ''"
            @close="selectedCard = null">
            <template v-if="selectedCard">
                <p class="mb-4 text-sm text-(--th-text-secondary)">
                    Detalle de <strong class="text-(--th-text-primary)">{{ selectedCard.label }}</strong>. Aquí se mostrarían gráficas y tabla según el tipo de métrica.
                </p>
                <div class="mb-4 flex h-48 items-center justify-center rounded-xl border border-(--th-border) bg-(--th-input-bg) text-sm text-(--th-text-muted)">
                    Gráfica de tendencia (placeholder)
                </div>
                <div class="overflow-hidden rounded-xl border border-(--th-border)">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-(--th-border) bg-(--th-input-bg)">
                                <th class="px-4 py-2.5 text-left font-semibold text-(--th-text-secondary)">Concepto</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-(--th-text-secondary)">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-(--th-border) last:border-b-0">
                                <td class="px-4 py-2.5 text-(--th-text-primary)">Total</td>
                                <td class="px-4 py-2.5 text-right font-medium text-(--th-text-primary)">{{ selectedCard.value }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </DetailDrawer>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DashboardHeader from '@/Components/Dashboards/DashboardHeader.vue'
import MetricCard from '@/Components/Dashboards/MetricCard.vue'
import DetailDrawer from '@/Components/Dashboards/DetailDrawer.vue'
import CustomPicker from '@/Components/Tables/Pickers/CustomPicker.vue'
import { Icon } from '@iconify/vue'
import { autoAnimate } from '@formkit/auto-animate'
import { useUsers } from './composables/useUsers'

const variantStyles: Record<string, { iconBg: string; iconColor: string }> = {
    blue: { iconBg: 'bg-blue-500/15 dark:bg-blue-400/20', iconColor: 'text-blue-600 dark:text-blue-400' },
    green: { iconBg: 'bg-emerald-500/15 dark:bg-emerald-400/20', iconColor: 'text-emerald-600 dark:text-emerald-400' },
    red: { iconBg: 'bg-rose-500/15 dark:bg-rose-400/20', iconColor: 'text-rose-600 dark:text-rose-400' },
}

type CardItem = {
    id: string
    label: string
    value: string
    icon: string
    variant: 'blue' | 'green' | 'red'
}

const page = usePage<{ unit?: string | null }>()
const unit = computed(() => page.props.unit ?? null)

const title = computed(() =>
    unit.value ? `Vista general · ${unit.value}` : 'Vista general'
)
const subtitle = 'Indicadores clave de usuarios por estatus. Clic en una tarjeta para ver detalle.'

const breadcrumbs = computed(() => [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Dashboards de métricas', href: '/dashboards' },
    { name: title.value },
])

const { users, isLoading, getIndicadores } = useUsers()

function apiCardToItem(raw: { id: string; label: string; value: number; variant: string; iconKey: string }): CardItem {
    return {
        id: raw.id,
        label: raw.label,
        value: String(raw.value),
        icon: raw.iconKey,
        variant: raw.variant as CardItem['variant'],
    }
}

const heroCard = computed<CardItem | null>(() => {
    const u = users.value?.hero
    return u ? apiCardToItem(u) : null
})

const primaryCards = computed<CardItem[]>(() => {
    const arr = users.value?.primary
    return Array.isArray(arr) ? arr.map(apiCardToItem) : []
})

const secondaryCards = computed<CardItem[]>(() => {
    const arr = users.value?.secondary
    return Array.isArray(arr) ? arr.map(apiCardToItem) : []
})

const gridRef = ref<HTMLElement | null>(null)
const selectedCard = ref<CardItem | null>(null)

function openDetail(card: CardItem) {
    selectedCard.value = card
}

function loadData() {
    const date = new Date().toISOString().slice(0, 10)
    getIndicadores(date, unit.value ?? undefined)
}

onMounted(() => {
    loadData()
    if (gridRef.value) autoAnimate(gridRef.value)
})

watch(unit, () => { loadData() })
</script>
