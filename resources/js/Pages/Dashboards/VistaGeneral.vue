<template>
    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-10">
            <DashboardHeader :title="title" :subtitle="subtitle" icon="heroicons:user-group">
                <!-- Stats chips: datos de resumen rápido bajo el título -->
                <template #stats>
                    <span
                        v-for="chip in headerChips"
                        :key="chip.label"
                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-(--th-border)"
                        style="background: rgba(255,255,255,0.55); backdrop-filter: blur(8px);"
                    >
                        <span class="h-1.5 w-1.5 rounded-full" :class="chip.dot" aria-hidden="true" />
                        <span class="text-(--th-text-secondary)">{{ chip.label }}:</span>
                        <span class="font-semibold text-(--th-text-primary)">{{ chip.value }}</span>
                    </span>
                </template>
                <template #actions>
                    <CustomPicker :initial-range="{ start: '2025-01-01', end: '2025-01-31' }" initial-type="custom"
                        select-disabled class="rounded-xl" />
                </template>
            </DashboardHeader>

            <!-- Skeleton loader -->
            <div v-if="isLoading" class="space-y-5">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-[2fr_1fr]">
                    <AppSkeleton variant="card" class="min-h-[220px]" />
                    <div class="flex flex-col gap-5">
                        <AppSkeleton variant="card" />
                        <AppSkeleton variant="card" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    <AppSkeleton v-for="n in 5" :key="n" variant="card" />
                </div>
            </div>

            <template v-else>
                <!-- ── Bento de métricas primarias ── -->
                <section>
                    <!-- Grid: hero card (2/3 width) + stacked cards (1/3 width) -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-[2fr_1fr]">
                        <!-- Hero card: spans 2 rows -->
                        <MetricCard
                            v-if="primaryCards[0]"
                            :featured="true"
                            :live="true"
                            :label="primaryCards[0].label"
                            :value="primaryCards[0].value"
                            :icon="primaryCards[0].icon"
                            :variant="primaryCards[0].variant"
                            :trend="heroTrend"
                            :trend-percent="2.4"
                            :sparkline-data="heroSparkline"
                            comparison="vs. mes anterior"
                            class="sm:row-span-2 h-full"
                            :class="{ 'ring-2 ring-(--th-input-focus-border) ring-offset-2 ring-offset-(--th-ring-offset)': selectedCard?.id === primaryCards[0]?.id }"
                            @click="primaryCards[0] && openDetail(primaryCards[0])"
                        />

                        <!-- Stacked right column -->
                        <div class="flex flex-col gap-5">
                            <MetricCard
                                v-if="primaryCards[1]"
                                :label="primaryCards[1].label"
                                :value="primaryCards[1].value"
                                :icon="primaryCards[1].icon"
                                :variant="primaryCards[1].variant"
                                :trend="'up'"
                                :trend-percent="1.8"
                                :sparkline-data="[126, 128, 129, 130, 131, 132, 132]"
                                comparison="vs. mes anterior"
                                class="flex-1"
                                :class="{ 'ring-2 ring-(--th-input-focus-border) ring-offset-2 ring-offset-(--th-ring-offset)': selectedCard?.id === primaryCards[1]?.id }"
                                @click="primaryCards[1] && openDetail(primaryCards[1])"
                            />
                            <MetricCard
                                v-if="primaryCards[2]"
                                :label="primaryCards[2].label"
                                :value="primaryCards[2].value"
                                :icon="primaryCards[2].icon"
                                :variant="primaryCards[2].variant"
                                :trend="'down'"
                                :trend-percent="-0.5"
                                :sparkline-data="[10.5, 10.2, 10, 10.1, 9.8, 9.5, 10]"
                                comparison="vs. mes anterior"
                                class="flex-1"
                                :class="{ 'ring-2 ring-(--th-input-focus-border) ring-offset-2 ring-offset-(--th-ring-offset)': selectedCard?.id === primaryCards[2]?.id }"
                                @click="primaryCards[2] && openDetail(primaryCards[2])"
                            />
                        </div>
                    </div>
                </section>

                <!-- ── Otras métricas: bento de tiles compactos ── -->
                <section>
                    <p class="mb-4 text-xs font-semibold uppercase tracking-widest text-(--th-group-label)">
                        Otras métricas
                    </p>

                    <div ref="gridRef" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                        <button
                            v-for="card in secondaryCards"
                            :key="card.id"
                            type="button"
                            class="secondary-metric-tile group relative flex flex-col overflow-hidden rounded-2xl p-4 text-left transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-(--p-focus-ring-color) focus:ring-offset-2 focus:ring-offset-(--th-ring-offset)"
                            :class="{ 'ring-2 ring-(--th-input-focus-border) ring-offset-2 ring-offset-(--th-ring-offset)': selectedCard?.id === card.id }"
                            @click="openDetail(card)"
                        >
                            <!-- Glass layer -->
                            <span
                                class="absolute inset-0 rounded-2xl border border-white/20 bg-white/65 shadow-md backdrop-blur-xl transition-all duration-300 dark:border-white/10 dark:bg-white/5 dark:shadow-none group-hover:bg-white/80 group-hover:shadow-lg dark:group-hover:bg-white/8"
                                aria-hidden="true"
                            />
                            <!-- Colored dot indicator (top-right) -->
                            <span
                                class="absolute right-3 top-3 h-2 w-2 rounded-full transition-transform duration-300 group-hover:scale-125"
                                :class="variantDot[card.variant] ?? 'bg-rose-400'"
                                aria-hidden="true"
                            />

                            <div class="relative z-10 flex flex-col gap-2.5">
                                <!-- Icon -->
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-105"
                                    :class="variantStyles[card.variant]?.iconBg ?? 'bg-rose-500/15'"
                                >
                                    <Icon
                                        :icon="card.icon"
                                        class="h-4 w-4"
                                        :class="variantStyles[card.variant]?.iconColor ?? 'text-rose-600'"
                                        aria-hidden="true"
                                    />
                                </div>
                                <!-- Value -->
                                <p class="text-xl font-bold tabular-nums tracking-tight text-(--th-text-primary)">
                                    {{ card.value }}
                                </p>
                                <!-- Label -->
                                <p class="line-clamp-2 text-xs font-medium leading-snug text-(--th-text-secondary)">
                                    {{ card.label }}
                                </p>
                            </div>

                            <!-- Hover arrow indicator -->
                            <div class="relative z-10 mt-3 flex items-center gap-1 text-(--th-item-active-color) opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                <span class="text-xs font-semibold">Ver detalle</span>
                                <Icon icon="heroicons:arrow-right" class="h-3 w-3 transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
                            </div>
                        </button>
                    </div>
                </section>

                <!-- ── Tabla de detalle (inline) ── -->
                <section
                    class="detail-table-section overflow-hidden rounded-2xl border border-(--th-border) bg-(--th-input-bg) shadow-sm"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-(--th-border) bg-(--th-input-bg) px-4 py-3"
                    >
                        <h3 class="text-sm font-semibold text-(--th-text-primary)">
                            {{ selectedCard ? `Detalle: ${selectedCard.label}` : 'Detalle de la métrica' }}
                        </h3>
                        <button
                            v-if="selectedCard"
                            type="button"
                            class="text-xs font-medium text-(--th-text-muted) transition-colors hover:text-(--th-item-active-color)"
                            @click="selectedCard = null"
                        >
                            Limpiar selección
                        </button>
                    </div>
                    <div
                        v-if="!selectedCard"
                        class="flex flex-col items-center justify-center gap-2 px-4 py-12 text-center"
                    >
                        <Icon icon="heroicons:table-cells" class="h-10 w-10 text-(--th-text-muted)" aria-hidden="true" />
                        <p class="text-sm text-(--th-text-muted)">
                            Selecciona una métrica arriba para ver el detalle en la tabla.
                        </p>
                    </div>
                    <div v-else class="relative min-h-[200px] overflow-x-auto px-4 pb-4">
                        <DetailMetricTable
                            v-show="!detailLoading"
                            :rows="detailRows as Record<string, unknown>[]"
                            :columns="detailTableColumns"
                            search-placeholder="Buscar en concepto, valor, %..."
                            :export-label="selectedCard?.label"
                        />
                    </div>
                </section>

                <p class="text-xs text-(--th-text-muted)">
                    Clic en cualquier métrica para ver el detalle en la tabla debajo.
                </p>
            </template>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DetailMetricTable from '@/Components/Dashboards/DetailMetricTable.vue'
