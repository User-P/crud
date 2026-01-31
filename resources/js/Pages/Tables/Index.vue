<template>
    <AdminLayout title="Tablas" subtitle="Prueba de TanStack Table (básica)">
        <table class="text-black border border-gray-300 w-full table-auto">
            <thead>
                <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                    <th
                    class="border border-gray-300 p-2 bg-gray-200"
                    v-for="header in headerGroup.headers" :key="header.id">
                        <FlexRender
                            v-if="!header.isPlaceholder"
                            :render="header.column.columnDef.header"
                            :props="header.getContext()"
                        />
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                class="border border-gray-300 hover:bg-gray-100"
                v-for="row in table.getRowModel().rows" :key="row.id">
                    <td v-for="cell in row.getVisibleCells()" :key="cell.id">
                        <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                    </td>
                </tr>
            </tbody>
        </table>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { faker } from '@faker-js/faker'
import {
    type ColumnDef,
    FlexRender,
    getCoreRowModel,
    useVueTable,
} from '@tanstack/vue-table'

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

const table = useVueTable({
    get data() {
        return data.value
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
})
</script>
