<template>
    <AdminLayout title="Dashboard de métricas" subtitle="Vista consolidada: todos los indicadores en una sola pantalla"
        :breadcrumbs="[
            { name: 'Dashboard', href: '/dashboard' },
            { name: 'Dashboards de métricas', href: '/dashboards' },
            { name: 'Vista consolidada' },
        ]">
        <div class="space-y-14">
            <!-- ── Vista general ── -->
            <section :id="sectionIds.vistaGeneral" class="scroll-mt-6">
                <div class="consolidated-section rounded-r-2xl border border-(--th-border) border-l-4 border-l-[#0b4261] bg-(--th-input-bg)/50 pl-6 pr-5 py-5 dark:border-l-[#5bb56a] space-y-6">
                    <h2 class="mb-6 text-lg font-semibold text-(--th-text-primary) pb-2">
                        Vista general
                    </h2>
                    <div class="grid gap-5 sm:grid-cols-3">
                        <MetricCard v-for="card in primaryCards" :key="card.id" :label="card.label" :value="card.value"
                            :icon="card.icon" :variant="card.variant" class="transition-shadow"
                            :class="{ 'ring-2 ring-(--th-input-focus-border) ring-offset-2 ring-offset-(--th-ring-offset)': selectedCard?.id === card.id }"
                            @click="selectedCard = card" />
                    </div>
                    <div>
                        <p class="mb-4 text-xs font-semibold uppercase tracking-widest text-(--th-group-label)">Otras
                            métricas</p>
                        <div
                            class="flex flex-col overflow-hidden rounded-2xl border border-(--th-border) bg-(--th-input-bg) shadow-sm">
                            <button v-for="card in secondaryCards" :key="card.id" type="button"
                                class="group flex w-full items-center gap-4 border-b border-(--th-border) px-5 py-4 text-left last:border-b-0 transition-colors hover:bg-(--th-item-hover-bg) focus:outline-none focus:ring-2 focus:ring-inset focus:ring-(--p-focus-ring-color)"
                                :class="{ 'bg-(--th-item-hover-bg)': selectedCard?.id === card.id }"
                                @click="selectedCard = card">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-transform group-hover:scale-105"
                                    :class="variantStyles[card.variant].iconBg">
                                    <Icon :icon="card.icon" class="h-5 w-5"
                                        :class="variantStyles[card.variant].iconColor" aria-hidden="true" />
                                </div>
                                <span class="min-w-0 flex-1 font-medium text-(--th-text-primary)">{{ card.label
                                    }}</span>
                                <span class="tabular-nums font-semibold text-(--th-text-primary)">{{ card.value
                                    }}</span>
                                <Icon icon="heroicons:chevron-right"
                                    class="h-5 w-5 shrink-0 text-(--th-text-muted) group-hover:text-(--th-item-active-color)"
                                    aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-(--th-border) bg-(--th-input-bg) overflow-hidden shadow-sm">
                        <div
                            class="flex flex-wrap items-center justify-between gap-3 border-b border-(--th-border) px-4 py-3">
                            <h3 class="text-sm font-semibold text-(--th-text-primary)">
                                {{ selectedCard ? `Detalle: ${selectedCard.label}` : 'Detalle de la métrica' }}
                            </h3>
                            <button v-if="selectedCard" type="button"
                                class="text-xs font-medium text-(--th-text-muted) hover:text-(--th-item-active-color) transition-colors"
                                @click="selectedCard = null">
                                Limpiar selección
                            </button>
                        </div>
                        <div v-if="!selectedCard"
                            class="flex flex-col items-center justify-center gap-2 px-4 py-12 text-center">
                            <Icon icon="heroicons:table-cells" class="h-10 w-10 text-(--th-text-muted)"
                                aria-hidden="true" />
                            <p class="text-sm text-(--th-text-muted)">Selecciona una métrica arriba para ver el detalle.
                            </p>
                        </div>
                        <div v-else class="overflow-x-auto px-4 pb-4">
                            <DetailMetricTable
                                :rows="detailRows"
                                :has-porcentaje="hasPorcentaje"
                                :has-actualizado="hasActualizado"
                                :format-valor="formatValor"
                                :export-label="selectedCard?.label"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── Usuarios activos / inactivos ── -->
            <section :id="sectionIds.activosInactivos" class="scroll-mt-6">
                <div class="consolidated-section rounded-r-2xl border border-(--th-border) border-l-4 border-l-emerald-500 bg-(--th-input-bg)/50 pl-6 pr-5 py-5 dark:border-l-emerald-400">
                    <h2 class="mb-6 text-lg font-semibold text-(--th-text-primary) pb-2">
                        Usuarios activos vs inactivos
                    </h2>
                <div class="space-y-8">
                    <div
                        class="rounded-xl border border-(--th-border) bg-(--th-input-bg)/80 px-4 py-3 text-sm text-(--th-text-secondary) backdrop-blur-sm">
                        <span class="font-medium text-(--th-text-primary)">Resumen:</span>
                        El 94% de los usuarios están activos; 6% inactivos. La mayoría en estatus PROVISIONED.
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <MetricCard label="Usuarios totales" value="140,000" icon="heroicons:globe-alt" variant="violet"
                            trend="up" :trend-percent="2.4" :sparkline-data="[128, 131, 132, 130, 134, 138, 140]"
                            comparison="vs. mes anterior" />
                        <MetricCard label="Usuarios activos" value="132,000" icon="heroicons:check-circle"
                            variant="green" trend="up" :trend-percent="1.8"
                            :sparkline-data="[126, 128, 129, 130, 131, 132, 132]" comparison="vs. mes anterior" />
                        <MetricCard label="Usuarios inactivos" value="8,000" icon="heroicons:x-circle" variant="red"
                            trend="down" :trend-percent="-0.5" :sparkline-data="[9, 8.5, 8.2, 8.1, 8.2, 8, 8]"
                            comparison="vs. mes anterior" />
                    </div>
                    <div class="grid gap-6 lg:grid-cols-2">
                        <ExpandableChart title="Activos / inactivos">
                            <PieChart :data="[{ name: 'Activos', value: 132000 }, { name: 'Inactivos', value: 8000 }]"
                                legend-position="bottom" donut />
                        </ExpandableChart>
                        <ExpandableChart title="Estatus de usuario">
                            <HorizontalBarChart title=""
                                :categories="['PROVISIONED', 'PASSWORD EXPIRED', 'LOCKED OUT', 'SUSPENDED']" :series="[
                                    { name: 'PROVISIONED', data: [122, 0, 0, 0] },
                                    { name: 'PASSWORD EXPIRED', data: [0, 20, 0, 0] },
                                    { name: 'LOCKED OUT', data: [0, 0, 50, 0] },
                                    { name: 'SUSPENDED', data: [0, 0, 0, 30] },
                                ]" />
                        </ExpandableChart>
                    </div>
                </div>
                </div>
            </section>

            <!-- ── Días suspendidos ── -->
            <section :id="sectionIds.diasSuspendidos" class="scroll-mt-6">
                <div class="consolidated-section rounded-r-2xl border border-(--th-border) border-l-4 border-l-amber-500 bg-(--th-input-bg)/50 pl-6 pr-5 py-5 dark:border-l-amber-400">
                    <h2 class="mb-6 text-lg font-semibold text-(--th-text-primary) pb-2">
                        Días usuarios suspendidos
                    </h2>
                <div class="space-y-8">
                    <div
                        class="rounded-xl border border-(--th-border) bg-(--th-input-bg)/80 px-4 py-3 text-sm text-(--th-text-secondary) backdrop-blur-sm">
                        <span class="font-medium text-(--th-text-primary)">Insight:</span>
                        El 44% de los usuarios suspendidos llevan 7+ días (riesgo elevado). Priorizar revisión de ese
                        grupo.
                    </div>
                    <div
                        class="flex flex-wrap gap-6 rounded-xl border border-(--th-border) bg-(--th-input-bg) px-5 py-4">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-amber-400" aria-hidden="true" />
                            <span class="text-sm text-(--th-text-secondary)">1-3 días (riesgo menor)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-orange-500" aria-hidden="true" />
                            <span class="text-sm text-(--th-text-secondary)">4-6 días (riesgo moderado)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-rose-500" aria-hidden="true" />
                            <span class="text-sm text-(--th-text-secondary)">7+ días (riesgo elevado)</span>
                        </div>
                    </div>
                    <ExpandableChart title="Usuarios suspendidos por rango de días">
                        <SemaphoreBarChart :categories="['1-3 días', '4-6 días', '7+ días']" :values="[600, 900, 1200]"
                            :colors="['#eab308', '#f97316', '#ef4444']" />
                    </ExpandableChart>
                </div>
                </div>
            </section>

            <!-- ── Usuarios nuevos ── -->
            <section :id="sectionIds.usuariosNuevos" class="scroll-mt-6">
                <div class="consolidated-section rounded-r-2xl border border-(--th-border) border-l-4 border-l-blue-500 bg-(--th-input-bg)/50 pl-6 pr-5 py-5 dark:border-l-blue-400">
                    <h2 class="mb-6 text-lg font-semibold text-(--th-text-primary) pb-2">
                        Usuarios nuevos
                    </h2>
                <div class="space-y-8">
                    <div v-if="usersAdd?.cards" class="grid gap-4 sm:grid-cols-3">
                        <MetricCard v-for="card in usersAdd.cards" :key="card.id" :label="card.label"
                            :value="formatValue(card.value)"
                            :icon="'iconKey' in card ? (card as { iconKey: string }).iconKey : (card as { icon: string }).icon"
                            :variant="(card.variant === 'yellow' ? 'blue' : card.variant) as 'blue' | 'green' | 'red' | 'violet'" />
                    </div>
                    <ExpandableChart title="Tendencia de altas (mensual)">
                        <div class="flex h-64 items-center justify-center text-sm text-(--th-text-muted)">
                            Gráfica de línea: {{ usersAdd?.line?.labels?.length ?? 0 }} meses (datos dummy)
                        </div>
                    </ExpandableChart>
                    <ExpandableChart title="Altas por día (última semana)">
                        <div class="flex h-64 items-center justify-center text-sm text-(--th-text-muted)">
                            Gráfica de barras: {{ usersAdd?.bar?.labels?.length ?? 0 }} días (datos dummy)
                        </div>
                    </ExpandableChart>
                </div>
                </div>
            </section>

            <!-- ── Por unidad de negocio ── -->
            <section :id="sectionIds.unidades" class="scroll-mt-6">
                <div class="consolidated-section rounded-r-2xl border border-(--th-border) border-l-4 border-l-[#0b4261] bg-(--th-input-bg)/50 pl-6 pr-5 py-5 dark:border-l-[#5bb56a]">
                    <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-(--th-group-label)">
                        Por unidad de negocio
                    </h2>
                <div class="flex flex-wrap gap-3">
                    <a v-for="card in byUnits" :key="card.name" :href="card.href"
                        class="unit-pill group flex items-center gap-3 rounded-2xl border border-white/20 bg-white/70 px-5 py-3.5 shadow-md backdrop-blur-xl transition-all duration-300 hover:-translate-y-0.5 hover:border-white/30 hover:bg-white/90 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-(--p-focus-ring-color) focus:ring-offset-2 focus:ring-offset-(--th-ring-offset) dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10"
                        @click.prevent="navigate(card.href)">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-transform group-hover:scale-105"
                            :class="card.iconBg">
                            <Icon :icon="card.icon" :class="card.iconColor" class="h-5 w-5" aria-hidden="true" />
                        </div>
                        <div class="min-w-0">
                            <span class="block truncate font-semibold text-(--th-text-primary)">{{ card.name }}</span>
                            <span class="text-xs text-(--th-text-muted)">{{ card.badge }}</span>
                        </div>
                        <Icon icon="heroicons:arrow-right"
                            class="h-4 w-4 shrink-0 text-(--th-item-active-color) transition-transform group-hover:translate-x-0.5"
                            aria-hidden="true" />
                    </a>
                </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DetailMetricTable from '@/Components/Dashboards/DetailMetricTable.vue'
