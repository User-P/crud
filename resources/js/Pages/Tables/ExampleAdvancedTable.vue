<template>
    <div class="space-y-4">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <Button type="button" class="p-button-sm p-button-secondary" label="Regenerar datos"
                    @click="emit('regenerate')" />
                <Button type="button" class="p-button-sm p-button-info" label="Log Seleccionados"
                    @click="logSelected" />
            </div>
            <div class="w-full sm:w-auto">
                <label for="advanced-global-search" class="sr-only">Buscar</label>
                <InputText id="advanced-global-search" v-model="globalSearch" type="search"
                    class="w-full min-w-[12rem] sm:w-52 lg:w-64 text-sm p-2" placeholder="Buscar..." />
            </div>
        </div>

        <div v-if="tableApi" class="rounded-lg border border-gray-200/80 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="text-sm font-medium text-gray-700">Filtros</div>
                    <div class="flex items-center gap-3 text-xs text-gray-500">
                        <span>Filtrados: {{ filteredTotal }}</span>
                        <span v-if="totalRows !== filteredTotal">Total: {{ totalRows }}</span>
                        <span v-if="selectedCount">Seleccionados: {{ selectedCount }}</span>
                    </div>
                </div>

                <div v-if="filtersOpen" class="min-w-0">
                    <TableFiltersAdvanced :table="tableApi" />
                </div>


                <div class="flex justify-end">
                    <Button type="button" class="p-button-sm p-button-text"
                        :label="filtersOpen ? 'Ocultar filtros' : 'Mostrar filtros'"
                        @click="filtersOpen = !filtersOpen" />
                </div>
            </div>
        </div>

        <TanStackTable ref="tableRef" :data="data" :columns="columns" :loading="loading" enable-sorting
            enable-pagination enable-global-filter :show-global-filter="false" :show-selected-count="false"
            enable-column-filters selectable :page-size="10" show-sticky-header skeleton-loading :skeleton-rows="8"
            row-click="drawer" drawer-title="Detalle del registro" @update:cell="(e) => emit('update:cell', e)"
            @open-drawer="onOpenDrawer">
            <template #pagination="{ table }">
                <TablePagination :table="table" :page-size-options="[5, 10, 25]" />
            </template>

            <template #expanded-row="{ original }">
                <div class="text-xs font-mono bg-gray-100 rounded p-3 overflow-auto max-h-48">
                    <pre>{{ JSON.stringify(original, null, 2) }}</pre>
                </div>
            </template>



            <template #row-actions="{ original }">
                <div class="flex items-center gap-2">
                    <Button type="button" class="p-button-sm" label="Editar"
                        @click="() => emit('edit-row', original)" />
                    <Button type="button" class="p-button-sm p-button-danger" label="Eliminar"
                        @click="() => emit('delete-row', original)" />
                </div>
            </template>
        </TanStackTable>

        <PrimeSidebar v-model:visible="sidebarVisible" position="right" class="w-full sm:max-w-lg lg:max-w-xl"
            :modal="true" :dismissable="true" @hide="sidebarVisible = false">
            <template #header>
                <span class="font-semibold">{{ sidebarTitle || 'Detalle' }}</span>
            </template>
            <div v-if="sidebarRow" class="p-2">
                <div class="space-y-4">
                    <dl class="grid grid-cols-1 gap-2 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">ID</dt>
                            <dd class="mt-0.5">{{ sidebarRow.id }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Nombre</dt>
                            <dd class="mt-0.5">{{ sidebarRow.name }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Email</dt>
                            <dd class="mt-0.5">{{ sidebarRow.email }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Estado</dt>
                            <dd class="mt-0.5">{{ sidebarRow.status }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Departamento</dt>
                            <dd class="mt-0.5">{{ sidebarRow.department }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Edad</dt>
                            <dd class="mt-0.5">{{ sidebarRow.age }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Score</dt>
                            <dd class="mt-0.5">{{ sidebarRow.score }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Ciudad</dt>
                            <dd class="mt-0.5">{{ sidebarRow.city }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Alta</dt>
                            <dd class="mt-0.5">{{ sidebarRow.createdAt }}</dd>
                        </div>
                    </dl>
                    <Button type="button" class="p-button-sm" label="Cerrar" @click="sidebarVisible = false" />
                </div>
            </div>
        </PrimeSidebar>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import TanStackTable from '@/Components/Tables/TanStackTable.vue'
import TablePagination from '@/Components/Tables/TablePagination.vue'
import TableFiltersAdvanced from '@/Components/Tables/TableFiltersAdvanced.vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import PrimeSidebar from 'primevue/sidebar'

withDefaults(defineProps<{ data: any[]; columns: ColumnDef<any>[]; loading?: boolean }>(), {
    loading: false,
})

const emit = defineEmits<{
    (e: 'update:cell', payload: { rowId: string; columnId: string; value: unknown; oldValue: unknown; original: any }): void
    (e: 'regenerate'): void
    (e: 'edit-row', payload: any): void
    (e: 'delete-row', payload: any): void
}>()

const filtersOpen = ref(true)
const tableRef = ref<any>(null)
const tableApi = computed(() => tableRef.value?.table)
const filteredTotal = computed(() => tableRef.value?.filteredTotal?.value ?? 0)
const totalRows = computed(() => tableRef.value?.totalRows?.value ?? 0)
const selectedCount = computed(() => tableRef.value?.selectedCount?.value ?? 0)

// Local drawer state - listen to `open-drawer` emitted by the table and show a local PrimeSidebar
const sidebarVisible = ref(false)
const sidebarRow = ref<any | null>(null)
const sidebarTitle = ref<string | undefined>(undefined)
function onOpenDrawer(payload: { row: any; original: any; title?: string }) {
    sidebarRow.value = payload.original ?? payload.row?.original ?? payload.row
    sidebarTitle.value = payload.title
    sidebarVisible.value = true
}

const globalSearch = computed({
    get: () => tableApi.value?.getState().globalFilter ?? '',
    set: (value: string) => {
        tableApi.value?.setGlobalFilter(value || '')
    },
})

const logSelected = () => {
    if (!tableApi.value) return
    console.log(tableApi.value.getSelectedRowModel().rows)
}
</script>
