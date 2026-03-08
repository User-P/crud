<template>
    <AdminLayout
        :breadcrumbs="[
            { name: 'Dashboard', href: '/dashboard' },
            { name: 'Dashboards de métricas', href: '/dashboards' },
            { name: 'Usuarios nuevos' },
        ]"
    >
        <div class="space-y-8">
            <DashboardHeader
                title="Usuarios nuevos"
                subtitle="Altas de usuarios y tendencias"
                icon="heroicons:user-plus"
            >
                <template #actions>
                    <CustomPicker
                        initial-preset="lastMonth"
                        select-disabled
                        class="cosmos-surface rounded-xl shadow-sm"
                    />
                </template>
            </DashboardHeader>

            <!-- Cards de altas -->
            <div v-if="usersAdd?.cards" class="grid gap-4 sm:grid-cols-3">
                <MetricCard
                    v-for="card in usersAdd.cards"
                    :key="card.id"
                    :label="card.label"
                    :value="formatValue(card.value)"
                    :icon="card.icon"
                    :variant="card.variant === 'yellow' ? 'blue' : card.variant"
                />
            </div>

            <!-- Gráfica lineal (tendencia mensual) -->
            <ExpandableChart title="Tendencia de altas (mensual)">
                <div class="h-64 flex items-center justify-center text-slate-500 text-sm">
                    Gráfica de línea: {{ usersAdd?.line?.labels?.length ?? 0 }} meses (datos dummy)
                </div>
            </ExpandableChart>

            <!-- Gráfica de barras (altas por día) -->
            <ExpandableChart title="Altas por día (última semana)">
                <div class="h-64 flex items-center justify-center text-slate-500 text-sm">
                    Gráfica de barras: {{ usersAdd?.bar?.labels?.length ?? 0 }} días (datos dummy)
                </div>
            </ExpandableChart>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DashboardHeader from '@/Components/Dashboards/DashboardHeader.vue'
import MetricCard from '@/Components/Dashboards/MetricCard.vue'
import CustomPicker from '@/Components/Tables/Pickers/CustomPicker.vue'
import ExpandableChart from '@/Components/Dashboards/ExpandableChart.vue'
import { useUsers } from './composables/useUsers'

const { usersAdd, getUsersAdd } = useUsers()

function formatValue(value: unknown): string {
    if (typeof value === 'number') return value.toLocaleString()
    return String(value ?? '–')
}

onMounted(() => {
    const date = new Date().toISOString().slice(0, 10)
    getUsersAdd(date)
})
</script>
