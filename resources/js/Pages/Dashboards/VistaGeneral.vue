<template>
    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-10" :style="unitStyle">
            <DashboardHeader :title="title" :subtitle="subtitle" icon="heroicons:user-group">
                <!-- Stats chips: datos de resumen rápido bajo el título -->
                <template #stats>
                    <span
                        v-for="chip in headerChips"
                        :key="chip.label"
                        class="glass-chip inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-[var(--th-border)]"
                    >
                        <span class="h-1.5 w-1.5 rounded-full" :class="chip.dot" aria-hidden="true" />
                        <span class="text-[color:var(--th-text-secondary)]">{{ chip.label }}:</span>
                        <span class="font-semibold text-[color:var(--th-text-primary)]">{{ chip.value }}</span>
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
                        <!-- Hero card: spans 2 rows, with mini donut + live tooltip -->
                        <MetricCard
                            v-if="primaryCards[0]"
                            :featured="true"
                            :live="true"
                            last-sync="Hoy 08:00"
                            :label="primaryCards[0].label"
                            :value="primaryCards[0].value"
                            :icon="primaryCards[0].icon"
                            :variant="primaryCards[0].variant"
                            :trend="heroTrend"
                            :trend-percent="2.4"
                            :mini-chart="heroMiniChart"
                            :sparkline-data="heroSparklineData"
                            comparison="vs. mes anterior"
                            class="sm:row-span-2 h-full"
                            :class="{ 'ring-2 ring-[var(--th-input-focus-border)] ring-offset-2 ring-offset-[var(--th-ring-offset)]': selectedCard?.id === primaryCards[0]?.id }"
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
                                :class="{ 'ring-2 ring-[var(--th-input-focus-border)] ring-offset-2 ring-offset-[var(--th-ring-offset)]': selectedCard?.id === primaryCards[1]?.id }"
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
                                :class="{ 'ring-2 ring-[var(--th-input-focus-border)] ring-offset-2 ring-offset-[var(--th-ring-offset)]': selectedCard?.id === primaryCards[2]?.id }"
                                @click="primaryCards[2] && openDetail(primaryCards[2])"
                            />
                        </div>
                    </div>
                </section>

                <!-- ── Otras métricas: bento de tiles con popover rico + drag-and-drop ── -->
                <section>
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-widest text-[color:var(--th-group-label)]">
                            Otras métricas
                        </p>
                        <span class="text-xs text-[color:var(--th-text-muted)]">
                            <Icon icon="heroicons:arrows-up-down" class="mr-1 inline h-3.5 w-3.5" aria-hidden="true" />
                            Arrastra para reordenar
                        </span>
                    </div>

                    <!--
                        VueDraggable renders the grid container itself.
                        handle=".drag-handle" targets a div OUTSIDE the <button>
                        so pointer events reach SortableJS without interference.
                    -->
                    <VueDraggable
                        v-model="orderedSecondaryCards"
                        handle=".drag-handle"
                        :animation="200"
                        ghost-class="sortable-ghost"
                        chosen-class="sortable-chosen"
                        class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5"
                    >
                        <div
                            v-for="card in orderedSecondaryCards"
                            :key="card.id"
                            class="relative group/tile"
                        >
                            <!-- ── Top-right: handle de arrastre + gota (variante), en una sola fila ── -->
                            <div
                                class="absolute right-2 top-2 z-50 flex items-center gap-2"
                            >
                                <div
                                    class="drag-handle flex h-6 w-6 cursor-grab select-none items-center justify-center rounded-md text-[color:var(--th-text-muted)] opacity-50 transition-opacity duration-200 hover:opacity-90 group-hover/tile:opacity-70 active:cursor-grabbing"
                                    aria-label="Arrastrar para reordenar"
                                    title="Arrastrar para reordenar"
                                >
                                    <Icon icon="heroicons:bars-3" class="h-3.5 w-3.5" aria-hidden="true" />
                                </div>
                                <span
                                    class="h-2 w-2 rounded-full transition-transform duration-300 group-hover/tile:scale-125"
                                    :class="variantDot[card.variant] ?? 'bg-rose-400'"
                                    aria-hidden="true"
                                />
                            </div>

                            <!-- ── Rich hover popover (above the tile) ── -->
                            <div
                                class="pointer-events-none absolute bottom-full left-0 z-40 mb-2 w-48 min-w-full opacity-0 transition-all duration-200 group-hover/tile:opacity-100 group-hover/tile:-translate-y-0.5"
                                aria-hidden="true"
                            >
                                <div
                                    class="relative overflow-hidden rounded-2xl border border-white/25 shadow-2xl backdrop-blur-2xl dark:border-white/10"
                                    style="background: rgba(255,255,255,0.95)"
                                >
                                    <div class="flex items-center gap-2 border-b border-[var(--th-border)] px-3 py-2.5">
                                        <span class="h-1.5 w-1.5 rounded-full" :class="variantDot[card.variant] ?? 'bg-rose-400'" />
                                        <span class="text-xs font-semibold leading-tight text-[color:var(--th-text-primary)]">{{ card.label }}</span>
                                    </div>
                                    <div v-if="tileSparkline(card.id).length >= 2" class="h-10 px-3 pt-2">
                                        <Sparkline
                                            :data="tileSparkline(card.id)"
                                            :color="card.variant === 'red' ? 'red' : card.variant === 'green' ? 'green' : 'blue'"
                                            :filled="true"
                                        />
                                    </div>
                                    <ul class="divide-y divide-[var(--th-border)] px-3 py-1.5">
                                        <li
                                            v-for="row in tilePreviewRows(card.id)"
                                            :key="row.concepto"
                                            class="flex items-center justify-between gap-2 py-1.5 text-xs"
                                        >
                                            <span class="truncate text-[color:var(--th-text-secondary)]">{{ row.concepto }}</span>
                                            <span class="tabular-nums font-semibold text-[color:var(--th-text-primary)]">
                                                {{ typeof row.valor === 'number' ? row.valor.toLocaleString('es') : row.valor }}
                                            </span>
                                        </li>
                                        <li v-if="!tilePreviewRows(card.id).length" class="py-2 text-center text-xs text-[color:var(--th-text-muted)]">
                                            Sin datos disponibles
                                        </li>
                                    </ul>
                                    <!-- Caret pointing down -->
                                    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-full">
                                        <div class="border-4 border-transparent" style="border-top-color: rgba(255,255,255,0.95)" />
                                    </div>
                                </div>
                            </div>

                            <!-- ── Tile button ── -->
                            <button
                                type="button"
                                class="secondary-metric-tile group relative flex w-full flex-col overflow-hidden rounded-2xl p-4 pl-5 text-left transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[var(--p-focus-ring-color)] focus:ring-offset-2 focus:ring-offset-[var(--th-ring-offset)]"
                                :class="{ 'ring-2 ring-[var(--th-input-focus-border)] ring-offset-2 ring-offset-[var(--th-ring-offset)]': selectedCard?.id === card.id }"
                                @click="openDetail(card)"
                            >
                                <!-- Glass layer -->
                                <span
                                    class="absolute inset-0 rounded-2xl border border-white/20 bg-white/65 shadow-md backdrop-blur-xl transition-all duration-300 dark:border-white/10 dark:bg-white/5 dark:shadow-none group-hover:bg-white/80 group-hover:shadow-lg dark:group-hover:bg-white/8"
                                    aria-hidden="true"
                                />
                                <!-- Barra lateral de variante (como MetricCard) -->
                                <span
                                    class="absolute left-0 top-4 bottom-4 w-1 rounded-full transition-all duration-300 group-hover:w-1.5"
                                    :class="variantDot[card.variant] ?? 'bg-rose-400'"
                                    aria-hidden="true"
                                />

                                <div class="relative z-10 flex flex-col gap-2.5 pt-1">
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
                                    <p class="text-xl font-bold tabular-nums tracking-tight text-[color:var(--th-text-primary)]">{{ card.value }}</p>
                                    <p class="line-clamp-2 text-xs font-medium leading-snug text-[color:var(--th-text-secondary)]">{{ card.label }}</p>
                                </div>

                                <div class="relative z-10 mt-3 flex items-center gap-1 text-[color:var(--th-item-active-color)] opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                    <span class="text-xs font-semibold">Ver detalle</span>
                                    <Icon icon="heroicons:arrow-right" class="h-3 w-3 transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
                                </div>
                            </button>
                        </div>
                    </VueDraggable>
                </section>

                <!-- ── Tabla de detalle (inline) ── -->
                <section
                    class="detail-table-section overflow-hidden rounded-2xl border border-[var(--th-border)] bg-[var(--th-input-bg)] shadow-sm"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-[var(--th-border)] bg-[var(--th-input-bg)] px-4 py-3"
                    >
                        <h3 class="text-sm font-semibold text-[color:var(--th-text-primary)]">
                            {{ selectedCard ? `Detalle: ${selectedCard.label}` : 'Detalle de la métrica' }}
                        </h3>
                        <button
                            v-if="selectedCard"
                            type="button"
                            class="text-xs font-medium text-[color:var(--th-text-muted)] transition-colors hover:text-[color:var(--th-item-active-color)]"
                            @click="selectedCard = null"
                        >
                            Limpiar selección
                        </button>
                    </div>
                    <!-- ── Illustrated empty state ── -->
                    <div
                        v-if="!selectedCard"
                        class="flex flex-col items-center justify-center gap-4 px-4 py-12 text-center"
                    >
                        <!-- Minimalist inline SVG illustration: data table + cursor -->
                        <svg
                            viewBox="0 0 160 100"
                            class="h-24 w-36 text-[color:var(--th-text-muted)]"
                            aria-hidden="true"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <!-- Table card background -->
                            <rect x="20" y="12" width="120" height="76" rx="8" fill="currentColor" opacity="0.06" />
                            <!-- Header row -->
                            <rect x="28" y="20" width="104" height="14" rx="4" fill="currentColor" opacity="0.12" />
                            <!-- Header cell separators -->
                            <rect x="28" y="20" width="48" height="14" rx="4" fill="currentColor" opacity="0.08" />
                            <rect x="86" y="20" width="30" height="14" rx="4" fill="currentColor" opacity="0.08" />
                            <!-- Data rows -->
                            <rect x="28" y="40" width="60" height="6" rx="2" fill="currentColor" opacity="0.10" />
                            <rect x="96" y="40" width="36" height="6" rx="2" fill="currentColor" opacity="0.14" />
                            <rect x="28" y="52" width="50" height="6" rx="2" fill="currentColor" opacity="0.08" />
                            <rect x="96" y="52" width="28" height="6" rx="2" fill="currentColor" opacity="0.10" />
                            <rect x="28" y="64" width="55" height="6" rx="2" fill="currentColor" opacity="0.06" />
                            <rect x="96" y="64" width="32" height="6" rx="2" fill="currentColor" opacity="0.08" />
                            <!-- Row dividers -->
                            <line x1="28" y1="37" x2="132" y2="37" stroke="currentColor" stroke-width="0.8" opacity="0.15" />
                            <line x1="28" y1="49" x2="132" y2="49" stroke="currentColor" stroke-width="0.8" opacity="0.10" />
                            <line x1="28" y1="61" x2="132" y2="61" stroke="currentColor" stroke-width="0.8" opacity="0.08" />
                            <!-- Animated cursor pointer -->
                            <g opacity="0.35">
                                <path d="M108 56 L108 74 L113 69 L116 76 L119 74.5 L116 68 L122 68 Z" fill="currentColor" />
                                <path d="M108 56 L108 74 L113 69 L116 76 L119 74.5 L116 68 L122 68 Z" fill="currentColor" opacity="0.3" stroke="white" stroke-width="1.5" stroke-linejoin="round" />
                            </g>
                            <!-- Dotted selection ring -->
                            <circle cx="80" cy="50" r="38" stroke="currentColor" stroke-width="1" stroke-dasharray="5 4" opacity="0.12" />
                        </svg>

                        <div class="space-y-1">
                            <p class="text-sm font-medium text-[color:var(--th-text-secondary)]">
                                Ninguna métrica seleccionada
                            </p>
                            <p class="text-xs text-[color:var(--th-text-muted)]">
                                Haz clic en cualquier tarjeta o tile para ver el desglose en la tabla.
                            </p>
                        </div>
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

                <p class="text-xs text-[color:var(--th-text-muted)]">
                    Clic en cualquier métrica para ver el detalle en la tabla debajo.
                </p>
            </template>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { VueDraggable } from 'vue-draggable-plus'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DetailMetricTable from '@/Components/Dashboards/DetailMetricTable.vue'
