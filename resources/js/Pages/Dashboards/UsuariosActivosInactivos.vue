<template>
    <AdminLayout
        :breadcrumbs="[
            { name: 'Dashboard', href: '/dashboard' },
            { name: 'Dashboards de métricas', href: '/dashboards' },
            { name: 'Usuarios activos / inactivos' },
        ]"
    >
        <div class="space-y-8">
            <DashboardHeader
                title="Usuarios activos vs inactivos"
                subtitle="Distribución y estatus por tipo de cuenta"
                icon="heroicons:user-group"
            >
                <template #actions>
                    <CustomPicker
                        initial-preset="lastMonth"
                        select-disabled
                        class="rounded-xl border border-(--th-border) bg-(--th-input-bg) shadow-sm"
                    />
                </template>
            </DashboardHeader>

            <!-- Insight en una línea (data storytelling) -->
            <div class="rounded-xl border border-(--th-border) bg-(--th-input-bg)/80 px-4 py-3 text-sm text-(--th-text-secondary) backdrop-blur-sm">
                <span class="font-medium text-(--th-text-primary)">Resumen:</span>
                El 94% de los usuarios están activos; 6% inactivos. La mayoría en estatus PROVISIONED.
            </div>

            <!-- KPIs con contexto -->
            <div ref="cardsRef" class="grid gap-4 sm:grid-cols-3">
                <MetricCard
                    label="Usuarios totales"
                    value="140,000"
                    icon="heroicons:globe-alt"
                    variant="violet"
                    trend="up"
                    :trend-percent="2.4"
                    :sparkline-data="[128, 131, 132, 130, 134, 138, 140]"
                    comparison="vs. mes anterior"
                />
                <MetricCard
                    label="Usuarios activos"
                    value="132,000"
                    icon="heroicons:check-circle"
                    variant="green"
                    trend="up"
                    :trend-percent="1.8"
                    :sparkline-data="[126, 128, 129, 130, 131, 132, 132]"
                    comparison="vs. mes anterior"
                />
                <MetricCard
                    label="Usuarios inactivos"
                    value="8,000"
                    icon="heroicons:x-circle"
                    variant="red"
                    trend="down"
                    :trend-percent="-0.5"
                    :sparkline-data="[9, 8.5, 8.2, 8.1, 8.2, 8, 8]"
                    comparison="vs. mes anterior"
                />
            </div>

            <!-- Gráficos (F-pattern: tendencias en el medio) -->
            <div class="grid gap-6 lg:grid-cols-2">
                <ExpandableChart title="Activos / inactivos">
                    <PieChart
                        :data="[
                            { name: 'Activos', value: 132000 },
                            { name: 'Inactivos', value: 8000 },
                        ]"
                        legend-position="bottom"
                        donut
                    />
                </ExpandableChart>

                <ExpandableChart title="Estatus de usuario">
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
                </ExpandableChart>
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
import ExpandableChart from '@/Components/Dashboards/ExpandableChart.vue'
import PieChart from '@/Components/Charts/PieChart.vue'
import HorizontalBarChart from '@/Components/Charts/HorizontalBarChart.vue'
import { autoAnimate } from '@formkit/auto-animate'

const cardsRef = ref<HTMLElement | null>(null)

onMounted(() => {
    if (cardsRef.value) autoAnimate(cardsRef.value)
})
</script>
