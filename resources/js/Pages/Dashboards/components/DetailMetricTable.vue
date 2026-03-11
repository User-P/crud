<template>
  <div
    class="detail-metric-table rounded-xl border border-(--th-border) bg-(--th-input-bg)/60 backdrop-blur-sm overflow-hidden"
  >
    <!-- Barra de búsqueda + exportar -->
    <div
      class="flex flex-wrap items-center gap-3 border-b border-(--th-border) bg-(--th-input-bg)/40 px-4 py-3"
    >
      <span class="relative flex-1 min-w-[200px] max-w-sm">
        <Icon
          icon="heroicons:magnifying-glass"
          class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-(--th-text-muted)"
          aria-hidden="true"
        />
        <InputText
          v-model="searchText"
          :placeholder="searchPlaceholder"
          class="w-full rounded-lg border-(--th-input-border) bg-(--th-input-bg) pl-9 text-sm placeholder:italic"
          aria-label="Buscar en la tabla"
        />
      </span>

      <div class="flex items-center gap-3">
        <span
          v-if="searchText"
          class="rounded-full bg-(--th-item-active-bg) px-2.5 py-1 text-xs font-medium text-(--th-item-active-color)"
        >
          {{ filteredRows.length }} de {{ rows.length }} coincidencias
        </span>
        <Button
          label="Exportar CSV"
          icon="pi pi-download"
          size="small"
          outlined
          :disabled="filteredRows.length === 0"
          severity="secondary"
          class="shrink-0"
          :loading="exportingCsv"
          @click="exportCSV"
        />
      </div>
    </div>

    <!-- DataTable de PrimeVue -->
    <DataTable
      :value="filteredRows"
      size="small"
      striped-rows
      :rows="rowsPerPage"
      :rows-per-page-options="rowsPerPageOptions"
      paginator
      paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
      current-page-report-template="{first} – {last} de {totalRecords}"
      responsive-layout="scroll"
      class="detail-datatable text-(--th-text-primary) border-0"
    >
      <Column
        v-for="col in columns"
        :key="col.key"
        :field="col.key"
        :header="col.header"
        :sortable="col.sortable !== false"
        :class="col.class"
      >
        <template v-if="col.format" #body="{ data }">
          <span :class="{ 'tabular-nums font-semibold': col.numeric }">
            {{ col.format(data[col.key], data) }}
          </span>
        </template>
      </Column>

      <template #empty>
        <EmptyState
          :title="searchText ? 'Ningún registro coincide con la búsqueda' : 'No hay datos para mostrar'"
          :description="searchText ? 'Prueba con otros términos.' : 'Selecciona una métrica o filtra los datos.'"
          icon="heroicons:magnifying-glass"
        />
      </template>

      <template #footer>
        <div
          class="flex flex-wrap items-center justify-between gap-3 border-t border-(--th-border) bg-(--th-input-bg)/30 px-4 py-3"
        >
          <p class="text-sm text-(--th-text-secondary)">
            <span class="font-semibold tabular-nums text-(--th-text-primary)">{{ totalRecords }}</span>
            {{ totalRecords === 1 ? 'registro' : 'registros' }}
            <span v-if="searchText" class="ml-1 text-(--th-text-muted)">(filtrados)</span>
          </p>
          <p class="text-xs text-(--th-text-muted)">Usa el paginador para ver más resultados.</p>
        </div>
      </template>
    </DataTable>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Icon } from '@iconify/vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import EmptyState from '@/Components/EmptyState.vue'

// ─── Tipos públicos ───────────────────────────────────────────────────────────

export interface DetailMetricColumn<T = Record<string, unknown>> {
  key: string
  header: string
  exportLabel?: string
  sortable?: boolean
  format?: (value: unknown, row: T) => string
  exportFormat?: (value: unknown, row: T) => string
  class?: string
  numeric?: boolean
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = withDefaults(
  defineProps<{
    rows: Record<string, unknown>[]
    columns: DetailMetricColumn[]
    searchPlaceholder?: string
    exportLabel?: string
    rowsPerPage?: number
    rowsPerPageOptions?: number[]
    maxRowsPerCsvFile?: number
  }>(),
  {
    searchPlaceholder: 'Buscar en la tabla…',
    rowsPerPage: 5,
    rowsPerPageOptions: () => [5, 10, 15, 25, 50],
    maxRowsPerCsvFile: 0,
  },
)

// ─── Estado interno ───────────────────────────────────────────────────────────

const searchText = ref('')
const exportingCsv = ref(false)

// ─── Búsqueda ─────────────────────────────────────────────────────────────────

const filteredRows = computed(() => {
  const q = searchText.value.trim().toLowerCase()
  if (!q) return props.rows

  return props.rows.filter((row) =>
    props.columns.some((col) => {
      const raw = row[col.key]
      const str = raw == null
        ? ''
        : col.format
          ? col.format(raw, row)
          : String(raw)
      return str.toLowerCase().includes(q)
    }),
  )
})

const totalRecords = computed(() => filteredRows.value.length)

// ─── Exportar CSV ─────────────────────────────────────────────────────────────

function escapeCsvCell(value: string): string {
  return /[",\n\r]/.test(value) ? `"${value.replace(/"/g, '""')}"` : value
}

function buildCsvChunk(chunkRows: Record<string, unknown>[]): string {
  const header = props.columns.map((c) => c.exportLabel ?? c.header).join(',')
  const lines = chunkRows.map((row) =>
    props.columns
      .map((c) => {
        const raw = row[c.key]
        const str = c.exportFormat
          ? c.exportFormat(raw, row)
          : c.format
            ? c.format(raw, row)
            : raw == null ? '' : String(raw)
        return escapeCsvCell(str)
      })
      .join(','),
  )
  return [header, ...lines].join('\r\n')
}

function downloadBlob(blob: Blob, filename: string) {
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  a.click()
  URL.revokeObjectURL(url)
}

async function exportCSV() {
  if (filteredRows.value.length === 0) return

  const rows = filteredRows.value
  const maxPerFile = props.maxRowsPerCsvFile ?? 0
  const baseName = `detalle-${(props.exportLabel || 'datos')
    .replace(/[^\w\s-]/g, '')
    .replace(/\s+/g, '-')}-${new Date().toISOString().slice(0, 10)}`

  if (maxPerFile > 0 && rows.length > maxPerFile) {
    exportingCsv.value = true
    try {
      const JSZip = (await import('jszip')).default
      const zip = new JSZip()
      const totalParts = Math.ceil(rows.length / maxPerFile)

      for (let part = 0; part < totalParts; part++) {
        const chunk = rows.slice(part * maxPerFile, Math.min((part + 1) * maxPerFile, rows.length))
        zip.file(`parte-${part + 1}-de-${totalParts}.csv`, '\ufeff' + buildCsvChunk(chunk), {
          createFolders: false,
        })
      }

      downloadBlob(await zip.generateAsync({ type: 'blob' }), `${baseName}.zip`)
    } finally {
      exportingCsv.value = false
    }
  } else {
    downloadBlob(
      new Blob(['\ufeff' + buildCsvChunk(rows)], { type: 'text/csv;charset=utf-8' }),
      `${baseName}.csv`,
    )
  }
}
</script>