import type { DetailMetricColumn } from '@/Components/Dashboards/DetailMetricTable.vue'
import { useGlobalLoading } from '@/composables/useGlobalLoading'
import AppSkeleton from '@/Components/AppSkeleton.vue'
import DashboardHeader from '@/Components/Dashboards/DashboardHeader.vue'
import MetricCard from '@/Components/Dashboards/MetricCard.vue'
import Sparkline from '@/Components/Dashboards/Sparkline.vue'
import CustomPicker from '@/Components/Tables/Pickers/CustomPicker.vue'
import { Icon } from '@iconify/vue'
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

// ── Draggable secondary grid ────────────────────────────────────────────────
// orderedSecondaryCards is a local ref so drag reorders persist in this session
const orderedSecondaryCards = ref<CardItem[]>([])
watch(secondaryCards, (cards) => { orderedSecondaryCards.value = [...cards] }, { immediate: true })

// ── Unit-level colour theming ────────────────────────────────────────────────
// Changes --th-item-active-color and related vars per business unit
const UNIT_THEMES: Record<string, Record<string, string>> = {
    EKT:         { '--th-item-active-color': '#0b4261', '--th-item-active-glow': 'rgba(11,66,97,0.25)',   '--p-primary-color': '#0b4261' },
    TPE:         { '--th-item-active-color': '#5bb56a', '--th-item-active-glow': 'rgba(91,181,106,0.25)', '--p-primary-color': '#5bb56a' },
    TVA:         { '--th-item-active-color': '#d97706', '--th-item-active-glow': 'rgba(217,119,6,0.25)',  '--p-primary-color': '#d97706' },
    BACK_OFFICE: { '--th-item-active-color': '#7c3aed', '--th-item-active-glow': 'rgba(124,58,237,0.25)','--p-primary-color': '#7c3aed' },
}
const unitStyle = computed<Record<string, string>>(() =>
    unit.value ? (UNIT_THEMES[unit.value.toUpperCase()] ?? {}) : {}
)

