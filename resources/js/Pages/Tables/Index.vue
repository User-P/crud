<template>
    <AdminLayout title="Tablas" subtitle="Prueba de TanStack Table (base reutilizable)">
        <TanStackTable
            :data="data"
            :columns="columns"
            :loading="loading"
            enable-sorting
            enable-pagination
            enable-global-filter
            enable-column-filters
            selectable
            :page-size="10"
            show-sticky-header
        >
            <template #toolbar="{ table }">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <Button
                            type="button"
                            class="p-button-sm p-button-secondary"
                            label="Regenerar datos"
                            @click="regenerate"
                        />
                        <Button
                            type="button"
                            class="p-button-sm p-button-info"
                            label="Log Seleccionados"
                            @click="logSelected(table)"
                        />
                    </div>
                    <TableFilters :table="table" />
                </div>
            </template>
            <template #pagination="{ table }">
                <TablePagination :table="table" :page-size-options="[5, 10, 25]" />
            </template>
            <template #row-actions="{ original }">
                <div class="flex items-center gap-2">
                    <Button type="button" class="p-button-sm" label="Editar" @click="editRow(original)" />
                    <Button
                        type="button"
                        class="p-button-sm p-button-danger"
                        label="Eliminar"
                        @click="deleteRow(original)"
                    />
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
import TableFilters from '@/Components/Tables/TableFilters.vue'
import { faker } from '@faker-js/faker'
import { type ColumnDef, type Table } from '@tanstack/vue-table'
import Button from 'primevue/button'

type BasicRow = {
    id: string
    name: string
    email: string
}

const makeFakeRows = (count = 10): BasicRow[] =>
    Array.from({ length: count }, () => ({
        id: faker.string.uuid(),
        name: faker.person.fullName(),
        email: faker.internet.email(),
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
        meta: { filterPlaceholder: 'Filtrar nombre' },
    },
    {
        accessorKey: 'email',
        header: 'Email',
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: 'includesString',
        meta: { filterPlaceholder: 'Filtrar email' },
    },
]

</script>
