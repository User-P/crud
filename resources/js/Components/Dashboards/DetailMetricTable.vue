<template>
    <div
        class="detail-metric-table rounded-xl border border-[var(--th-border)] bg-[var(--th-input-bg)] backdrop-blur-sm overflow-hidden">
        <div
            class="flex flex-wrap items-center gap-3 border-b border-[var(--th-border)] bg-[var(--th-input-bg)] px-4 py-3">
            <span class="relative flex-1 min-w-[220px] max-w-sm">
                <Icon icon="heroicons:magnifying-glass"
                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[color:var(--th-text-muted)]"
                    aria-hidden="true" />
                <InputText v-model="rawSearchText" :placeholder="searchPlaceholder"
                    class="w-full rounded-lg border-[var(--th-input-border)] bg-[var(--th-input-bg)] pl-9 text-sm placeholder:italic shadow-sm"
                    aria-label="Buscar en la tabla" />
            </span>

            <div class="flex items-center gap-3">
                <span v-if="searchText"
                    class="rounded-full bg-[var(--th-item-active-bg)] px-2.5 py-1 text-xs font-medium text-[color:var(--th-item-active-color)]">
                    {{ totalRecords }} de {{ rows.length }} coincidencias
                </span>
                <span v-if="isProcessing" class="text-xs text-[color:var(--th-text-muted)]">
                    Procesando...
                </span>
                <Button label="Exportar CSV" icon="pi pi-download" size="small" outlined :disabled="totalRecords === 0"
                    severity="secondary" class="shrink-0" :loading="exportingCsv" @click="exportCSV" />
            </div>
        </div>

        <div class="overflow-x-auto max-h-[62vh]">
            <table class="min-w-full text-sm border-separate border-spacing-0">
                <thead class="sticky top-0 z-10 border-b border-[var(--th-border)] bg-[var(--th-input-bg)]">
                    <tr>
                        <th v-for="col in columns" :key="col.key"
                            class="px-4 py-3 text-left text-xs uppercase tracking-wide font-semibold text-[color:var(--th-text-secondary)] border-b border-[var(--th-border)]/80"
                            :class="{
                                'cursor-pointer select-none hover:text-[color:var(--th-item-active-color)]':
                                    col.sortable !== false,
                            }" :aria-sort="col.sortable !== false
                                ? sorting?.key === col.key
                                    ? sorting.desc
                                        ? 'descending'
                                        : 'ascending'
                                    : 'none'
                                : undefined
                                " @click="col.sortable !== false && toggleSort(col.key)">
                            <div class="flex items-center gap-2">
                                <span>{{ col.header }}</span>
                                <span v-if="col.sortable !== false" class="text-xs text-[color:var(--th-text-muted)]">
                                    {{ sortIndicator(col.key) }}
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody v-if="pageRows.length > 0">
                    <tr v-for="(row, rowIndex) in pageRows" :key="rowIndex"
                        class="border-b border-[var(--th-border)]/60 odd:bg-[var(--th-input-bg)] even:bg-[var(--th-item-hover-bg)]/20 hover:bg-[var(--th-item-hover-bg)]/35 transition-colors">
                        <td v-for="col in columns" :key="col.key"
                            class="px-4 py-2.5 text-[color:var(--th-text-primary)]" :class="[
                                col.class,
                                { 'tabular-nums font-semibold': col.numeric },
                            ]">
                            {{ formatCellValue(row, col) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="pageRows.length === 0" class="px-4 py-8">
            <EmptyState :title="searchText
                ? 'Ningún registro coincide con la búsqueda'
                : 'No hay datos para mostrar'
                " :description="searchText
                    ? 'Prueba con otros términos.'
                    : 'Selecciona una métrica o filtra los datos.'
                    " icon="heroicons:magnifying-glass" />
        </div>

        <div
            class="flex flex-wrap items-center justify-between gap-3 border-t border-[var(--th-border)] bg-[var(--th-input-bg)] px-4 py-3">
            <p class="text-sm text-[color:var(--th-text-secondary)]">
                <span class="font-semibold tabular-nums text-[color:var(--th-text-primary)]">{{ totalRecords }}</span>
                {{ totalRecords === 1 ? 'registro' : 'registros' }}
                <span v-if="searchText" class="ml-1 text-[color:var(--th-text-muted)]">(filtrados)</span>
            </p>
            <div class="flex items-center gap-2 text-xs">
                <button type="button"
                    class="rounded-md border border-[var(--th-border)] px-2 py-1 text-[color:var(--th-text-secondary)] disabled:opacity-50"
                    :disabled="pageIndex === 0 || isProcessing" @click="pageIndex = Math.max(0, pageIndex - 1)">
                    Anterior
                </button>
                <span class="text-[color:var(--th-text-muted)]">
                    Página {{ currentPage }} de {{ totalPages }}
                </span>
                <button type="button"
                    class="rounded-md border border-[var(--th-border)] px-2 py-1 text-[color:var(--th-text-secondary)] disabled:opacity-50"
                    :disabled="pageIndex >= totalPages - 1 || isProcessing"
                    @click="pageIndex = Math.min(totalPages - 1, pageIndex + 1)">
                    Siguiente
                </button>
                <label class="ml-2 text-[color:var(--th-text-muted)]">Filas:</label>
                <select v-model.number="pageSize"
                    class="rounded-md border border-[var(--th-border)] bg-[var(--th-input-bg)] px-2 py-1 text-[color:var(--th-text-secondary)]"
                    :disabled="isProcessing">
                    <option v-for="opt in rowsPerPageOptions" :key="opt" :value="opt">
                        {{ opt }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onBeforeUnmount, toRaw } from 'vue';
import { Icon } from '@iconify/vue';
import EmptyState from '@/Components/EmptyState.vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';

/** Configuración de una columna (datos dinámicos) */
export interface DetailMetricColumn<T = Record<string, unknown>> {
    /** Clave del campo en cada fila */
    key: string;
    /** Encabezado en la tabla y por defecto en CSV */
    header: string;
    /** Etiqueta para exportación CSV (por defecto: header) */
    exportLabel?: string;
    /** Si la columna es ordenable */
    sortable?: boolean;
    /** Formatear valor para mostrar (y para CSV si no hay exportFormat) */
    format?: (value: unknown, row: T) => string;
    /** Formatear para CSV (por defecto: format o String(value)) */
    exportFormat?: (value: unknown, row: T) => string;
    /** Clase CSS para las celdas */
    class?: string;
    /** Aplicar tabular-nums y font-semibold (para números) */
    numeric?: boolean;
}

const props = withDefaults(
    defineProps<{
        /** Filas genéricas (cualquier objeto con claves que coincidan con columns[].key) */
        rows: Record<string, unknown>[];
        /** Definición de columnas */
        columns: DetailMetricColumn[];
        /** Placeholder del campo de búsqueda */
        searchPlaceholder?: string;
        /** Etiqueta para el nombre del archivo al exportar */
        exportLabel?: string;
        /** Filas por página por defecto */
        rowsPerPage?: number;
        /** Opciones del selector de filas por página */
        rowsPerPageOptions?: number[];
        /**
         * Si > 0, export CSV se divide en varios archivos de hasta esta cantidad
         * de filas cada uno y se empaqueta en un único ZIP.
         */
        maxRowsPerCsvFile?: number;
    }>(),
    {
        searchPlaceholder: 'Buscar en la tabla…',
        rowsPerPage: 10,
        rowsPerPageOptions: () => [10, 15, 25, 50, 100],
        maxRowsPerCsvFile: 0,
    }
);

// ─── Estado reactivo ───────────────────────────────────────────────────────────

const rawSearchText = ref('');
const searchText = ref('');
const exportingCsv = ref(false);
const isProcessing = ref(false);
const pageIndex = ref(0);
const pageSize = ref(props.rowsPerPage);
const workerTotal = ref(props.rows.length);
const workerPageIndexes = ref<number[]>([]);
const sorting = ref<{ key: string; desc: boolean } | null>(null);
const datasetVersion = ref(0);

// ─── Debounce búsqueda ─────────────────────────────────────────────────────────

let searchDebounce: ReturnType<typeof setTimeout> | undefined;

watch(
    () => rawSearchText.value,
    (value) => {
        if (searchDebounce) clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            searchText.value = value.trim();
            pageIndex.value = 0;
        }, 220);
    }
);

