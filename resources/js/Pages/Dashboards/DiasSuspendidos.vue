<template>
    <AdminLayout
        :breadcrumbs="[
            { name: 'Dashboard', href: '/dashboard' },
            { name: 'Dashboards de métricas', href: '/dashboards' },
            { name: 'Días usuarios suspendidos' },
        ]"
    >
        <div
            class="space-y-8 rounded-3xl bg-slate-50 p-6 sm:p-8"
            style="background-image: radial-gradient(circle at 1px 1px, rgba(0,0,0,.06) 1px, transparent 0); background-size: 24px 24px;"
        >
            <DashboardHeader
                title="Visualizaciones - Días Usuarios Suspendidos (Semáforo de riesgo)"
                subtitle="Clic en 'Usuarios Suspendidos' → segmentación por días"
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

            <!-- Leyenda de riesgo -->
            <div class="flex flex-wrap gap-6 rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-amber-400" />
                    <span class="text-sm text-slate-600">1-3 días (riesgo menor)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-orange-500" />
                    <span class="text-sm text-slate-600">4-6 días (riesgo moderado)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-rose-500" />
                    <span class="text-sm text-slate-600">7+ días (riesgo elevado)</span>
                </div>
            </div>

            <!-- Gráfico semáforo -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <CalendarDaysIcon class="h-5 w-5 text-indigo-600" />
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700">
                        Días usuarios suspendidos (por rango)
                    </h3>
                </div>
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
    CalendarDaysIcon,
} from '@heroicons/vue/24/outline'
import { autoAnimate } from '@formkit/auto-animate'

const chartRef = ref<HTMLElement | null>(null)

onMounted(() => {
    if (chartRef.value) autoAnimate(chartRef.value)
})
</script>
