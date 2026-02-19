<template>
    <AdminLayout
        title="Dashboards de métricas"
        subtitle="Resumen y acceso rápido a cada dashboard"
        :breadcrumbs="[
            { name: 'Dashboard', href: '/dashboard' },
            { name: 'Dashboards de métricas' },
        ]"
    >
        <div class="space-y-8">
            <!-- Quick stats: números clave en 5 segundos (best practice) -->
            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">
                    Resumen rápido
                </h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="flex items-center gap-4 rounded-xl bg-slate-50/80 p-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                            <UserGroupIcon class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold tabular-nums text-slate-900">140k</p>
                            <p class="text-sm text-slate-500">Usuarios totales</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-xl bg-slate-50/80 p-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                            <CheckCircleIcon class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold tabular-nums text-slate-900">132k</p>
                            <p class="text-sm text-slate-500">Activos</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-xl bg-slate-50/80 p-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                            <ExclamationTriangleIcon class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold tabular-nums text-slate-900">2,050</p>
                            <p class="text-sm text-slate-500">Suspendidos</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Dashboards disponibles (jerarquía clara) -->
            <section>
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">
                    Explorar por tema
                </h2>
                <div
                    ref="cardsRef"
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <a
                        v-for="card in dashboardCards"
                        :key="card.href"
                        :href="card.href"
                        class="group flex flex-col rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:ring-offset-2"
                        @click.prevent="navigate(card.href)"
                    >
                        <div class="flex items-center justify-between">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-105"
                                :class="card.iconBg"
                            >
                                <component :is="card.icon" class="h-6 w-6" :class="card.iconColor" aria-hidden="true" />
                            </div>
                            <span
                                class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 group-hover:bg-slate-200"
                            >
                                {{ card.badge }}
                            </span>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-slate-900">
                            {{ card.name }}
                        </h3>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-500">
                            {{ card.description }}
                        </p>
                        <span
                            class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 group-hover:text-indigo-700"
                        >
                            Abrir dashboard
                            <ArrowRightIcon class="ml-1 h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                        </span>
                    </a>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
    PresentationChartBarIcon,
    UserGroupIcon,
    ExclamationTriangleIcon,
    ArrowRightIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline'
import { autoAnimate } from '@formkit/auto-animate'

const dashboardCards = [
    {
        name: 'Vista general',
        description: 'KPIs principales con tendencias, sparklines y drill-down al detalle.',
        href: '/dashboards/vista-general',
        icon: PresentationChartBarIcon,
        iconBg: 'bg-indigo-100',
        iconColor: 'text-indigo-600',
        badge: 'KPIs',
    },
    {
        name: 'Usuarios activos / inactivos',
        description: 'Distribución y estatus con gráficos interactivos.',
        href: '/dashboards/usuarios-activos-inactivos',
        icon: UserGroupIcon,
        iconBg: 'bg-emerald-100',
        iconColor: 'text-emerald-600',
        badge: 'Gráficos',
    },
    {
        name: 'Días suspendidos',
        description: 'Semáforo de riesgo por tiempo de suspensión.',
        href: '/dashboards/dias-suspendidos',
        icon: ExclamationTriangleIcon,
        iconBg: 'bg-amber-100',
        iconColor: 'text-amber-600',
        badge: 'Riesgo',
    },
]

const cardsRef = ref<HTMLElement | null>(null)

const navigate = (href: string) => {
    router.visit(href)
}

onMounted(() => {
    if (cardsRef.value) autoAnimate(cardsRef.value)
})
</script>