import type { DetailMetricColumn } from '@/Components/Dashboards/DetailMetricTable.vue'
import { useGlobalLoading } from '@/composables/useGlobalLoading'
import AppSkeleton from '@/Components/AppSkeleton.vue'
import DashboardHeader from '@/Components/Dashboards/DashboardHeader.vue'
import MetricCard from '@/Components/Dashboards/MetricCard.vue'
import CustomPicker from '@/Components/Tables/Pickers/CustomPicker.vue'
import { Icon } from '@iconify/vue'
import { autoAnimate } from '@formkit/auto-animate'
import { useUsers } from './composables/useUsers'
import type { DetailTableRow } from './composables/useUsers'

// Variant styles for compact tiles
const variantStyles: Record<string, { iconBg: string; iconColor: string }> = {
    blue: { iconBg: 'bg-[#0b4261]/15 dark:bg-[#0d5a7a]/25', iconColor: 'text-[#0b4261] dark:text-[#5bb56a]' },
    green: { iconBg: 'bg-emerald-500/15 dark:bg-emerald-400/20', iconColor: 'text-emerald-600 dark:text-emerald-400' },
    red: { iconBg: 'bg-rose-500/15 dark:bg-rose-400/20', iconColor: 'text-rose-600 dark:text-rose-400' },
    violet: { iconBg: 'bg-violet-500/15 dark:bg-violet-400/20', iconColor: 'text-violet-600 dark:text-violet-400' },
}

