<template>
    <AdminLayout title="Charts" subtitle="Galería de ejemplos con Apache ECharts y datos ficticios" :breadcrumbs="[
        { name: 'Dashboard', href: '/dashboard' },
        { name: 'Charts' },
    ]">
        <div class="space-y-6">

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white border-2 border-gray-950 rounded-md">
                    <LineChart :labels="months" :series="lineSeries" title="Visitas por mes" :area="true" />
                </div>

                <div class="bg-white border-2 border-gray-950 rounded-md">

                    <BarChart :categories="barCategories" :series="barSeries" title="Ventas vs objetivo"
                        :stacked="true" />
                </div>

                <div class="bg-white border-2 border-gray-950 rounded-md">
                    <PieChart :data="pieData" title="Participación de canales" legend-position="left" />
                </div>
                <div class="bg-white border-2 border-gray-950 rounded-md">
                    <RadarChart :indicators="radarIndicators" :data="radarData" title="Rendimiento por planta" />
                </div>

                <div class="bg-white border-2 border-gray-950 rounded-md">
                    <RadarChart :indicators="radarIndicators" :data="radarData" title="KPIs operativos" />
                </div>

                <div class="bg-white border-2 border-gray-950 rounded-md">
                    <GaugeChart :value="gaugeValue" :min="80" :max="100" title="Disponibilidad" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue'
import LineChart from '@/Components/Charts/LineChart.vue'
import BarChart from '@/Components/Charts/BarChart.vue'
import PieChart from '@/Components/Charts/PieChart.vue'
import RadarChart from '@/Components/Charts/RadarChart.vue'
import GaugeChart from '@/Components/Charts/GaugeChart.vue'

type Series = { name: string; data: number[] }
type RadarIndicator = { name: string; max: number }
type RadarPoint = { name: string; value: number[] }

interface Props {
    pieData: { value: number; name: string }[]
    months: string[]
    lineSeries: Series[]
    barCategories: string[]
    barSeries: Series[]
    radarIndicators: RadarIndicator[]
    radarData: RadarPoint[]
    gaugeValue: number
}
defineProps<Props>()

</script>
