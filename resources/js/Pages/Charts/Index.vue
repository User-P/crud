<template>
    <AdminLayout title="Charts" subtitle="Graficas generales y por grupo con datos del backend" :breadcrumbs="[
        { name: 'Dashboard', href: '/dashboard' },
        { name: 'Charts' },
    ]">
        <div class="mx-auto max-w-6xl space-y-6 p-6">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <div class="h-96 w-full">
                    <BarChart show-toolbox :categories="['2012', '2013', '2014', '2015', '2016']" :series="[
                        { name: 'Forest', data: [320, 332, 301, 334, 390] },
                        { name: 'Steppe', data: [220, 182, 191, 234, 290] },
                        { name: 'Desert', data: [150, 232, 201, 154, 190] },
                        { name: 'Wetland', data: [98, 77, 101, 99, 40] },
                    ]" show-value-labels label-formatter="{c}  {name|{a}}"
                        :label-rich="{ name: { fontWeight: 600 } }" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BarChart from '@/Components/Charts/BarChart.vue';

</script>
