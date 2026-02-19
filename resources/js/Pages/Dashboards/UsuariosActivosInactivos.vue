<template>
    <AdminLayout
        :breadcrumbs="[
            { name: 'Dashboard', href: '/dashboard' },
            { name: 'Dashboards de métricas', href: '/dashboards' },
            { name: 'Usuarios activos / inactivos' },
        ]"
    >
        <div
            class="space-y-8 rounded-3xl bg-slate-50 p-6 sm:p-8"
            style="background-image: radial-gradient(circle at 1px 1px, rgba(0,0,0,.06) 1px, transparent 0); background-size: 24px 24px;"
        >
            <DashboardHeader
                title="Visualizaciones - Usuarios Activos vs Inactivos"
                subtitle="Distribución de población (clic en 'Usuarios totales')"
                :icon="UserGroupIcon"
            >
                <template #actions>
                    <CustomPicker
                        initial-preset="lastMonth"
                        select-disabled
                        class="rounded-xl border border-slate-200 bg-white shadow-sm"
                    />
                </template>
            </DashboardHeader>

            <!-- Tarjetas KPI -->
            <div ref="cardsRef" class="grid gap-6 sm:grid-cols-3">
                <MetricCard
                    label="Usuarios totales"
                    value="140,000"
                    :icon="GlobeAltIcon"
                    variant="blue"
                />
                <MetricCard
                    label="Usuarios activos"
                    value="132,000"
                    :icon="CheckCircleIcon"
                    variant="green"
                />
                <MetricCard
                    label="Usuarios inactivos"
                    value="8,000"
                    :icon="XCircleIcon"
                    variant="red"
                />
            </div>

            <!-- Gráficos -->
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-2">
                        <UserGroupIcon class="h-5 w-5 text-indigo-600" />
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700">
                            Usuarios activos / inactivos
                        </h3>
                    </div>
                    <div class="h-80">
                        <PieChart
                            :data="[
                                { name: 'Activos', value: 132000 },
                                { name: 'Inactivos', value: 8000 },
                            ]"
                            legend-position="bottom"
                        />
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-700">
                        Estatus de usuario
                    </h3>
                    <div class="h-80">
                        <HorizontalBarChart
                            title=""
                            :categories="['PROVISIONED', 'PASSWORD EXPIRED', 'LOCKED OUT', 'SUSPENDED']"
                            :series="[
                                { name: 'PROVISIONED', data: [122, 0, 0, 0] },
                                { name: 'PASSWORD EXPIRED', data: [0, 20, 0, 0] },
                                { name: 'LOCKED OUT', data: [0, 0, 50, 0] },
                                { name: 'SUSPENDED', data: [0, 0, 0, 30] },
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DashboardHeader from '@/Components/Dashboards/DashboardHeader.vue'
import MetricCard from '@/Components/Dashboards/MetricCard.vue'
import CustomPicker from '@/Components/Tables/Pickers/CustomPicker.vue'
import PieChart from '@/Components/Charts/PieChart.vue'
import HorizontalBarChart from '@/Components/Charts/HorizontalBarChart.vue'
import {
    GlobeAltIcon,
    CheckCircleIcon,
    XCircleIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/outline'
import { autoAnimate } from '@formkit/auto-animate'

const cardsRef = ref<HTMLElement | null>(null)

onMounted(() => {
    if (cardsRef.value) autoAnimate(cardsRef.value)
})
</script>