import MetricCard from '@/Components/Dashboards/MetricCard.vue'
import ExpandableChart from '@/Components/Dashboards/ExpandableChart.vue'
import PieChart from '@/Components/Charts/PieChart.vue'
import HorizontalBarChart from '@/Components/Charts/HorizontalBarChart.vue'
import SemaphoreBarChart from '@/Components/Charts/SemaphoreBarChart.vue'
import { useUsers } from './composables/useUsers'
import type { DetailTableRow } from './composables/useUsers'

const sectionIds = {
    vistaGeneral: 'vista-general',
    activosInactivos: 'activos-inactivos',
    diasSuspendidos: 'dias-suspendidos',
    usuariosNuevos: 'usuarios-nuevos',
    unidades: 'unidades',
}

const { getIndicadores, getUsersAdd, detailsByCard,  byUnits, users, usersAdd } = useUsers()

const variantStyles: Record<string, { iconBg: string; iconColor: string }> = {
    blue: { iconBg: 'bg-blue-500/15 dark:bg-blue-400/20', iconColor: 'text-blue-600 dark:text-blue-400' },
    green: { iconBg: 'bg-emerald-500/15 dark:bg-emerald-400/20', iconColor: 'text-emerald-600 dark:text-emerald-400' },
    red: { iconBg: 'bg-rose-500/15 dark:bg-rose-400/20', iconColor: 'text-rose-600 dark:text-rose-400' },
}

