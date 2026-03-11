<template>
  <DashboardLayout
    page="Usuarios Cyaal"
    :breadcrumbs="[
      { name: 'Dashboard', href: '/dashboard_cyaal/usuarios' },
      { name: 'Dashboards de métricas' },
    ]"
  >
    <div class="space-y-8">
      <Header
        :title="titles.main"
        subtitle="Indicadores clave de usuarios. Clic en una tarjeta para ver detalle."
        icon="heroicons:user-group"
      >
        <template #actions>
          <DatePicker
            v-model="date"
            :maxDate="maxDate"
            showIcon
            iconDisplay="input"
            dateFormat="dd/mm/yy"
            fluid
          />
        </template>
      </Header>

      <template v-if="users">
        <!-- Tarjetas hero + complementarias -->
        <section>
          <div class="grid grid-cols-1 gap-5 sm:grid-cols-[2fr_1fr]">
            <MetricCard
              v-for="card in users.main"
              :key="card.id"
              v-bind="card"
              :trend-percent="2.4"
              :class="selectedRing(card.id)"
              @click="openDetail(card)"
            />

            <div class="flex flex-col gap-5">
              <MetricCard
                v-for="card in users.more"
                :key="card.id"
                v-bind="card"
                :trend-percent="2.4"
                :class="selectedRing(card.id)"
                @click="openDetail(card)"
              />
            </div>
          </div>
        </section>

        <!-- Con licencia -->
        <section>
          <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">
            Con licencia
          </h3>
          <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <MetricCard
              v-for="card in users.primary"
              :key="card.id"
              v-bind="card"
              :class="selectedRing(card.id)"
              @click="openDetail(card)"
            />
          </div>
        </section>

        <!-- Sin licencia -->
        <section>
          <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">
            Sin licencia
          </h3>
          <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <MetricCard
              v-for="card in users.secondary"
              :key="card.id"
              v-bind="card"
              :class="selectedRing(card.id)"
              @click="openDetail(card)"
            />
          </div>
        </section>

        <!-- Tabla de detalle -->
        <section
          class="overflow-hidden rounded-2xl border border-[var(--th-border)] bg-[var(--th-input-bg)] shadow-sm"
        >
          <div
            class="flex flex-wrap items-center justify-between gap-3 border-b border-[var(--th-border)] bg-[var(--th-input-bg)] px-4 py-3"
          >
            <h3 class="text-sm font-semibold text-[color:var(--th-text-primary)]">
              {{ selectedCard ? `Detalle: ${selectedCard.label}` : 'Detalle de la métrica' }}
            </h3>
          </div>

          <div class="relative min-h-[200px] overflow-x-auto px-4 pb-4">
            <!--
              :key fuerza re-montaje del componente al cambiar de tarjeta,
              reseteando búsqueda y paginación automáticamente.
            -->
            <DetailMetricTable
              :key="selectedCard?.id"
              class="mt-3"
              v-show="!isLoading"
              :rows="detailRows"
              :columns="detailColumns"
              search-placeholder="Buscar en ID, estatus…"
              :export-label="selectedCard?.label"
            />
          </div>
        </section>

        <!-- Gráfica (opcional) -->
        <div
          v-if="users.chart"
          class="rounded-md border border-slate-300 bg-blue-50"
        >
          <BarChart
            :labels="users.chart.labels"
            :datasets="users.chart.datasets"
          />
        </div>

        <p class="text-xs text-slate-400">
          Última actualización: datos de referencia · Clic en cualquier tarjeta para ver detalle
        </p>
      </template>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { DatePicker } from 'primevue'
import { usePage } from '@inertiajs/vue3'

import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Header from '@/Components/Cyaal/Header.vue'
import MetricCard from '@/Components/Cyaal/MetricCard.vue'
import BarChart from '@/Components/Charts/BarChart.vue'
import DetailMetricTable, { type DetailMetricColumn } from '../components/DetailMetricTable.vue'
import { useUsers } from '../composables/useUsers'

// ─── Tipos ────────────────────────────────────────────────────────────────────

type CardItem = {
  id: string
  label: string
  value: string | number
  iconKey: string
  variant: 'blue' | 'green' | 'red'
}

// ─── Composable ───────────────────────────────────────────────────────────────

const { isLoading, users, details, getIndicadores, getDetails, clearDetails } = useUsers()

// ─── Estado local ─────────────────────────────────────────────────────────────

const date = ref<Date>(new Date())
const maxDate = ref<Date>(new Date())
const selectedCard = ref<CardItem | null>(null)

// ─── Inertia page props ───────────────────────────────────────────────────────

const page = usePage<{ unit?: string | null }>()
const unit = computed(() => page.props.unit ?? null)

// ─── Títulos dinámicos ────────────────────────────────────────────────────────

const titles = computed(() => ({
  main: 'Consumo de Licencias ' + (unit.value ?? ''),
}))

// ─── Clase de anillo para la tarjeta seleccionada ─────────────────────────────

function selectedRing(id: string) {
  return selectedCard.value?.id === id
    ? 'ring-2 ring-[var(--th-input-focus-border)] ring-offset-2 ring-offset-[var(--th-ring-offset)]'
    : ''
}

// ─── Abrir detalle de tarjeta ─────────────────────────────────────────────────

function openDetail(card: CardItem) {
  selectedCard.value = card
  // Siempre solicita datos frescos para la fecha activa.
  // clearDetails() en watch(date) evita mostrar datos de otra fecha.
  getDetails(card.id, date.value.toISOString().split('T')[0], unit.value ?? undefined)
}

// ─── Watchers ─────────────────────────────────────────────────────────────────

// Al cambiar la fecha: limpiar caché y recargar indicadores
watch(
  date,
  (newDate) => {
    selectedCard.value = null
    clearDetails()
    getIndicadores(newDate.toISOString().split('T')[0], unit.value ?? undefined)
  },
  { immediate: true },
)

// Cuando los indicadores lleguen, abrir la primera tarjeta automáticamente
watch(
  users,
  (u) => {
    const firstCard = u?.main?.[0] ?? null
    if (firstCard && !selectedCard.value) {
      openDetail(firstCard as CardItem)
    }
  },
  { immediate: true },
)

// ─── Filas de detalle ─────────────────────────────────────────────────────────

/**
 * `details` es reactive({}) en el composable, por lo que Vue detecta
 * cuando se agrega details[card.id] y re-evalúa este computed.
 */
const detailRows = computed<Record<string, unknown>[]>(() => {
  if (!selectedCard.value) return []
  return details[selectedCard.value.id] ?? []
})

// ─── Columnas de la tabla ─────────────────────────────────────────────────────

/**
 * Define las columnas según la tarjeta activa.
 * Extiende este computed para mostrar columnas diferentes por sección si es necesario.
 */
const detailColumns = computed<DetailMetricColumn[]>(() => [
  {
    key: 'id_cyaal_usr',
    header: 'ID Usuario',
    sortable: true,
    class: 'font-mono font-medium',
  },
  {
    key: 'estatus_cyaal_usr',
    header: 'Estatus',
    sortable: true,
  },
])
</script>
