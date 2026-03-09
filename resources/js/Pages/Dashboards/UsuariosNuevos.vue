<template>
    <AdminLayout :breadcrumbs="[
        { name: 'Dashboard', href: '/dashboard' },
        { name: 'Dashboards de métricas', href: '/dashboards' },
        { name: 'Usuarios nuevos' },
    ]">
        <div class="space-y-8">
            <DashboardHeader title="Usuarios nuevos" subtitle="Altas de usuarios y tendencias"
                icon="heroicons:user-plus">
                <template #actions>
                    <CustomPicker initial-preset="lastMonth" select-disabled class="  rounded-xl shadow-sm" />
                </template>
            </DashboardHeader>

            <!-- Cards de altas -->
            <div v-if="chartCards.length" class="grid gap-4 sm:grid-cols-3">
                <MetricCard v-for="card in chartCards" :key="card.id" :label="card.label"
                    :value="formatValue(card.value)" :icon="card.iconKey"
                    :variant="(card.variant === 'yellow' ? 'blue' : card.variant) as 'blue' | 'green' | 'red' | 'violet'" />
            </div>

            <!-- Gráfica lineal (tendencia mensual) -->
            <ExpandableChart title="Tendencia de altas (mensual)">
                <LineChart
                    :labels="chartLineLabels"
                    :series="chartLineSeries"
                    smooth
                    area
                    :area-opacity="0.12"
                    legend-position="bottom"
                />
            </ExpandableChart>

            <!-- Gráfica de barras (altas por día) -->
            <ExpandableChart title="Altas por día (última semana)">
                <BarChart
                    :categories="chartBarCategories"
                    :series="chartBarSeries"
                    :show-value-labels="true"
                />
            </ExpandableChart>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DashboardHeader from '@/Components/Dashboards/DashboardHeader.vue'
import MetricCard from '@/Components/Dashboards/MetricCard.vue'
import CustomPicker from '@/Components/Tables/Pickers/CustomPicker.vue'
import ExpandableChart from '@/Components/Dashboards/ExpandableChart.vue'
import LineChart from '@/Components/Charts/LineChart.vue'
import BarChart from '@/Components/Charts/BarChart.vue'
import { useUsers } from './composables/useUsers'

const { usersAdd, getUsersAdd } = useUsers()

/** Datos dummy por defecto para que las gráficas siempre tengan algo que pintar */
const DEFAULT_LINE = {
    labels: ['Ene 2025', 'Feb 2025', 'Mar 2025', 'Abr 2025', 'May 2025', 'Jun 2025'],
    series: [{ name: 'Usuarios creados', data: [1200, 1450, 1320, 1580, 1420, 1530] }],
}
const DEFAULT_BAR = {
    labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
    series: [{ name: 'Altas diarias', data: [12, 19, 15, 22, 18, 8, 5] }],
}

const chartCards = computed(() => usersAdd.value?.cards ?? [])
const chartLineLabels = computed(() => usersAdd.value?.line?.labels ?? DEFAULT_LINE.labels)
const chartLineSeries = computed(() => usersAdd.value?.line?.series ?? DEFAULT_LINE.series)
const chartBarCategories = computed(() => usersAdd.value?.bar?.labels ?? DEFAULT_BAR.labels)
const chartBarSeries = computed(() => usersAdd.value?.bar?.series ?? DEFAULT_BAR.series)

function formatValue(value: unknown): string {
    if (typeof value === 'number') return value.toLocaleString()
    return String(value ?? '–')
}

onMounted(() => {
    const date = new Date().toISOString().slice(0, 10)
    getUsersAdd(date)
})
</script>
