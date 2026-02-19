<template>
    <AdminLayout
        title="Dashboards de métricas"
        subtitle="Selecciona un dashboard para visualizar las métricas de usuarios"
        :breadcrumbs="[
            { name: 'Dashboard', href: '/dashboard' },
            { name: 'Dashboards de métricas' },
        ]"
    >
        <div
            class="min-h-[60vh] rounded-3xl bg-slate-50 p-8"
            style="background-image: radial-gradient(circle at 1px 1px, rgba(0,0,0,.06) 1px, transparent 0); background-size: 24px 24px;"
        >
            <div class="mx-auto max-w-4xl">
                <p class="mb-8 text-center text-slate-600">
                    Elige uno de los dashboards disponibles para explorar las métricas en detalle.
                </p>
                <div
                    ref="cardsRef"
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <a
                        v-for="card in dashboardCards"
                        :key="card.href"
                        :href="card.href"
                        class="group flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-2 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        @click.prevent="navigate(card.href)"
                    >
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 transition-transform duration-300 group-hover:scale-110"
                        >
                            <component :is="card.icon" class="h-7 w-7" aria-hidden="true" />
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-slate-900">
                            {{ card.name }}
                        </h3>
                        <p class="mt-2 flex-1 text-sm text-slate-500">
                            {{ card.description }}
                        </p>
                        <span
                            class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 group-hover:text-indigo-700"
                        >
                            Ver dashboard
                            <ArrowRightIcon class="ml-1 h-4 w-4 transition-transform group-hover:translate-x-1" />
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
    PresentationChartBarIcon,
    UserGroupIcon,
    ExclamationTriangleIcon,
    ArrowRightIcon,
} from '@heroicons/vue/24/outline'

const dashboardCards = [
    {
        name: 'Vista general',
        description: 'Indicadores clave: usuarios totales, activos, inactivos, en espera y más.',
        href: '/dashboards/vista-general',
        icon: PresentationChartBarIcon,
    },
    {
        name: 'Usuarios activos / inactivos',
        description: 'Distribución de población con gráficos interactivos y estatus de usuario.',
        href: '/dashboards/usuarios-activos-inactivos',
        icon: UserGroupIcon,
    },
    {
        name: 'Días usuarios suspendidos',
        description: 'Semáforo de riesgo por rango de días de suspensión.',
        href: '/dashboards/dias-suspendidos',
        icon: ExclamationTriangleIcon,
    },
]

const cardsRef = ref<HTMLElement | null>(null)

const navigate = (href: string) => {
    router.visit(href)
}
</script>