// ─── Helpers de celda ──────────────────────────────────────────────────────────

function getCellValue(row: Record<string, unknown>, key: string): unknown {
    return row[key];
}

function formatCellValue(
    row: Record<string, unknown>,
    col: DetailMetricColumn
): string {
    const raw = getCellValue(row, col.key);
    if (col.format) return col.format(raw, row as Record<string, unknown>);
    return raw == null ? '' : String(raw);
}

// ─── Computed de paginación ────────────────────────────────────────────────────

const pageRows = computed<Record<string, unknown>[]>(() => {
    if (workerPageIndexes.value.length === 0) return [];
    return workerPageIndexes.value.map((idx) => props.rows[idx]);
});

const totalRecords = computed(() => workerTotal.value);

// FIX: guard contra pageSize === 0 para evitar Infinity en totalPages
const totalPages = computed(() =>
    Math.max(1, Math.ceil(totalRecords.value / Math.max(1, pageSize.value)))
);

const currentPage = computed(() =>
    Math.min(pageIndex.value + 1, totalPages.value)
);

// ─── Web Worker ────────────────────────────────────────────────────────────────

/**
 * FIX parseMaybeNumber: detecta si el valor usa formato anglosajón (ej. "1.5")
 * antes de aplicar normalización es-MX, evitando que "1.5" → "15".
 *
 * FIX exportAll: modo sin paginación usado por exportCSV para delegar
 * filtrado/sort al worker en lugar de repetirlos en el hilo principal.
 */
