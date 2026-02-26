<template>
    <AdminLayout title="Dashboards de métricas" subtitle="Resumen y acceso rápido a cada dashboard" :breadcrumbs="[
        { name: 'Dashboard', href: '/dashboard' },
        { name: 'Dashboards de métricas' },
    ]">

        <div class="space-y-8">
            <section>
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">
                    Explorar por tema
                </h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <DashboardCards v-for="card in dashboardCards" v-bind="card" :key="card.name"
                        @navigate="navigate" />
                </div>
            </section>
            <section>
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">
                    Explorar por unidad de negocio
                </h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <DashboardCards v-for="card in byUnits" v-bind="card" :key="card.name" @navigate="navigate" />
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useUsers } from './composables/useUsers';
import DashboardCards from './components/DashboardCards.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const navigate = (href: string) => {
    router.visit(href);
};

const {  getResumen, dashboardCards, byUnits } = useUsers();

onMounted(() => {
    getResumen();
});
</script>
