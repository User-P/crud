<template>
  <AdminLayout
    title="Estadísticas"
    subtitle="Visualiza las estadísticas del sistema"
    :breadcrumbs="[
      { name: 'Dashboard', href: '/dashboard' },
      { name: 'Estadísticas' },
    ]"
  >
    <section class="space-y-8">
      <div>
        <h3 class="text-base font-semibold text-gray-900">Resumen general</h3>
        <p class="mt-1 text-sm text-gray-500">
          Valores dummy que más adelante vendrán desde la API y detonarían tablas o gráficas
          específicas.
        </p>
      </div>

      <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <article
          v-for="card in statCards"
          :key="card.id"
          class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
          <div class="flex items-center justify-between">
            <div class="text-sm font-medium text-slate-500">{{ card.label }}</div>
            <component :is="card.icon" class="h-6 w-6 text-slate-400" aria-hidden="true" />
          </div>
          <p class="mt-3 text-3xl font-semibold text-slate-900">
            {{ card.value }}
          </p>
          <p class="mt-1 text-sm text-slate-500">{{ card.helper }}</p>

          <div class="mt-4 flex items-center gap-2 text-sm font-medium">
            <span
              :class="[
                'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold',
                card.trend === 'up'
                  ? 'bg-emerald-50 text-emerald-700'
                  : card.trend === 'down'
                    ? 'bg-rose-50 text-rose-700'
                    : 'bg-slate-100 text-slate-600',
              ]"
            >
              <ArrowTrendingUpIcon
                v-if="card.trend === 'up'"
                class="mr-1 h-4 w-4"
                aria-hidden="true"
              />
              <ArrowTrendingDownIcon
                v-else-if="card.trend === 'down'"
                class="mr-1 h-4 w-4"
                aria-hidden="true"
              />
              <MinusSmallIcon v-else class="mr-1 h-4 w-4" aria-hidden="true" />
              {{ card.delta }}
            </span>
            <span class="text-slate-500">{{ card.deltaLabel }}</span>
          </div>
        </article>
      </div>

      <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
        <ChartBarIcon class="mx-auto h-12 w-12 text-slate-400" />
        <h4 class="mt-4 text-lg font-semibold text-slate-900">Widgets interactivos próximamente</h4>
        <p class="mt-2 text-sm text-slate-600">
          Cada tarjeta podrá abrir un datatable o una gráfica con ApexCharts para profundizar en los
          datos. Este bloque se mantendrá como recordatorio hasta enlazar los endpoints reales.
        </p>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  ArrowTrendingDownIcon,
  ArrowTrendingUpIcon,
  ChartBarIcon,
  CpuChipIcon,
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
</script>