const WORKER_SOURCE = `
  let dataset = [];
  let columnKeys = [];
  let activeDatasetVersion = 0;

  const parseMaybeNumber = (value) => {
    if (typeof value === 'number' && Number.isFinite(value)) return value;
    if (typeof value !== 'string') return null;
    const trimmed = value.replace(/[%,$\\s]/g, '');
    if (!trimmed) return null;

    const dotCount = (trimmed.match(/\\./g) || []).length;
    const commaCount = (trimmed.match(/,/g) || []).length;

    let normalized;
    if (dotCount === 1 && commaCount === 0) {
      // Formato anglosajón simple ("1.5") — no transformar
      normalized = trimmed;
    } else if (dotCount === 1 && commaCount === 1 && trimmed.indexOf(',') < trimmed.indexOf('.')) {
      // Formato anglosajón con miles ("1,234.56") — quitar coma de miles
      normalized = trimmed.replace(/,/g, '');
    } else {
      // Formato es-MX ("1.234,56") — quitar puntos de miles, coma → punto decimal
      normalized = trimmed.replace(/\\./g, '').replace(',', '.');
    }

    const parsed = Number(normalized);
    return Number.isFinite(parsed) ? parsed : null;
  };

  const parseMaybeDate = (value) => {
    if (value instanceof Date) return value.getTime();
    if (typeof value !== 'string') return null;
    const ts = Date.parse(value);
    return Number.isFinite(ts) ? ts : null;
  };

  self.onmessage = (event) => {
    const payload = event.data;

    if (payload.type === 'init') {
      dataset = Array.isArray(payload.rows) ? payload.rows : [];
      columnKeys = Array.isArray(payload.columnKeys) ? payload.columnKeys : [];
      activeDatasetVersion = Number(payload.datasetVersion || 0);
      return;
    }

    if (payload.type !== 'process') return;

    const q = (payload.query || '').toLowerCase();
    const sort = payload.sorting;
    const pageIndex = Number(payload.pageIndex ?? 0);
    const pageSize = Number(payload.pageSize || 10);
    const exportAll = !!payload.exportAll;

    // ── Filtro ──
    let indexes = [];
    for (let i = 0; i < dataset.length; i++) {
      if (!q) { indexes.push(i); continue; }
      const row = dataset[i] || {};
      let match = false;
      for (let j = 0; j < columnKeys.length; j++) {
        const v = row[columnKeys[j]];
        if (String(v == null ? '' : v).toLowerCase().includes(q)) {
          match = true;
          break;
        }
      }
      if (match) indexes.push(i);
    }

    // ── Sort ──
    if (sort && sort.key) {
      indexes.sort((a, b) => {
        const av = dataset[a]?.[sort.key];
        const bv = dataset[b]?.[sort.key];
        if (av == null && bv == null) return 0;
        if (av == null) return sort.desc ? 1 : -1;
        if (bv == null) return sort.desc ? -1 : 1;

        const nav = parseMaybeNumber(av);
        const nbv = parseMaybeNumber(bv);
        if (nav !== null && nbv !== null) return sort.desc ? nbv - nav : nav - nbv;

        const dav = parseMaybeDate(av);
        const dbv = parseMaybeDate(bv);
        if (dav !== null && dbv !== null) return sort.desc ? dbv - dav : dav - dbv;

        const cmp = String(av ?? '').localeCompare(String(bv ?? ''), 'es', {
          numeric: true, sensitivity: 'base', ignorePunctuation: true,
        });
        return sort.desc ? -cmp : cmp;
      });
    }

    const total = indexes.length;

    // ── Paginación (omitida si exportAll) ──
    let pageIndexes;
    if (exportAll) {
      pageIndexes = indexes;
    } else {
      const start = pageIndex * pageSize;
      const end = Math.min(start + pageSize, total);
      pageIndexes = start >= total ? [] : indexes.slice(start, end);
    }

    self.postMessage({
      reqId: payload.reqId,
      datasetVersion: activeDatasetVersion,
      total,
      pageIndexes,
      exportAll,
    });
  };
`;

