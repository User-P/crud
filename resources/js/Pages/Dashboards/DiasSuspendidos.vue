<template>
    <AdminLayout
        :breadcrumbs="[
            { name: 'Dashboard', href: '/dashboard' },
            { name: 'Dashboards de métricas', href: '/dashboards' },
            { name: 'Días usuarios suspendidos' },
        ]"
    >
        <div class="space-y-8">
            <DashboardHeader
                title="Días usuarios suspendidos"
                subtitle="Semáforo de riesgo por tiempo de suspensión"
                :icon="ExclamationTriangleIcon"
            >
                <template #actions>
                    <CustomPicker
                        initial-preset="lastMonth"
                        select-disabled
                        class="rounded-xl border border-slate-200 bg-white shadow-sm"
                    />
                </template>
            </DashboardHeader>

            <!-- Data storytelling: insight en una línea -->
            <div class="rounded-xl border border-amber-100 bg-amber-50/50 px-4 py-3 text-sm text-amber-900">
                <span class="font-medium">Insight:</span>
                El 44% de los usuarios suspendidos llevan 7+ días (riesgo elevado). Priorizar revisión de ese grupo.
            </div>

            <!-- Leyenda de riesgo -->
            <div class="flex flex-wrap gap-6 rounded-xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-amber-400" aria-hidden="true" />
                    <span class="text-sm text-slate-600">1-3 días (riesgo menor)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-orange-500" aria-hidden="true" />
                    <span class="text-sm text-slate-600">4-6 días (riesgo moderado)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-rose-500" aria-hidden="true" />
                    <span class="text-sm text-slate-600">7+ días (riesgo elevado)</span>
                </div>
            </div>

            <!-- Gráfico semáforo -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-600">
                    Usuarios suspendidos por rango de días
                </h3>
                <div ref="chartRef" class="h-80">
                    <SemaphoreBarChart
                        :categories="['1-3 días', '4-6 días', '7+ días']"
                        :values="[600, 900, 1200]"
                        :colors="['#eab308', '#f97316', '#ef4444']"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DashboardHeader from '@/Components/Dashboards/DashboardHeader.vue'
import CustomPicker from '@/Components/Tables/Pickers/CustomPicker.vue'
import SemaphoreBarChart from '@/Components/Charts/SemaphoreBarChart.vue'
import {
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'
import { autoAnimate } from '@formkit/auto-animate'

const chartRef = ref<HTMLElement | null>(null)

onMounted(() => {
    if (chartRef.value) autoAnimate(chartRef.value)
})
</script>
