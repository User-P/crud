<template>
    <AdminLayout title="Tablas" subtitle="Prueba de TanStack Table (base reutilizable)">
        <div class="grid grid-cols-1 gap-6 text-black">
            <div>
                <ExampleAdvancedTable :data="data" :columns="columns" :loading="loading" @update:cell="onUpdateCell"
                    @regenerate="regenerate" @edit-row="editRow" @delete-row="deleteRow" />
            </div>
            <div>
                <Informe />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ExampleAdvancedTable from '@/Pages/Tables/ExampleAdvancedTable.vue'
import { faker } from '@faker-js/faker'
import { type ColumnDef } from '@tanstack/vue-table'
import Informe from './Informe.vue'

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
    // Datos arbitrarios que no se muestran como columnas, p. ej. para la fila expandida o case report
    details?: any
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
        details: {
            // Estructura de ejemplo para `caseReport`
            description: faker.lorem.paragraph(),
            findings: Array.from({ length: faker.number.int({ min: 1, max: 4 }) }, () => faker.lorem.sentence()),
            actionables: Array.from({ length: faker.number.int({ min: 1, max: 3 }) }, () => ({
                key: `AC-${faker.string.alphanumeric(6).toUpperCase()}`,
                area: faker.helpers.arrayElement(['Seguridad', 'TI', 'Soporte']),
                action: faker.lorem.sentence(),
                status: faker.helpers.arrayElement(['Solicitado', 'Pendiente', 'Completado']),
            })),
        },
    }))

const data = ref<BasicRow[]>(makeFakeRows(100))
const loading = ref(false)

const regenerate = async () => {
    loading.value = true
    await new Promise((resolve) => setTimeout(resolve, 350))
    data.value = makeFakeRows(100)
    loading.value = false
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

    if (columnId === 'status' && STATUS_OPTIONS.includes(value as BasicRow['status'])) {
        updateRow({ status: value as BasicRow['status'] })
        return
    }
    if (columnId === 'department' && DEPARTMENTS.includes(value as BasicRow['department'])) {
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
        if (Number.isFinite(next)) {
            const clamped = Math.min(100, Math.max(0, next))
            updateRow({ score: clamped })
        }
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
        meta: { filterPlaceholder: 'Filtrar ID', emitOnClick: true },
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
            filterOptions: STATUS_OPTIONS.map((s) => ({ label: s, value: s })),
            editable: true,
            editOptions: STATUS_OPTIONS.map((s) => ({ label: s, value: s })),
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