let worker: Worker | null = null;
let requestId = 0;

function ensureWorker(): Worker {
    if (worker) return worker;
    const blob = new Blob([WORKER_SOURCE], { type: 'application/javascript' });
    worker = new Worker(URL.createObjectURL(blob));
    return worker;
}

function initWorkerData() {
    datasetVersion.value += 1;
    ensureWorker().postMessage({
        type: 'init',
        rows: toRaw(props.rows),
        columnKeys: props.columns.map((c) => c.key),
        datasetVersion: datasetVersion.value,
    });
}

function processRows() {
    isProcessing.value = true;
    requestId += 1;
    ensureWorker().postMessage({
        type: 'process',
        reqId: requestId,
        datasetVersion: datasetVersion.value,
        query: searchText.value,
        sorting: sorting.value
            ? { key: sorting.value.key, desc: sorting.value.desc }
            : null,
        pageIndex: pageIndex.value,
        pageSize: pageSize.value,
    });
}

// FIX: onmessage registrado en onMounted, no en tiempo de setup.
// Evita problemas con SSR, hot-reload y doble montado del componente.
onMounted(() => {
    const w = ensureWorker();
    w.onmessage = (
        event: MessageEvent<{
            reqId: number;
            datasetVersion: number;
            total: number;
            pageIndexes: number[];
            exportAll?: boolean;
        }>
    ) => {
        const payload = event.data;
        if (payload.reqId !== requestId) return;
        if (payload.datasetVersion !== datasetVersion.value) return;

        // Respuesta para exportCSV — notificar a la Promise pendiente
        if (payload.exportAll) {
            pendingExportResolve?.(payload.pageIndexes);
            pendingExportResolve = null;
            return;
        }

        workerTotal.value = payload.total;
        workerPageIndexes.value = payload.pageIndexes;
        isProcessing.value = false;
    };
});

// ─── Sort ──────────────────────────────────────────────────────────────────────

function toggleSort(key: string) {
    if (sorting.value?.key !== key) {
        sorting.value = { key, desc: false };
    } else if (!sorting.value.desc) {
        sorting.value = { key, desc: true };
    } else {
        sorting.value = null;
    }
    pageIndex.value = 0;
}

function sortIndicator(key: string): string {
    if (sorting.value?.key !== key) return '';
    return sorting.value.desc ? '↓' : '↑';
}

// ─── Watchers ─────────────────────────────────────────────────────────────────

watch(
    () => [props.rows, props.columns] as const,
    () => {
        rawSearchText.value = '';
        searchText.value = '';
        sorting.value = null;
        pageIndex.value = 0;
        workerPageIndexes.value = [];
        workerTotal.value = props.rows.length;
        initWorkerData();
        processRows();
    },
    { immediate: true }
);

watch(
    () => [searchText.value, pageIndex.value, pageSize.value, sorting.value] as const,
    () => { processRows(); }
);

