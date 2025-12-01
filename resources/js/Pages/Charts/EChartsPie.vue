<template>
    <AdminLayout title="Gráfica de pastel" subtitle="Ejemplo sencillo de Apache ECharts" :breadcrumbs="[
        { name: 'Dashboard', href: '/dashboard' },
        { name: 'Charts', href: '/charts' },
        { name: 'Pie' },
    ]">
        <div class="mx-auto max-w-5xl space-y-4 p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">Apache ECharts + Vue</h1>
                    <p class="text-sm text-slate-500">
                        Ejemplo basico de grafica de pastel con datos enviados desde Laravel via Inertia.
                    </p>
                </div>
                <a href="https://echarts.apache.org/en/option.html#series-pie" target="_blank" rel="noreferrer"
                    class="text-sm font-medium text-blue-600 hover:underline">
                    Documentacion
                </a>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <div class="h-96 w-full">
                    <PieChart
                        :data="data"
                        :title="title || 'Distribución por categoría'"
                        legend-position="left"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PieChart from "@/Components/Charts/PieChart.vue";

type Slice = {
    value: number;
    name: string;
};

defineProps<{
    data: Slice[];
    title?: string;
}>();
</script>
