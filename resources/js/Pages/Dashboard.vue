<template>
  <AdminLayout
    title="Dashboard"
    subtitle="Bienvenido a tu panel de administración"
    :breadcrumbs="[{ name: 'Dashboard' }]"
  >
    <!-- Stats -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
      <div
        v-for="stat in stats"
        :key="stat.name"
        class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6"
      >
        <dt class="truncate text-sm font-medium text-gray-500">
          {{ stat.name }}
        </dt>
        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
          {{ stat.value }}
        </dd>
        <div class="mt-2 flex items-center text-sm">
          <component
            :is="stat.changeType === 'increase' ? ArrowUpIcon : ArrowDownIcon"
            :class="[
              stat.changeType === 'increase' ? 'text-green-500' : 'text-red-500',
              'h-4 w-4',
            ]"
          />
          <span
            :class="[
              stat.changeType === 'increase' ? 'text-green-600' : 'text-red-600',
              'ml-1 font-medium',
            ]"
          >
            {{ stat.change }}
          </span>
          <span class="ml-1 text-gray-500">vs mes anterior</span>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <!-- Recent Activity -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-6">
          <h3 class="text-base font-semibold leading-6 text-gray-900">
            Actividad Reciente
          </h3>
          <div class="mt-6 flow-root">
            <ul role="list" class="-mb-8">
              <li
                v-for="(activity, activityIdx) in recentActivity"
                :key="activity.id"
              >
                <div class="relative pb-8">
                  <span
                    v-if="activityIdx !== recentActivity.length - 1"
                    class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"
                    aria-hidden="true"
                  />
                  <div class="relative flex space-x-3">
                    <div>
                      <span
                        :class="[
                          activity.iconBackground,
                          'flex h-8 w-8 items-center justify-center rounded-full ring-8 ring-white',
                        ]"
                      >
                        <component
                          :is="activity.icon"
                          class="h-5 w-5 text-white"
                          aria-hidden="true"
                        />
                      </span>
                    </div>
                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                      <div>
                        <p class="text-sm text-gray-500">
                          {{ activity.content }}
                        </p>
                      </div>
                      <div class="whitespace-nowrap text-right text-sm text-gray-500">
                        <time>{{ activity.time }}</time>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-6">
          <h3 class="text-base font-semibold leading-6 text-gray-900">
            Acciones Rápidas
          </h3>
          <div class="mt-6 grid grid-cols-2 gap-4">
            <button
              v-for="action in quickActions"
              :key="action.name"
              type="button"
              class="relative rounded-lg border-2 border-dashed border-gray-300 p-4 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
              @click="router.visit(action.href)"
            >
              <component
                :is="action.icon"
                class="mx-auto h-8 w-8 text-gray-400"
                aria-hidden="true"
              />
              <span class="mt-2 block text-sm font-semibold text-gray-900">
                {{ action.name }}
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Chart placeholder -->
    <div class="mt-8 overflow-hidden rounded-lg bg-white shadow">
      <div class="p-6">
        <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">
          Estadísticas de los Últimos 30 Días
        </h3>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
          <div class="text-center">
            <ChartBarIcon class="mx-auto h-12 w-12 text-gray-400" />
            <p class="mt-2 text-sm text-gray-500">
              Gráfica de estadísticas
            </p>
            <p class="text-xs text-gray-400">
              Integra Chart.js o ApexCharts aquí
            </p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  ArrowUpIcon,
  ArrowDownIcon,
  UserPlusIcon,
  DocumentPlusIcon,
  FolderPlusIcon,
  Cog6ToothIcon,
  CheckCircleIcon,
  UserCircleIcon,
  ChartBarIcon,
} from '@heroicons/vue/24/outline'

interface Stat {
  name: string
  value: string
  change: string
  changeType: 'increase' | 'decrease'
}

interface Activity {
  id: number
  content: string
  time: string
  icon: any
  iconBackground: string
}

interface QuickAction {
  name: string
  href: string
  icon: any
}

const stats: Stat[] = [
  { name: 'Total Usuarios', value: '1,234', change: '+12.5%', changeType: 'increase' },
  { name: 'Eventos', value: '567', change: '+8.2%', changeType: 'increase' },
  { name: 'Países', value: '89', change: '-2.4%', changeType: 'decrease' },
  { name: 'Activos Hoy', value: '345', change: '+15.3%', changeType: 'increase' },
]

const recentActivity: Activity[] = [
  {
    id: 1,
    content: 'Nuevo usuario registrado',
    time: 'Hace 5 min',
    icon: UserCircleIcon,
    iconBackground: 'bg-blue-500',
  },
  {
    id: 2,
    content: 'Evento creado exitosamente',
    time: 'Hace 15 min',
    icon: CheckCircleIcon,
    iconBackground: 'bg-green-500',
  },
  {
    id: 3,
    content: 'País actualizado',
    time: 'Hace 1 hora',
    icon: FolderPlusIcon,
    iconBackground: 'bg-purple-500',
  },
  {
    id: 4,
    content: 'Configuración modificada',
    time: 'Hace 2 horas',
    icon: Cog6ToothIcon,
    iconBackground: 'bg-orange-500',
  },
]

const quickActions: QuickAction[] = [
  { name: 'Nuevo Usuario', href: '/users/create', icon: UserPlusIcon },
  { name: 'Nuevo Evento', href: '/events/create', icon: DocumentPlusIcon },
  { name: 'Nuevo País', href: '/countries/create', icon: FolderPlusIcon },
  { name: 'Configuración', href: '/settings', icon: Cog6ToothIcon },
]
</script>
