<template>
    <div class="detail-metric-table rounded-xl border border-(--th-border) bg-(--th-input-bg)/60 backdrop-blur-sm overflow-hidden">
        <!-- Barra: búsqueda + exportar -->
        <div class="flex flex-wrap items-center gap-3 border-b border-(--th-border) bg-(--th-input-bg)/40 px-4 py-3">
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
                    @click="exportCSV"
                />
            </div>
        </div>

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
                        {{ col.format(getCellValue(data, col.key), data) }}
                    </span>
                </template>
            </Column>

            <template #empty>
                <div class="py-12 text-center">
                    <div
                        class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-(--th-input-bg) text-(--th-text-muted)"
                    >
                        <Icon icon="heroicons:magnifying-glass" class="h-6 w-6" aria-hidden="true" />
                    </div>
                    <p class="text-sm font-medium text-(--th-text-primary)">
                        {{ searchText ? 'Ningún registro coincide con la búsqueda' : 'No hay datos para mostrar' }}
                    </p>
                    <p class="mt-1 text-xs text-(--th-text-muted)">
                        {{ searchText ? 'Prueba con otros términos.' : 'Selecciona una métrica o filtra los datos.' }}
                    </p>
                </div>
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
                    <p class="text-xs text-(--th-text-muted)">
                        Usa el paginador para ver más resultados.
                    </p>
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

/** Configuración de una columna (datos dinámicos) */
export interface DetailMetricColumn<T = Record<string, unknown>> {
    /** Clave del campo en cada fila */
    key: string
    /** Encabezado en la tabla y por defecto en CSV */
    header: string
    /** Etiqueta para exportación CSV (por defecto: header) */
    exportLabel?: string
    /** Si la columna es ordenable */
    sortable?: boolean
    /** Formatear valor para mostrar (y para CSV si no hay exportFormat) */
    format?: (value: unknown, row: T) => string
    /** Formatear para CSV (por defecto: format o String(value)) */
    exportFormat?: (value: unknown, row: T) => string
    /** Clase CSS para las celdas */
    class?: string
    /** Aplicar tabular-nums y font-semibold (para números) */
    numeric?: boolean
}

const props = withDefaults(
    defineProps<{
        /** Filas genéricas (cualquier objeto con claves que coincidan con columns[].key) */
        rows: Record<string, unknown>[]
        /** Definición de columnas */
        columns: DetailMetricColumn[]
        /** Placeholder del campo de búsqueda */
        searchPlaceholder?: string
        /** Etiqueta para el nombre del archivo al exportar */
        exportLabel?: string
        /** Filas por página por defecto */
        rowsPerPage?: number
        /** Opciones del selector de filas por página */
        rowsPerPageOptions?: number[]
    }>(),
    {
        searchPlaceholder: 'Buscar en la tabla…',
        rowsPerPage: 5,
        rowsPerPageOptions: () => [5, 10, 15, 25, 50],
    }
)

const searchText = ref('')

function getCellValue(row: Record<string, unknown>, key: string): unknown {
    return row[key]
}

/** Búsqueda en todos los valores de las columnas */
const filteredRows = computed(() => {
    const q = searchText.value.trim().toLowerCase()
    if (!q) return props.rows
    const keys = props.columns.map((c) => c.key)
    return props.rows.filter((row) => {
        return keys.some((key) => {
            const val = getCellValue(row, key)
            const str =
                val == null
                    ? ''
                    : props.columns.find((c) => c.key === key)?.format
                      ? (props.columns.find((c) => c.key === key)!.format as (v: unknown, r: Record<string, unknown>) => string)(val, row)
                      : String(val)
            return str.toLowerCase().includes(q)
        })
    })
})

const totalRecords = computed(() => filteredRows.value.length)

function escapeCsvCell(value: string): string {
    if (/[",\n\r]/.test(value)) return `"${value.replace(/"/g, '""')}"`
    return value
}

/**
 * Export CSV: no hay límite de filas en el código.
 * Límites reales:
 * - Longitud máxima de string en JS (V8): ~268 millones de caracteres (2^28 - 1).
 *   Si cada fila tiene ~L caracteres, máximo teórico ≈ 268e6 / L (ej. L=150 → ~1,8M filas).
 * - Memoria: se construye el CSV como un string completo; el navegador puede fallar por RAM
 *   antes del límite de string (p. ej. 1–2M filas con columnas normales suele ser viable).
 * - Descarga: el archivo puede ser grande; depende de disco y del navegador.
 */
/** Export propio: CSV sin formato (PrimeVue DataTable tiene exportCSV() en el ref, pero usamos columnas dinámicas). */
function exportCSV() {
    if (filteredRows.value.length === 0) return
    const cols = props.columns
    const header = cols.map((c) => c.exportLabel ?? c.header).join(',')
    const lines = filteredRows.value.map((row) =>
        cols
            .map((c) => {
                const raw = getCellValue(row, c.key)
                const str = c.exportFormat
                    ? c.exportFormat(raw, row as Record<string, unknown>)
                    : c.format
                      ? c.format(raw, row as Record<string, unknown>)
                      : raw == null ? '' : String(raw)
                return escapeCsvCell(str)
            })
            .join(',')
    )
    const csv = [header, ...lines].join('\r\n')
    const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `detalle-${(props.exportLabel || 'datos').replace(/[^\w\s-]/g, '').replace(/\s+/g, '-')}-${new Date().toISOString().slice(0, 10)}.csv`
    a.click()
    URL.revokeObjectURL(url)
}
</script>
