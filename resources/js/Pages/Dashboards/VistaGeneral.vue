<template>
    <AdminLayout
        :breadcrumbs="[
            { name: 'Dashboard', href: '/dashboard' },
            { name: 'Dashboards de métricas', href: '/dashboards' },
            { name: 'Vista general' },
        ]"
    >
        <div class="space-y-8">
            <!-- Header + filtro (F-pattern: controles arriba) -->
            <DashboardHeader
                :title="title"
                :subtitle="subtitle"
                :icon="UserGroupIcon"
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

            <!-- Hero KPI: métrica principal (top-left, más destacada) -->
            <div class="grid gap-6 lg:grid-cols-3">
                <button
                    type="button"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-br from-indigo-50 to-white p-6 text-left shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                    @click="openDetail(heroCard)"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                            <component :is="heroCard.icon" class="h-6 w-6" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Métrica principal</p>
                            <p class="text-3xl font-bold tabular-nums text-slate-900">{{ heroCard.value }}</p>
                            <p class="text-sm font-medium text-slate-600">{{ heroCard.label }}</p>
                        </div>
                    </div>
                    <Sparkline v-if="heroCard.sparklineData?.length" :data="heroCard.sparklineData" color="blue" class="mt-4 h-10" />
                    <p class="mt-2 text-xs text-slate-400">{{ heroCard.comparison }}</p>
                    <span class="absolute right-4 top-4 text-xs text-slate-400">Clic para detalle →</span>
                </button>

                <!-- Otros 2 KPIs destacados en la misma fila -->
                <MetricCard
                    v-for="card in primaryCards"
                    :key="card.id"
                    :label="card.label"
                    :value="card.value"
                    :icon="card.icon"
                    :variant="card.variant"
                    :trend="card.trend"
                    :trend-percent="card.trendPercent"
                    :sparkline-data="card.sparklineData"
                    :comparison="card.comparison"
                    @click="openDetail(card)"
                />
            </div>

            <!-- Segunda fila: resto de métricas (F-pattern: detalle abajo) -->
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
                        :trend="card.trend"
                        :trend-percent="card.trendPercent"
                        :comparison="card.comparison"
                        @click="openDetail(card)"
                    />
                </div>
            </section>

            <p class="text-xs text-slate-400">
                Última actualización: datos de referencia · Clic en cualquier tarjeta para ver detalle (gráficas + tabla)
            </p>
        </div>

        <!-- Drill-down: panel lateral al clic -->
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
                            <tr class="border-t border-slate-100"><td class="px-4 py-2 text-slate-600">Total</td><td class="px-4 py-2 text-right font-medium">{{ selectedCard.value }}</td></tr>
                            <tr class="border-t border-slate-100"><td class="px-4 py-2 text-slate-600">vs. período anterior</td><td class="px-4 py-2 text-right">{{ selectedCard.trendPercent != null ? (selectedCard.trendPercent > 0 ? '+' : '') + selectedCard.trendPercent + '%' : '—' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </DetailDrawer>
    </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DashboardHeader from '@/Components/Dashboards/DashboardHeader.vue'
import MetricCard from '@/Components/Dashboards/MetricCard.vue'
import Sparkline from '@/Components/Dashboards/Sparkline.vue'
import DetailDrawer from '@/Components/Dashboards/DetailDrawer.vue'
import CustomPicker from '@/Components/Tables/Pickers/CustomPicker.vue'
import {
    GlobeAltIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    LockOpenIcon,
    UserMinusIcon,
    MinusCircleIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/outline'
import { autoAnimate } from '@formkit/auto-animate'

const title = 'Vista general'
const subtitle = 'Indicadores clave de usuarios. Clic en una tarjeta para ver detalle.'

type CardItem = {
    id: string
    label: string
    value: string
    icon: typeof GlobeAltIcon
    variant: 'blue' | 'green' | 'red'
    trend?: 'up' | 'down' | 'neutral'
    trendPercent?: number | null
    sparklineData?: number[]
    comparison?: string
}

const heroCard: CardItem = {
    id: 'total',
    label: 'Usuarios totales',
    value: '140,000',
    icon: GlobeAltIcon,
    variant: 'blue',
    trend: 'up',
    trendPercent: 2.4,
    sparklineData: [128, 131, 132, 130, 134, 138, 140],
    comparison: 'vs. mes anterior',
}

const primaryCards: CardItem[] = [
    {
        id: 'activos',
        label: 'Usuarios activos',
        value: '132,000',
        icon: CheckCircleIcon,
        variant: 'green',
        trend: 'up',
        trendPercent: 1.8,
        sparklineData: [126, 128, 129, 130, 131, 132, 132],
        comparison: 'vs. mes anterior',
    },
    {
        id: 'inactivos',
        label: 'Usuarios inactivos',
        value: '8,000',
        icon: XCircleIcon,
        variant: 'red',
        trend: 'down',
        trendPercent: -0.5,
        sparklineData: [9, 8.5, 8.2, 8.1, 8.2, 8, 8],
        comparison: 'vs. mes anterior',
    },
]

const secondaryCards: CardItem[] = [
    { id: 'espera', label: 'En espera', value: '8,000', icon: ClockIcon, variant: 'red', trend: 'neutral', trendPercent: 0, comparison: 'vs. mes anterior' },
    { id: 'password', label: 'Password expirado', value: '8,000', icon: LockOpenIcon, variant: 'red', trend: 'up', trendPercent: 0.3, comparison: 'vs. mes anterior' },
    { id: 'suspendidos', label: 'Suspendidos', value: '2,050', icon: UserMinusIcon, variant: 'red', trend: 'down', trendPercent: -1.2, comparison: 'vs. mes anterior' },
    { id: 'desactivados', label: 'Desactivados', value: '1,000', icon: MinusCircleIcon, variant: 'red', trend: 'neutral', trendPercent: null, comparison: 'vs. mes anterior' },
]

const gridRef = ref<HTMLElement | null>(null)
const selectedCard = ref<CardItem | null>(null)

function openDetail(card: CardItem) {
    selectedCard.value = card
}

onMounted(() => {
    if (gridRef.value) autoAnimate(gridRef.value)
})
</script>
