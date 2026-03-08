<template>
    <div class="space-y-3">
        <!-- Barra: búsqueda + exportar -->
        <div class="flex flex-wrap items-center gap-3">
            <span class="relative p-input-icon-left flex-1 min-w-[200px] max-w-sm">
                <Icon icon="heroicons:magnifying-glass" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-(--th-text-muted)" aria-hidden="true" />
                <InputText
                    v-model="searchText"
                    placeholder="Buscar en concepto, valor, %..."
                    class="w-full pl-9 text-sm"
                    aria-label="Buscar en la tabla de detalle"
                />
            </span>
            <div class="flex items-center gap-2">
                <span v-if="searchText" class="text-xs text-(--th-text-muted)">
                    {{ filteredRows.length }} de {{ rows.length }} coincidencias
                </span>
                <Button
                    label="Exportar CSV"
                    icon="pi pi-download"
                    size="small"
                    outlined
                    :disabled="filteredRows.length === 0"
                    class="text-(--th-text-primary)"
                    @click="exportCSV"
                />
            </div>
        </div>

        <DataTable
            :value="filteredRows"
            size="small"
            striped-rows
            :rows="5"
            :rows-per-page-options="[5, 10, 15, 25, 50]"
            paginator
            paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
            current-page-report-template="{first} – {last} de {totalRecords}"
            responsive-layout="scroll"
            class="detail-datatable text-(--th-text-primary)"
        >
            <Column field="concepto" header="Concepto" sortable class="font-medium" />
            <Column field="valor" header="Valor" sortable>
                <template #body="{ data }">
                    <span class="tabular-nums font-semibold">{{ formatValor(data.valor) }}</span>
                    <span v-if="data.unidad" class="ml-1 text-xs text-(--th-text-muted)">{{ data.unidad }}</span>
                </template>
            </Column>
            <Column v-if="hasPorcentaje" field="porcentaje" header="%" sortable />
            <Column v-if="hasActualizado" field="actualizado" header="Última actualización" sortable class="text-(--th-text-secondary)" />

            <template #empty>
                <div class="py-8 text-center text-sm text-(--th-text-muted)">
                    <Icon icon="heroicons:magnifying-glass" class="mx-auto h-8 w-8 mb-2 opacity-60" aria-hidden="true" />
                    <p>{{ searchText ? 'Ningún registro coincide con la búsqueda.' : 'No hay datos para mostrar.' }}</p>
                </div>
            </template>

            <template #footer>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-(--th-border) pt-3">
                    <p class="text-sm text-(--th-text-secondary)">
                        <span class="font-medium text-(--th-text-primary)">{{ totalRecords }}</span>
                        {{ totalRecords === 1 ? 'registro' : 'registros' }}
                        <span v-if="searchText" class="text-(--th-text-muted)">
                            (filtrados por búsqueda)
                        </span>
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
/** Compatible con DetailTableRow de useUsers */
export interface DetailMetricRow {
    concepto: string
    valor: number | string
    porcentaje?: string
    unidad?: string
    actualizado?: string
}

const props = withDefaults(
    defineProps<{
        rows: DetailMetricRow[]
        hasPorcentaje?: boolean
        hasActualizado?: boolean
        formatValor: (v: number | string) => string
        /** Etiqueta de la métrica (para nombre del archivo al exportar) */
        exportLabel?: string
    }>(),
    { hasPorcentaje: false, hasActualizado: false }
)

const searchText = ref('')

const filteredRows = computed(() => {
    const q = searchText.value.trim().toLowerCase()
    if (!q) return props.rows
    return props.rows.filter((r) => {
        const concepto = (r.concepto ?? '').toLowerCase()
        const valor = String(r.valor ?? '').toLowerCase()
        const porcentaje = (r.porcentaje ?? '').toLowerCase()
        const actualizado = (r.actualizado ?? '').toLowerCase()
        const unidad = (r.unidad ?? '').toLowerCase()
        return [concepto, valor, porcentaje, actualizado, unidad].some((s) => s.includes(q))
    })
})

const totalRecords = computed(() => filteredRows.value.length)

function escapeCsvCell(value: unknown): string {
    const s = value == null ? '' : String(value)
    if (/[",\n\r]/.test(s)) return `"${s.replace(/"/g, '""')}"`
    return s
}

function exportCSV() {
    if (filteredRows.value.length === 0) return
    const cols = ['concepto', 'valor', ...(props.hasPorcentaje ? ['porcentaje' as const] : []), ...(props.hasActualizado ? ['actualizado' as const] : []), 'unidad']
    const header = cols.map((c) => (c === 'concepto' ? 'Concepto' : c === 'valor' ? 'Valor' : c === 'porcentaje' ? '%' : c === 'actualizado' ? 'Última actualización' : 'Unidad')).join(',')
    const lines = filteredRows.value.map((r) => cols.map((c) => escapeCsvCell(r[c])).join(','))
    const csv = [header, ...lines].join('\r\n')
    const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `detalle-${(props.exportLabel || 'metrica').replace(/[^\w\s-]/g, '').replace(/\s+/g, '-')}-${new Date().toISOString().slice(0, 10)}.csv`
    a.click()
    URL.revokeObjectURL(url)
}
</script>
