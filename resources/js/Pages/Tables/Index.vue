<template>
    <AdminLayout title="Tablas" subtitle="Prueba de TanStack Table (base reutilizable)">
        <BaseTable
            :data="data"
            :columns="columns"
            :loading="loading"
            enable-sorting
            enable-pagination
            enable-global-filter
            :selectable="true"
            :page-size="10"
            show-sticky-header
        >
            <template #toolbar="{ table }">
                <div class="flex items-center gap-2">
                    <button type="button" class="px-3 py-1 rounded border" :disabled="loading"
                        @click="regenerate">
                        Regenerar datos
                    </button>
                    <button type="button" class="px-3 py-1 rounded border"
                        @click="logSelected(table)">Ver seleccionados</button>
                </div>
            </template>
            <template #pagination="{ table }">
                <TablePagination :table="table" :page-size-options="[5, 10, 25]" />
            </template>
        </BaseTable>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import BaseTable from '@/Components/Tables/BaseTable.vue'
import TablePagination from '@/Components/Tables/TablePagination.vue'
import { faker } from '@faker-js/faker'
import { type ColumnDef, type Table } from '@tanstack/vue-table'

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

const data = ref<BasicRow[]>(makeFakeRows(10))
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

const columns: ColumnDef<BasicRow>[] = [
    {
        accessorKey: 'id',
        header: 'ID',
        cell: (info) => info.getValue(),
    },
    {
        accessorKey: 'name',
        header: 'Nombre',
        cell: (info) => info.getValue(),
    },
    {
        accessorKey: 'email',
        header: 'Email',
        cell: (info) => info.getValue(),
    },
]

</script>