watch(
    () => totalPages.value,
    (pages) => {
        if (pageIndex.value > pages - 1) pageIndex.value = Math.max(0, pages - 1);
    }
);

// ─── Cleanup ──────────────────────────────────────────────────────────────────

onBeforeUnmount(() => {
    if (searchDebounce) clearTimeout(searchDebounce);
    worker?.terminate();
    worker = null;
});

// ─── Export CSV ───────────────────────────────────────────────────────────────

/**
 * FIX: en lugar de re-filtrar y re-ordenar en el hilo principal, se le pide
 * al worker todos los índices (exportAll: true). Esto garantiza:
 * 1. El orden exportado es idéntico al orden visible en la tabla.
 * 2. El hilo principal no se bloquea con datasets grandes.
 */
let pendingExportResolve: ((indexes: number[]) => void) | null = null;

function requestExportIndexes(): Promise<number[]> {
    return new Promise((resolve) => {
        pendingExportResolve = resolve;
        requestId += 1;
        ensureWorker().postMessage({
            type: 'process',
            reqId: requestId,
            datasetVersion: datasetVersion.value,
            query: searchText.value,
            sorting: sorting.value
                ? { key: sorting.value.key, desc: sorting.value.desc }
                : null,
            pageIndex: 0,
            pageSize: 0,
            exportAll: true,
        });
    });
}

function escapeCsvCell(value: string): string {
    if (/[",\n\r]/.test(value)) return `"${value.replace(/"/g, '""')}"`;
    return value;
}

function buildCsvChunk(chunkRows: Record<string, unknown>[]): string {
    const cols = props.columns;
    const header = cols.map((c) => escapeCsvCell(c.exportLabel ?? c.header)).join(',');
    const lines = chunkRows.map((row) =>
        cols
            .map((c) => {
                const raw = getCellValue(row, c.key);
                const str = c.exportFormat
                    ? c.exportFormat(raw, row as Record<string, unknown>)
                    : c.format
                        ? c.format(raw, row as Record<string, unknown>)
                        : raw == null
                            ? ''
                            : String(raw);
                return escapeCsvCell(str);
            })
            .join(',')
    );
    return [header, ...lines].join('\r\n');
}

function downloadBlob(blob: Blob, filename: string) {
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
}

async function exportCSV() {
    if (totalRecords.value === 0) return;
    exportingCsv.value = true;

    try {
        const indexes = await requestExportIndexes();
        const rows = indexes.map((i) => props.rows[i]);

        const maxPerFile = props.maxRowsPerCsvFile ?? 0;
        const baseName = `detalle-${(props.exportLabel || 'datos')
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')}-${new Date().toISOString().slice(0, 10)}`;

        if (maxPerFile > 0 && rows.length > maxPerFile) {
            const JSZip = (await import('jszip')).default;
            const zip = new JSZip();
            const totalParts = Math.ceil(rows.length / maxPerFile);
            const partNames: string[] = [];

            for (let part = 0; part < totalParts; part++) {
                const start = part * maxPerFile;
                const chunk = rows.slice(start, start + maxPerFile);
                const csv = buildCsvChunk(chunk);
                const partName = `parte-${part + 1}-de-${totalParts}.csv`;
                partNames.push(partName);
                zip.file(partName, '\ufeff' + csv, { createFolders: false });
            }

            const leeme = [
                'Exportación en varias partes',
                '────────────────────────────',
                `Total de registros: ${rows.length.toLocaleString('es')}`,
                `Partes: ${totalParts}`,
                '',
                'Archivos incluidos (en orden):',
                ...partNames.map((name, i) => `  ${i + 1}. ${name}`),
                '',
                'Cada archivo tiene cabecera. Para unir en Excel/LibreOffice:',
                'abrir el primero y luego insertar las filas de parte-2, parte-3, etc.',
            ].join('\r\n');
            zip.file('LEEME.txt', leeme, { createFolders: false });

            const blob = await zip.generateAsync({ type: 'blob' });
            downloadBlob(blob, `${baseName}.zip`);
        } else {
            const csv = buildCsvChunk(rows);
            downloadBlob(
                new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8' }),
                `${baseName}.csv`
            );
        }
    } finally {
        exportingCsv.value = false;
    }
}
</script>
