<template>
    <div class="card border-2 border-black overflow-auto" v-if="data.length > 0">
        <DataTable resizableColumns columnResizeMode="fit" size="small" :rowsPerPageOptions="[20, 50, 100]"
            style="font-family: 'Roboto Condensed'" stripedRows showGridlines scrollable
            v-model:expandedRows="expandedRows" :value="data" dataKey="uuid" paginator :rows="20"
            responsive-layout="scroll" :filters="filters" filterDisplay="menu">
            <template #header>
                <div class="flex justify-between items-center p-2">
                    <InputText v-model="filters['global'].value" placeholder="Buscar ..." class="w-full md:w-64" />
                </div>
            </template>

            <Column expander style="width: 3rem" />

            <Column field="name" header="Nombre" filter filterPlaceholder="Buscar" />

            <Column v-for="col in getMainColumns(data)" :key="col" :field="col" :header="formatHeader(col)" sortable
                filter :filterPlaceholder="`Buscar ${formatHeader(col)}`" />

            <template #expansion="slotProps">
                <div class="rounded-lg p-4 mt-2 shadow-inner">
                    <DataTable resizableColumns columnResizeMode="fit" size="small" :rowsPerPageOptions="[20, 50, 100]"
                        style="font-family: 'Roboto Condensed'" stripedRows showGridlines scrollable
                        :value="slotProps.data.details" paginator :rows="20" :filters="detailFilters"
                        filterDisplay="menu" class="border border-gray-200 rounded-lg">
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

                        <Column v-for="col in getDetailColumns(slotProps.data.details)" :key="col" :field="col"
                            :header="formatHeader(col)" sortable filter
                            :filterPlaceholder="`Buscar ${formatHeader(col)}`" />
                    </DataTable>
                </div>
            </template>
            <template #empty>
                <div class="p-3 m-3 alert text-center border rounded flex flex-column align-items-center justify-center min-h-[300px]"
                    style="background-color: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                    <i class="pi pi-info-circle" style="font-size: 3rem; margin-bottom: 10px;"></i>
                    <span style="font-size: 1.5rem; color: #333;">No hay datos disponibles.</span>
                </div>
            </template>
        </DataTable>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Column, DataTable, InputText } from 'primevue';

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
const filters = ref({ global: { value: null, matchMode: 'contains' } });
const detailFilters = ref({ global: { value: null, matchMode: 'contains' } });

const getMainColumns = (data: any[]) => {
    if (!data || data.length === 0) return [];
    const keys = Object.keys(data[0]);
    return keys.filter(
        (key) => !['name', 'details', 'activo', 'inactivo', 'uuid'].includes(key)
    );
};

const getDetailColumns = (details: any[]) => {
    if (!details || details.length === 0) return [];
    return Object.keys(details[0]);
};

const formatHeader = (key: string) => {
    return key.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
};
</script>

<style scoped>
.card {
    width: 100%;
}
</style>
