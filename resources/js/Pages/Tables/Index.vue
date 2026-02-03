<template>
    <AdminLayout title="Tablas" subtitle="Prueba de TanStack Table (base reutilizable)">
        <TanStackTable :data="data" :columns="columns" :loading="loading" enable-sorting enable-pagination
            enable-global-filter enable-column-filters selectable :page-size="10" show-sticky-header skeleton-loading
            :skeleton-rows="8" row-click="drawer" drawer-title="Detalle del registro" @update:cell="onUpdateCell">
            <template #toolbar="{ table }">
                <div class="flex flex-col gap-3 w-full min-w-0">
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <Button type="button" class="p-button-sm p-button-secondary" label="Regenerar datos"
                            @click="regenerate" />
                        <Button type="button" class="p-button-sm p-button-info" label="Log Seleccionados"
                            @click="logSelected(table)" />
                    </div>
                    <TableFiltersAdvanced :table="table" />
                </div>
            </template>
            <template #pagination="{ table }">
                <TablePagination :table="table" :page-size-options="[5, 10, 25]" />
            </template>
            <template #expanded-row="{ original }">
                <div class="text-xs font-mono bg-gray-100 rounded p-3 overflow-auto max-h-48">
                    <pre>{{ JSON.stringify(original, null, 2) }}</pre>
                </div>
            </template>
            <template #drawer="{ original, close }">
                <div class="space-y-4">
                    <dl class="grid grid-cols-1 gap-2 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">ID</dt>
                            <dd class="mt-0.5">{{ original.id }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Nombre</dt>
                            <dd class="mt-0.5">{{ original.name }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Email</dt>
                            <dd class="mt-0.5">{{ original.email }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Estado</dt>
                            <dd class="mt-0.5">{{ original.status }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Departamento</dt>
                            <dd class="mt-0.5">{{ original.department }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Edad</dt>
                            <dd class="mt-0.5">{{ original.age }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Score</dt>
                            <dd class="mt-0.5">{{ original.score }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Ciudad</dt>
                            <dd class="mt-0.5">{{ original.city }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Alta</dt>
                            <dd class="mt-0.5">{{ original.createdAt }}</dd>
                        </div>
                    </dl>
                    <Button type="button" class="p-button-sm" label="Cerrar" @click="close" />
                </div>
            </template>
            <template #row-actions="{ original }">
                <div class="flex items-center gap-2">
                    <Button type="button" class="p-button-sm" label="Editar" @click="editRow(original)" />
                    <Button type="button" class="p-button-sm p-button-danger" label="Eliminar"
                        @click="deleteRow(original)" />
                </div>
            </template>
        </TanStackTable>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import TanStackTable from '@/Components/Tables/TanStackTable.vue'
import TablePagination from '@/Components/Tables/TablePagination.vue'
import TableFiltersAdvanced from '@/Components/Tables/TableFiltersAdvanced.vue'
import { faker } from '@faker-js/faker'
import { type ColumnDef, type Table } from '@tanstack/vue-table'
import Button from 'primevue/button'

type BasicRow = {
    id: string
    name: string
    email: string
    status: 'Activo' | 'Inactivo'
    age: number
    score: number
    department: 'Ventas' | 'Marketing' | 'Soporte' | 'Finanzas' | 'TI'
    city: string
    createdAt: string
}

const DEPARTMENTS: BasicRow['department'][] = ['Ventas', 'Marketing', 'Soporte', 'Finanzas', 'TI']
const STATUS_OPTIONS: BasicRow['status'][] = ['Activo', 'Inactivo']

const makeFakeRows = (count = 10): BasicRow[] =>
    Array.from({ length: count }, () => ({
        id: faker.string.uuid(),
        name: faker.person.fullName(),
        email: faker.internet.email(),
        status: faker.helpers.arrayElement(STATUS_OPTIONS),
        age: faker.number.int({ min: 18, max: 65 }),
        score: faker.number.int({ min: 0, max: 100 }),
        department: faker.helpers.arrayElement(DEPARTMENTS),
        city: faker.location.city(),
        createdAt: faker.date.past({ years: 1 }).toISOString().slice(0, 10),
    }))

const data = ref<BasicRow[]>(makeFakeRows(100))
const loading = ref(false)

const regenerate = async () => {
    loading.value = true
    await new Promise((resolve) => setTimeout(resolve, 350))
    data.value = makeFakeRows(100)
    loading.value = false
}

const logSelected = (table: Table<BasicRow>) => {
    console.log(table.getSelectedRowModel().rows)
}

const editRow = (row: BasicRow) => {
    console.log('Editar', row)
}

const deleteRow = (row: BasicRow) => {
    console.log('Eliminar', row)
}

function onUpdateCell(payload: { rowId: string; columnId: string; value: unknown; oldValue: unknown; original: BasicRow }) {
    const { columnId, value, oldValue, original } = payload
    if (Object.is(value, oldValue)) return
    const index = data.value.findIndex((r) => r.id === original.id)
    if (index === -1) return
    const updateRow = (patch: Partial<BasicRow>) => {
        data.value = data.value.map((r, i) => (i === index ? { ...r, ...patch } : r))
    }

    if (columnId === 'status' && (value === 'Activo' || value === 'Inactivo')) {
        updateRow({ status: value })
        return
    }
    if (columnId === 'department' && typeof value === 'string') {
        updateRow({ department: value as BasicRow['department'] })
        return
    }
    if (columnId === 'name' && typeof value === 'string') {
        updateRow({ name: value })
        return
    }
    if (columnId === 'city' && typeof value === 'string') {
        updateRow({ city: value })
        return
    }
    if (columnId === 'score') {
        const next = typeof value === 'number' ? value : Number(value)
        if (Number.isFinite(next)) updateRow({ score: next })
        return
    }
    if (columnId === 'createdAt' && typeof value === 'string') {
        updateRow({ createdAt: value })
    }
}

const columns: ColumnDef<BasicRow>[] = [
    {
        accessorKey: 'id',
        header: 'ID',
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: 'includesString',
        meta: { filterPlaceholder: 'Filtrar ID' },
    },
    {
        accessorKey: 'name',
        header: 'Nombre',
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: 'includesString',
        meta: {
            filterPlaceholder: 'Filtrar nombre',
            editable: true,
            editPlaceholder: 'Editar nombre',
        },
    },
    {
        accessorKey: 'email',
        header: 'Email',
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: 'includesString',
        meta: { filterPlaceholder: 'Filtrar email' },
    },
    {
        accessorKey: 'status',
        header: 'Estado',
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: 'equalsString',
        meta: {
            filterType: 'select',
            filterOptions: [
                { label: 'Activo', value: 'Activo' },
                { label: 'Inactivo', value: 'Inactivo' },
            ],
            editable: true,
            editOptions: [
                { label: 'Activo', value: 'Activo' },
                { label: 'Inactivo', value: 'Inactivo' },
            ],
        },
    },
    {
        accessorKey: 'department',
        header: 'Departamento',
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: 'equalsString',
        meta: {
            filterType: 'select',
            filterOptions: DEPARTMENTS.map((d) => ({ label: d, value: d })),
            editable: true,
            editOptions: DEPARTMENTS.map((d) => ({ label: d, value: d })),
        },
    },
    {
        accessorKey: 'age',
        header: 'Edad',
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: 'inNumberRange',
        meta: { filterType: 'numberRange', filterMinPlaceholder: 'Min', filterMaxPlaceholder: 'Max' },
    },
    {
        accessorKey: 'score',
        header: 'Score',
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: 'inNumberRange',
        meta: {
            filterType: 'numberRange',
            filterMinPlaceholder: 'Min',
            filterMaxPlaceholder: 'Max',
            editable: true,
            editType: 'number',
            editPlaceholder: '0-100',
        },
    },
    {
        accessorKey: 'city',
        header: 'Ciudad',
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: 'includesString',
        meta: {
            filterPlaceholder: 'Filtrar ciudad',
            editable: true,
            editPlaceholder: 'Editar ciudad',
        },
    },
    {
        accessorKey: 'createdAt',
        header: 'Alta',
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: 'dateRange' as import('@tanstack/vue-table').FilterFnOption<BasicRow>,
        meta: {
            filterType: 'dateRange',
            filterFromPlaceholder: 'Desde',
            filterToPlaceholder: 'Hasta',
            editable: true,
            editType: 'date',
            editPlaceholder: 'YYYY-MM-DD',
        },
    },
]

</script>
