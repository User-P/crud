<template>
    <AdminLayout title="Charts" subtitle="Galería de ejemplos con Apache ECharts y datos ficticios" :breadcrumbs="[
        { name: 'Dashboard', href: '/dashboard' },
        { name: 'Charts' },
    ]">

        <Panel :value="0" class="mb-5 expanded-panel" toggleable>
            <template #header>
                <div class="p-3 cursor-pointer">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <h3 class="font-semibold text-lg mb-0">Resumen general</h3>
                        </div>
                    </div>
                </div>
            </template>
            <div class="grid grid-cols-6 gap-4 border-black mb-4 rounded-md">

                <div class="col-span-5" v-if="categories.length > 0">
                    <div class="grid grid-cols-1 gap-4">
                        <Panel v-for="[groupName, groupSeries] in groupEntries" :key="groupName" toggleable
                            class="expanded-panel">
                            <template #header>
                                <div class="p-3">
                                    <h3 class="font-semibold text-base mb-0">{{ groupName }}</h3>
                                </div>
                            </template>

                            <LineChart :title="groupName" :series="groupSeries" :labels="categories" />
                        </Panel>
                    </div>
                </div>

                <p v-else>sin datos</p>
            </div>
        </Panel>

        <Panel :value="0" class="mb-5 expanded-panel" toggleable>
            <template #header>
                <div class="p-3 cursor-pointer">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <h3 class="font-semibold text-lg mb-0">Tabla de detalles</h3>
                        </div>
                    </div>
                </div>
            </template>

            <DataTablecustomDetails :data="details" />
        </Panel>

    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useAlerts } from './useAlerts';
import DataTablecustomDetails from './DataTablecustomDetails.vue';
import Panel from 'primevue/panel';
import LineChart from '@/Components/Charts/LineChart.vue';

interface Props {
    cve: any;
}

type TypeItem = { name: string; group: string };

const props = defineProps<Props>();

const dates = ref({ start: '', end: '' });
const { series, categories, details, getAlerts } = useAlerts();

const types = ref<TypeItem[]>([
    { name: 'KEYWORD', group: 'Monaco' },
    { name: 'EVENTO', group: 'Monaco' },
    { name: 'WBA_TRM', group: 'Teramind' },
    { name: 'COMPORTAMIENTO', group: 'Monaco' },

    { name: 'SEVERIDAD ALTA', group: 'Alertas DLP' },
    { name: 'IP ORIGEN DIFERENTE', group: 'Alertas DLP' },
    { name: 'PROTOCOLO EMAIL', group: 'Alertas DLP' },
    { name: 'PROTECCIÓN CÓDIGO FUENTE', group: 'Alertas DLP' },
    { name: 'POLÍTICA DE MENSAJERÍA', group: 'Alertas DLP' },
    { name: 'POLÍTICA DE USO DE USB', group: 'Alertas DLP' },
    { name: 'POLÍTICA DE DATOS PERSONALES', group: 'Alertas DLP' },
    { name: 'POLÍTICA DE DATOS PERSONALES Y TDC', group: 'Alertas DLP' },
    { name: 'POLÍTICA BIN BANCO AZTECA', group: 'Alertas DLP' },
    { name: 'POLÍTICA CUENTAS CLABE O #TDC', group: 'Alertas DLP' },
    { name: 'POLÍTICA LB BLOQUEO', group: 'Alertas DLP' },
    { name: 'ALERTA ONEDRIVE', group: 'Alertas DLP' },
    { name: 'BORRAR ARCHIVOS', group: 'Alertas DLP' },
    { name: 'REMOVABLE', group: 'Alertas DLP' },

    { name: 'TECLEO', group: 'Modelos' },
    { name: 'RESUMEN', group: 'Modelos' },
    { name: 'RESUMEN_TI', group: 'Modelos' },
    { name: 'BUSQUEDA_EMPLEO', group: 'Modelos' },
]);

const selectedCategories = ref<string[]>(types.value.map((t) => t.name));

const typeToGroup = computed<Record<string, string>>(() => {
    const map: Record<string, string> = {};
    for (const t of types.value) map[t.name] = t.group;
    return map;
});

const seriesByGroup = computed<Record<string, any[]>>(() => {
    const out: Record<string, any[]> = {};
    const allSeries = (series.value as any[]) || [];

    for (const s of allSeries) {
        const typeName = s?.name;
        if (!typeName) continue;

        if (!selectedCategories.value.includes(typeName)) continue;

        const group = typeToGroup.value[typeName] ?? 'Otros';
        (out[group] ??= []).push(s);
    }

    return out;
});

const groupEntries = computed(() => Object.entries(seriesByGroup.value));

let debounceTimer: any = null;

watch(
    [dates, selectedCategories],
    () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const selectedTypes = types.value
                .filter((t) => selectedCategories.value.includes(t.name))
                .map((t) => ({ name: t.name, group: t.group }));

            getAlerts(dates.value.start, dates.value.end, props.cve, selectedTypes);
        }, 250);
    },
    { deep: true }
);

watch(
    types,
    (newTypes) => {
        const allowed = newTypes.map((t) => t.name);
        selectedCategories.value = selectedCategories.value.filter((v) =>
            allowed.includes(v)
        );
        if (selectedCategories.value.length === 0 && allowed.length > 0) {
            selectedCategories.value = allowed;
        }
    },
    { deep: true }
);
</script>
