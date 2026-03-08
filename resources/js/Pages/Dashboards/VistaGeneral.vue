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

                <div class="grid gap-5 sm:grid-cols-3">

                    <MetricCard v-for="card in primaryCards" :key="card.id" :label="card.label" :value="card.value"
                        :icon="card.icon" :variant="card.variant" class="transition-shadow"
                        :class="{ 'ring-2 ring-(--th-input-focus-border) ring-offset-2 ring-offset-(--th-ring-offset)': selectedCard?.id === card.id }"
                        @click="openDetail(card)" />
                </div>

                <!-- Otras métricas: lista compacta -->
                <section>
                    <p class="mb-4 text-xs font-semibold uppercase tracking-widest text-(--th-group-label)">
                        Otras métricas
                    </p>
                    <div ref="gridRef"
                        class="  flex flex-col overflow-hidden rounded-2xl border border-(--th-border) bg-(--th-input-bg) shadow-sm">
                        <button v-for="card in secondaryCards" :key="card.id" type="button"
                            class="group flex w-full items-center gap-4 border-b border-(--th-border) px-5 py-4 text-left last:border-b-0 transition-colors hover:bg-(--th-item-hover-bg) focus:outline-none focus:ring-2 focus:ring-inset focus:ring-(--p-focus-ring-color)"
                            :class="{ 'bg-(--th-item-hover-bg)': selectedCard?.id === card.id }"
                            @click="openDetail(card)">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-transform group-hover:scale-105"
                                :class="variantStyles[card.variant].iconBg">
                                <Icon :icon="card.icon" class="h-5 w-5" :class="variantStyles[card.variant].iconColor"
                                    aria-hidden="true" />
                            </div>
                            <span class="min-w-0 flex-1 font-medium text-(--th-text-primary)">{{ card.label }}</span>
                            <span class="tabular-nums font-semibold text-(--th-text-primary)">{{ card.value }}</span>
                            <Icon icon="heroicons:chevron-right"
                                class="h-5 w-5 shrink-0 text-(--th-text-muted) group-hover:text-(--th-item-active-color)"
                                aria-hidden="true" />
                        </button>
                    </div>
                </section>

                <!-- Tabla de detalle (inline, sin panel lateral) -->
                <section
                    class="detail-table-section rounded-2xl border border-(--th-border) bg-(--th-input-bg) overflow-hidden shadow-sm">
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-(--th-border) bg-(--th-input-bg) px-4 py-3">
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
                        <p class="text-sm text-(--th-text-muted)">Selecciona una métrica arriba para ver el detalle en
                            la tabla.</p>
                    </div>
                    <div v-else class="overflow-x-auto px-4 pb-4">
                        <DetailMetricTable
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
import { computed, onMounted, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DetailMetricTable from '@/Components/Dashboards/DetailMetricTable.vue'
import type { DetailMetricColumn } from '@/Components/Dashboards/DetailMetricTable.vue'
import DashboardHeader from '@/Components/Dashboards/DashboardHeader.vue'
import MetricCard from '@/Components/Dashboards/MetricCard.vue'
import CustomPicker from '@/Components/Tables/Pickers/CustomPicker.vue'
import { Icon } from '@iconify/vue'
import { autoAnimate } from '@formkit/auto-animate'
import { useUsers } from './composables/useUsers'
import type { DetailTableRow } from './composables/useUsers'

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

const gridRef = ref<HTMLElement | null>(null)
const selectedCard = ref<CardItem | null>(null)

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
    if (u?.hero && !selectedCard.value) selectedCard.value = apiCardToItem(u.hero)
}, { immediate: true })

watch(unit, () => { loadData() })
</script>