type CardItem = { id: string; label: string; value: string; icon: string; variant: 'blue' | 'green' | 'red' }

const selectedCard = ref<CardItem | null>(null)

function apiCardToItem(raw: { id: string; label: string; value: number; variant: string; iconKey: string }): CardItem {
    return { id: raw.id, label: raw.label, value: String(raw.value), icon: raw.iconKey, variant: raw.variant as CardItem['variant'] }
}

const primaryCards = computed<CardItem[]>(() => {
    const arr = users.value?.primary
    return Array.isArray(arr) ? arr.map(apiCardToItem) : []
})

const secondaryCards = computed<CardItem[]>(() => {
    const arr = users.value?.secondary
    return Array.isArray(arr) ? arr.map(apiCardToItem) : []
})

const detailRows = computed<DetailTableRow[]>(() => {
    if (!selectedCard.value) return []
    return detailsByCard[selectedCard.value.id] ?? []
})

const hasPorcentaje = computed(() => detailRows.value.some((r) => r.porcentaje != null))
const hasActualizado = computed(() => detailRows.value.some((r) => r.actualizado != null))

function formatValor(v: number | string): string {
    if (typeof v === 'number') return v.toLocaleString('es')
    return String(v)
}

function formatValue(value: unknown): string {
    if (typeof value === 'number') return value.toLocaleString()
    return String(value ?? '–')
}

function navigate(href: string) {
    router.visit(href)
}

onMounted(() => {
    const date = new Date().toISOString().slice(0, 10)
    getIndicadores(date)
    getUsersAdd(date)
})

watch(users, (u) => {
    if (u?.primary?.length && !selectedCard.value) selectedCard.value = apiCardToItem(u.primary[0])
}, { immediate: true })
</script>
