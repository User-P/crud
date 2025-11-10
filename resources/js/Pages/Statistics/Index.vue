<template>
    <AdminLayout title="Estadísticas" subtitle="Visualiza las estadísticas del sistema" :breadcrumbs="[
        { name: 'Dashboard', href: '/dashboard' },
        { name: 'Estadísticas' },
    ]">
        <section class="space-y-8">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Resumen general</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Valores dummy que más adelante vendrán desde la API y detonarían tablas o gráficas
                    específicas.
                </p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                <article v-for="card in statCards" :key="card.id"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-medium text-slate-500">{{ card.label }}</div>
                        <div class="flex items-center gap-2">
                            <button type="button"
                                class="rounded-full bg-slate-100 p-2 text-slate-500 transition hover:bg-slate-200 hover:text-slate-800"
                                title="Ver datatable" @click="openDetails(card.id, 'table')">
                                <component :is="card.icon" class="h-5 w-5" aria-hidden="true" />
                            </button>
                            <button type="button"
                                class="rounded-full bg-indigo-100 p-2 text-indigo-600 transition hover:bg-indigo-200 hover:text-indigo-800"
                                title="Ver gráfica (ApexCharts)" @click="openDetails(card.id, 'chart')">
                                <ChartBarIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        {{ card.value }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">{{ card.helper }}</p>

                    <div class="mt-4 flex items-center gap-2 text-sm font-medium">
                        <span :class="[
                            'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold',
                            card.trend === 'up'
                                ? 'bg-emerald-50 text-emerald-700'
                                : card.trend === 'down'
                                    ? 'bg-rose-50 text-rose-700'
                                    : 'bg-slate-100 text-slate-600',
                        ]">
                            <ArrowTrendingUpIcon v-if="card.trend === 'up'" class="mr-1 h-4 w-4" aria-hidden="true" />
                            <ArrowTrendingDownIcon v-else-if="card.trend === 'down'" class="mr-1 h-4 w-4"
                                aria-hidden="true" />
                            <MinusSmallIcon v-else class="mr-1 h-4 w-4" aria-hidden="true" />
                            {{ card.delta }}
                        </span>
                        <span class="text-slate-500">{{ card.deltaLabel }}</span>
                    </div>
                </article>
            </div>
        </section>

        <section v-if="activeDetail" class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">
                        Detalle de {{ selectedCard?.label ?? 'modelo' }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        <span v-if="activeDetail?.mode === 'table'">
                            Datos dummy mostrados con PrimeVue DataTable. Más adelante se reemplazarán con la
                            respuesta del endpoint real.
                        </span>
                        <span v-else>
                            Placeholder para el componente de ApexCharts correspondiente al insight.
                        </span>
                    </p>
                </div>

                <button type="button"
                    class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100"
                    @click="closeDetails">
                    Cerrar detalles
                </button>
            </div>

            <div v-if="activeDetail?.mode === 'table'"
                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <DataTable :value="currentDetailRows" paginator :rows="5" :rows-per-page-options="[5, 10, 20]"
                    table-style="min-width: 50rem" responsive-layout="scroll">
                    <Column field="model" header="Modelo" sortable />
                    <Column field="monitoredUsers" header="Usuarios monitoreados" sortable />
                    <Column field="alerts" header="Alertas activas" sortable />
                    <Column field="sources" header="Fuentes claves" />
                    <Column field="lastSync" header="Última actualización" sortable />
                </DataTable>
            </div>

            <div v-else
                class="rounded-2xl border border-dashed border-indigo-200 bg-indigo-50 p-10 text-center text-indigo-700">
                <ChartBarIcon class="mx-auto h-10 w-10 text-indigo-400" />
                <h4 class="mt-4 text-lg font-semibold">Placeholder de ApexCharts</h4>
                <p class="mt-2 text-sm">
                    Aquí agregarás el componente de gráficos para
                    <strong>{{ selectedCard?.label ?? 'este insight' }}</strong>. Conecta tus datos cuando
                    estés listo.
                </p>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import {
    ArrowTrendingDownIcon,
    ArrowTrendingUpIcon,
    ChartBarIcon,
    CpuChipIcon,
    MinusSmallIcon,
    ShieldCheckIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/outline'
import type { Component } from 'vue'

type StatCard = {
    id: string
    label: string
    value: string
    helper: string
    delta: string
    deltaLabel: string
    trend: 'up' | 'down' | 'flat'
    icon: Component
}

const statCards: StatCard[] = [
    {
        id: 'monitored-users',
        label: 'Usuarios monitoreados',
        value: '32,795',
        helper: 'De un universo de 45,120 usuarios activos',
        delta: '+3.2%',
        deltaLabel: 'vs. el mes anterior',
        trend: 'up',
        icon: UserGroupIcon,
    },
    {
        id: 'sensitive-events',
        label: 'Eventos sensibles',
        value: '1,842',
        helper: 'Alertas generadas por los modelos ML',
        delta: '-5.4%',
        deltaLabel: 'vs. semana anterior',
        trend: 'down',
        icon: ShieldCheckIcon,
    },
    {
        id: 'automations',
        label: 'Automatizaciones activas',
        value: '128',
        helper: 'Workflows monitoreando anomalías',
        delta: '+0.0%',
        deltaLabel: 'sin cambios recientes',
        trend: 'flat',
        icon: CpuChipIcon,
    },
    {
        id: 'avg-response-time',
        label: 'Tiempo medio de respuesta',
        value: '14m 22s',
        helper: 'Desde que se dispara una alerta crítica',
        delta: '+1.1%',
        deltaLabel: 'ligero incremento',
        trend: 'up',
        icon: ChartBarIcon,
    },
]

type DetailRow = {
    model: string
    monitoredUsers: string
    alerts: string
    sources: string
    lastSync: string
}

const detailRows: Record<string, DetailRow[]> = {
    'monitored-users': [
        {
            model: 'Tabla de Tecleo Alnova',
            monitoredUsers: '54,123',
            alerts: '59 críticas',
            sources: 'Plantilla, Log Exchange, DLP',
            lastSync: '2024-06-11 13:20',
        },
        {
            model: 'Búsqueda de Empleo',
            monitoredUsers: '26,781',
            alerts: '16 moderadas',
            sources: 'Mónaco, Teramind, Teams',
            lastSync: '2024-06-11 12:45',
        },
    ],
    'sensitive-events': [
        {
            model: 'Score Infractores DLP',
            monitoredUsers: '32,795',
            alerts: '10 críticas',
            sources: 'Cyberark, DLP, CASB',
            lastSync: '2024-06-11 07:52',
        },
        {
            model: 'Búsqueda de Empleo',
            monitoredUsers: '26,781',
            alerts: '16 moderadas',
            sources: 'Mónaco, Teramind, Teams',
            lastSync: '2024-06-11 12:45',
        },
    ],
    automations: [
        {
            model: 'Automatización DLP 01',
            monitoredUsers: '—',
            alerts: '5 workflows activos',
            sources: 'Cyberark, DLP',
            lastSync: '2024-06-10 18:31',
        },
        {
            model: 'Automatización CASB',
            monitoredUsers: '—',
            alerts: '3 workflows activos',
            sources: 'CASB, Log Exchange',
            lastSync: '2024-06-09 21:04',
        },
    ],
    'avg-response-time': [
        {
            model: 'Mesa SOC',
            monitoredUsers: '—',
            alerts: 'Tiempo medio: 12m',
            sources: 'Canal interno, PagerDuty',
            lastSync: '2024-06-11 11:05',
        },
        {
            model: 'Mesa DLP',
            monitoredUsers: '—',
            alerts: 'Tiempo medio: 17m',
            sources: 'Email, Teams, DLP',
            lastSync: '2024-06-10 20:48',
        },
    ],
}

type DetailMode = 'table' | 'chart'

const activeDetail = ref<{ cardId: string; mode: DetailMode } | null>(null)

const openDetails = (cardId: string, mode: DetailMode) => {
    activeDetail.value = { cardId, mode }
}

const closeDetails = () => {
    activeDetail.value = null
}

const selectedCard = computed(() => statCards.find((card) => card.id === activeDetail.value?.cardId))
const currentDetailRows = computed<DetailRow[]>(() =>
    activeDetail.value?.mode === 'table'
        ? detailRows[activeDetail.value?.cardId ?? ''] ?? []
        : []
)
</script>