// Helper: sparkline from detail rows for tile popovers (uses valor field)
function tileSparkline(cardId: string): number[] {
    const rows = detailsByCard[cardId] ?? []
    return rows.map((r) => (typeof r.valor === 'number' ? r.valor : 0)).filter(Boolean)
}
// Top 3 preview rows for tile popover
function tilePreviewRows(cardId: string): DetailTableRow[] {
    return (detailsByCard[cardId] ?? []).slice(0, 3)
}

// Hero card trend direction
const heroTrend = computed<'up' | 'down' | 'neutral'>(() =>
    primaryCards.value[0]?.variant === 'red' ? 'down' : 'up'
)

// Mini donut chart for hero card: shows activos vs inactivos breakdown
const heroMiniChart = computed(() => {
    const activos = Number(String(primaryCards.value[1]?.value ?? '132000').replace(/[,.\s]/g, '')) || 132000
    const inactivos = Number(String(primaryCards.value[2]?.value ?? '10000').replace(/[,.\s]/g, '')) || 10000
    return {
        type: 'donut' as const,
        data: [
            { name: 'Activos', value: activos },
            { name: 'Inactivos', value: inactivos },
        ],
        colors: ['#5bb56a', '#ef4444'],
    }
})

/** Tendencia últimos 7 días para la card principal (rellena más la card) */
const heroSparklineData = [138, 139, 140, 139, 141, 142, 142]

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
    if (hasActualizado.value) cols.push({ key: 'actualizado', header: 'Última actualización', sortable: true, class: 'text-[color:var(--th-text-secondary)]' })
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

onMounted(() => { loadData() })

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
