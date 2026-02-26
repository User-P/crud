<template>
    <AdminLayout
        :breadcrumbs="breadcrumbs"
    >
        <div class="space-y-8">
            <DashboardHeader
                :title="title"
                :subtitle="subtitle"
                icon="heroicons:user-group"
            >
                <template #actions>
                    <CustomPicker
                        :initial-range="{ start: '2025-01-01', end: '2025-01-31' }"
                        initial-type="custom"
                        select-disabled
                        class="rounded-xl border border-slate-200 bg-white shadow-sm"
                    />
                </template>
            </DashboardHeader>

            <div v-if="isLoading" class="flex justify-center py-12">
                <p class="text-sm text-slate-500">Cargando indicadores…</p>
            </div>

            <template v-else>
                <!-- Hero KPI -->
                <div class="grid gap-6 lg:grid-cols-3">
                    <button
                        v-if="heroCard"
                        type="button"
                        class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-br from-indigo-50 to-white p-6 text-left shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                        @click="openDetail(heroCard)"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                                <Icon :icon="heroCard.icon" class="h-6 w-6" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">Métrica principal</p>
                                <p class="text-3xl font-bold tabular-nums text-slate-900">{{ heroCard.value }}</p>
                                <p class="text-sm font-medium text-slate-600">{{ heroCard.label }}</p>
                            </div>
                        </div>
                        <span class="absolute right-4 top-4 text-xs text-slate-400">Clic para detalle →</span>
                    </button>

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

                <!-- Otras métricas -->
                <section>
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">
                        Otras métricas
                    </h3>
                    <div ref="gridRef" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <MetricCard
                            v-for="card in secondaryCards"
                            :key="card.id"
                            :label="card.label"
                            :value="card.value"
                            :icon="card.icon"
                            :variant="card.variant"
                            @click="openDetail(card)"
                        />
                    </div>
                </section>

                <p class="text-xs text-slate-400">
                    Clic en cualquier tarjeta para ver detalle (gráficas + tabla)
                </p>
            </template>

        </div>

        <DetailDrawer
            :visible="!!selectedCard"
            :title="selectedCard ? selectedCard.label : ''"
            @close="selectedCard = null"
        >
            <template v-if="selectedCard">
                <p class="mb-4 text-sm text-slate-600">
                    Detalle de <strong>{{ selectedCard.label }}</strong>. Aquí se mostrarían gráficas y tabla según el tipo de métrica.
                </p>
                <div class="mb-4 h-48 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 text-sm">
                    Gráfica de tendencia (placeholder)
                </div>
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-slate-600">Concepto</th>
                                <th class="px-4 py-2 text-right font-medium text-slate-600">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-2 text-slate-600">Total</td>
                                <td class="px-4 py-2 text-right font-medium">{{ selectedCard.value }}</td>
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