const variantDot: Record<string, string> = {
    blue: 'bg-[#0b4261] dark:bg-[#5bb56a]',
    green: 'bg-emerald-500 dark:bg-emerald-400',
    red: 'bg-rose-500 dark:bg-rose-400',
    violet: 'bg-violet-500 dark:bg-violet-400',
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
const subtitle = 'Indicadores clave de usuarios por estatus. Clic en una métrica para ver el detalle en la tabla.'

const breadcrumbs = computed(() => [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Dashboards de métricas', href: '/dashboards' },
    { name: title.value },
])

const { users, isLoading, getIndicadores, detailsByCard } = useUsers()

function apiCardToItem(raw: { id: string; label: string; value: number; variant: string; iconKey: string }): CardItem {
    return {
        id: raw.id,
        label: raw.label,
        value: String(raw.value),
        icon: raw.iconKey,
        variant: raw.variant as CardItem['variant'],
    }
}

const primaryCards = computed<CardItem[]>(() => {
    const arr = users.value?.primary
    return Array.isArray(arr) ? arr.map(apiCardToItem) : []
})

const secondaryCards = computed<CardItem[]>(() => {
    const arr = users.value?.secondary
    return Array.isArray(arr) ? arr.map(apiCardToItem) : []
})

// Hero card trend direction
const heroTrend = computed<'up' | 'down' | 'neutral'>(() =>
    primaryCards.value[0]?.variant === 'red' ? 'down' : 'up'
)

// Decorative sparkline for hero card (last 7 days trend)
const heroSparkline = [128000, 131000, 132000, 130000, 134000, 138000, 142000]

// Header stat chips — resumen rápido bajo el título
const headerChips = computed(() => {
    const total = primaryCards.value[0]?.value ?? '–'
    const activos = primaryCards.value[1]?.value ?? '–'
    return [
        { label: 'Total', value: total, dot: 'bg-[#0b4261] dark:bg-[#5bb56a]' },
        { label: 'Activos', value: activos, dot: 'bg-emerald-500 dark:bg-emerald-400' },
        { label: 'Sync', value: 'Hoy 08:00', dot: 'bg-amber-500 dark:bg-amber-400' },
    ]
})

const gridRef = ref<HTMLElement | null>(null)
const selectedCard = ref<CardItem | null>(null)
const detailLoading = ref(false)
const { show: showGlobalLoading, hide: hideGlobalLoading } = useGlobalLoading()

function openDetail(card: CardItem) {
    selectedCard.value = card
}

const detailRows = computed<DetailTableRow[]>(() => {
    if (!selectedCard.value) return []
    return detailsByCard[selectedCard.value.id] ?? []
})

const hasPorcentaje = computed(() => detailRows.value.some((r) => r.porcentaje != null))
const hasActualizado = computed(() => detailRows.value.some((r) => r.actualizado != null))

const detailTableColumns = computed<DetailMetricColumn[]>(() => {
    const cols: DetailMetricColumn[] = [
        { key: 'concepto', header: 'Concepto', sortable: true, class: 'font-medium' },
        {
            key: 'valor',
            header: 'Valor',
            sortable: true,
            numeric: true,
            format: (v, row) => formatValor(v as number | string) + (row.unidad ? ` ${row.unidad}` : ''),
            exportFormat: (v, row) => formatValor(v as number | string) + (row.unidad ? ` ${row.unidad}` : ''),
        },
    ]
    if (hasPorcentaje.value) cols.push({ key: 'porcentaje', header: '%', sortable: true })
    if (hasActualizado.value) cols.push({ key: 'actualizado', header: 'Última actualización', sortable: true, class: 'text-(--th-text-secondary)' })
    return cols
})

function formatValor(v: number | string): string {
    if (typeof v === 'number') return v.toLocaleString('es')
    return String(v)
}

function loadData() {
    const date = new Date().toISOString().slice(0, 10)
    getIndicadores(date, unit.value ?? undefined)
}

onMounted(() => {
    loadData()
    if (gridRef.value) autoAnimate(gridRef.value)
})

watch(users, (u) => {
    if (u?.primary?.length && !selectedCard.value) selectedCard.value = apiCardToItem(u.primary[0])
}, { immediate: true })

watch(selectedCard, (card) => {
    if (card) {
        showGlobalLoading('Cargando detalle…')
        detailLoading.value = true
        nextTick(() => {
            setTimeout(() => {
                detailLoading.value = false
                hideGlobalLoading()
            }, 280)
        })
    }
})

watch(unit, () => { loadData() })
</script>
