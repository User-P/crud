<template>
    <DataTable resizableColumns columnResizeMode="fit" size="small" :rowsPerPageOptions="[20, 50, 100]" stripedRows
        showGridlines scrollable v-model:expandedRows="expandedRows" :value="data" dataKey="uuid" paginator :rows="20"
        responsive-layout="scroll" :filters="filters" filterDisplay="menu">
        <template #header>
            <div class="flex justify-between items-center p-2">
                <InputText v-model="filters['global'].value" placeholder="Buscar ..." class="w-full md:w-64" />
            </div>
        </template>

        <Column expander style="width: 3rem" />
        <Column field="name" header="Título" filter filterPlaceholder="Buscar" />

        <Column v-for="col in getMainColumns(data)" :key="col" :field="col" :header="formatHeader(col)" sortable filter
            :filterPlaceholder="`Buscar ${formatHeader(col)}`" />

        <template #expansion="slotProps">
            <div class="rounded-lg p-4 mt-2 shadow-inner">
                <DataTable resizableColumns columnResizeMode="fit" size="small" :rowsPerPageOptions="[20, 50, 100]"
                    stripedRows showGridlines scrollable :value="slotProps.data.details" paginator :rows="20"
                    :filters="detailFilters" filterDisplay="menu" class="border border-gray-200 rounded-lg">
                    <template #header>
                        <div class="flex justify-between bg-gray-50 p-2"
                            style="background: #9dd3a6; border-radius: 5px">
                            <h5 class="text-lg font-semibold">
                                Detalles de {{ slotProps.data.name }}
                            </h5>
                            <InputText v-model="detailFilters['global'].value" placeholder="Buscar en detalles..."
                                class="w-full md:w-64" />
                        </div>
                    </template>

                    <Column v-for="cfg in getDetailColumns(slotProps.data.details)" :key="cfg.key" :field="cfg.field"
                        :header="cfg.header" sortable filter :filterPlaceholder="`Buscar ${cfg.header}`" />
                </DataTable>

                <div class="mt-4">
                    <LineChart v-if="getDateChart(slotProps.data.details)"
                        :title="`Tendencia por fecha — ${slotProps.data.name}`"
                        :labels="getDateChart(slotProps.data.details)!.labels"
                        :series="getDateChart(slotProps.data.details)!.series" smooth area />
                </div>
            </div>
        </template>
        <template #empty>
            <p>sin datos</p>
        </template>
    </DataTable>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Column, DataTable, InputText } from 'primevue';
import LineChart from '@/Components/Charts/LineChart.vue';

type Slice = {
    value: number;
    name: string;
    details?: any[];
    [key: string]: any;
};

interface Props {
    data: Slice[];
}
defineProps<Props>();

const expandedRows = ref<any[]>([]);
const filters = ref({
    global: { value: null as string | null, matchMode: 'contains' },
});
const detailFilters = ref({
    global: { value: null as string | null, matchMode: 'contains' },
});

const getMainColumns = (rows: any[]) => {
    if (!rows || rows.length === 0) return [];
    const allKeys = new Set<string>();
    rows.forEach((row) => Object.keys(row).forEach((key) => allKeys.add(key)));
    ['uuid', 'details', 'activo', 'inactivo', 'name'].forEach((k) =>
        allKeys.delete(k)
    );
    return Array.from(allKeys);
};

type DetailColumnConfig = {
    key: string;
    header: string;
    candidates: string[];
    field?: string;
};

const detail_column_config: DetailColumnConfig[] = [
    {
        key: 'cve_alerta',
        header: 'ID',
        candidates: ['cve_alerta', 'codigo_transaccion'],
    },
    {
        key: 'estatus_empleado',
        header: 'Estatus Empleado',
        candidates: ['estatus_empleado'],
    },
    {
        key: 'nm_regla',
        header: 'Regla',
        candidates: ['nm_reglas', 'regla', 'nm_comportamiento'],
    },
    {
        key: 'conteo',
        header: 'Conteo',
        candidates: ['conteo_eventos', 'conteo_archivos', 'conteo'],
    },
    { key: 'tipo_alerta', header: 'Tipo Alerta', candidates: ['tipo_alerta'] },
    {
        key: 'fecha',
        header: 'Fecha',
        candidates: [
            'fch_evento',
            'fch_incidente',
            'fch_proceso_fecha',
            'fecha_recepcion',
            'fch_carga',
            'audit_fch_carga',
        ],
    },
];

const collectKeysPresent = (details: any[]) => {
    const keysPresent = new Set<string>();
    for (const item of details ?? []) {
        Object.keys(item ?? {}).forEach((k) => keysPresent.add(k));
    }
    return keysPresent;
};

const pickFieldForConfig = (
    keysPresent: Set<string>,
    cfg: DetailColumnConfig
) => {
    return cfg.candidates.find((c) => keysPresent.has(c));
};

const getDetailColumns = (details: any[]) => {
    if (!details || details.length === 0) return [];
    const keysPresent = collectKeysPresent(details);
    const visible = detail_column_config
        .map((cfg) => ({ ...cfg, field: pickFieldForConfig(keysPresent, cfg) }))
        .filter((cfg) => !!cfg.field);
    return visible;
};

const formatHeader = (key: string) =>
    key.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());

const valueCandidates = ['conteo_eventos', 'conteo_archivos', 'conteo'];
const dateCandidates = [
    'fch_evento',
    'fch_incidente',
    'fch_proceso_fecha',
    'fecha_recepcion',
    'fch_carga',
    'audit_fch_carga',
];

const pickFirstPresent = (details: any[], candidates: string[]) => {
    const set = new Set<string>();
    for (const d of details ?? []) {
        Object.keys(d ?? {}).forEach((k) => set.add(k));
    }
    return candidates.find((c) => set.has(c));
};

const getDateChart = (details: any[]) => {
    if (!details || details.length === 0) return null;

    const dateKey = pickFirstPresent(details, dateCandidates);
    if (!dateKey) return null;

    const valueKey = pickFirstPresent(details, valueCandidates);
    const hasAnyValueField = !!valueKey; // ¿Hay campo de conteo en alguna fila?

    const totals = new Map<string, number>();
    for (const d of details) {
        const day = d?.[dateKey];
        if (!day) continue;

        const val = hasAnyValueField ? Number(d?.[valueKey!] ?? 0) : 1;

        totals.set(day, (totals.get(day) ?? 0) + (isFinite(val) ? val : 0));
    }

    const ordered = [...totals.entries()].sort((a, b) =>
        a[0] < b[0] ? -1 : a[0] > b[0] ? 1 : 0
    );
    const labels = ordered.map(([day]) => day);
    const data = ordered.map(([, total]) => total);

    return {
        labels,
        series: [{ name: hasAnyValueField ? 'Suma de conteo' : 'Registros', data }],
    };
};
</script>

<style scoped>
.card {
    width: 100%;
}

.mt-4 :deep(.echarts) {
    min-height: 280px;
}
</style>
