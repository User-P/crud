<template>
    <AdminLayout
        :breadcrumbs="[
            { name: 'Dashboard', href: '/dashboard' },
            { name: 'Dashboards de métricas', href: '/dashboards' },
            { name: 'Vista general' },
        ]"
    >
        <div
            class="space-y-8 rounded-3xl bg-slate-50 p-6 sm:p-8"
            style="background-image: radial-gradient(circle at 1px 1px, rgba(0,0,0,.06) 1px, transparent 0); background-size: 24px 24px;"
        >
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

            <div ref="gridRef" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <MetricCard
                    v-for="card in metricCards"
                    :key="card.id"
                    :label="card.label"
                    :value="card.value"
                    :icon="card.icon"
                    :variant="card.variant"
                    @click="onCardClick(card)"
                />
            </div>

            <p class="text-sm text-slate-500">
                Interacción: clic en cualquier tarjeta → abre vista de detalle (gráficas + tabla)
            </p>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DashboardHeader from '@/Components/Dashboards/DashboardHeader.vue'
import MetricCard from '@/Components/Dashboards/MetricCard.vue'
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

const title = 'Dashboard Producción – Vista General (indicadores)'
const subtitle = 'Plantilla visual de entrada (tarjetas KPI clicables)'

const metricCards = [
    { id: 'total', label: 'Usuarios totales', value: '140,000', icon: GlobeAltIcon, variant: 'blue' as const },
    { id: 'activos', label: 'Usuarios activos', value: '132,000', icon: CheckCircleIcon, variant: 'blue' as const },
    { id: 'inactivos', label: 'Usuarios inactivos', value: '8,000', icon: XCircleIcon, variant: 'blue' as const },
    { id: 'espera', label: 'Usuarios en espera', value: '8,000', icon: ClockIcon, variant: 'red' as const },
    { id: 'password', label: 'Usuarios con password expirado', value: '8,000', icon: LockOpenIcon, variant: 'red' as const },
    { id: 'suspendidos', label: 'Usuarios suspendidos', value: '2,050', icon: UserMinusIcon, variant: 'red' as const },
    { id: 'desactivados', label: 'Usuarios desactivados', value: '1,000', icon: MinusCircleIcon, variant: 'red' as const },
]

const gridRef = ref<HTMLElement | null>(null)

onMounted(() => {
    if (gridRef.value) autoAnimate(gridRef.value)
})

const onCardClick = (card: (typeof metricCards)[0]) => {
    console.log('Clic en tarjeta:', card.id)
    // TODO: navegar a vista detalle o abrir modal
}
</script>
