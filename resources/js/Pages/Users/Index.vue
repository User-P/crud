<template>
    <AdminLayout title="Usuarios" subtitle="Gestiona todos los usuarios del sistema" :breadcrumbs="[
        { name: 'Dashboard', href: '/dashboard' },
        { name: 'Usuarios' },
    ]">
        <!-- Actions bar -->
        <div class="mb-6 flex items-center justify-between">
            <div class="flex flex-1 items-center gap-x-4">
                <div class="relative flex-1 max-w-md">
                    <MagnifyingGlassIcon
                        class="pointer-events-none absolute inset-y-0 left-0 h-full w-5 text-gray-400 pl-3"
                        aria-hidden="true" />
                    <InputText v-model="search" type="search" placeholder="Buscar usuarios..." class="w-full pl-10" />
                </div>
                <Button type="button" outlined icon="pi pi-filter" :label="currentFilterLabel"
                    @click="toggleFilterMenu" />
                <PrimeMenu ref="filterMenu" :model="filterMenuItems" popup />
            </div>
            <Button type="button" icon="pi pi-user-plus" label="Nuevo Usuario" class="ml-0 sm:ml-4"
                @click="router.visit('/users/create')" />
        </div>

        <!-- Users table -->
        <div class="overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                            Usuario
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                            Email
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                            Rol
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                            Estado
                        </th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                            <span class="sr-only">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    <img class="h-10 w-10 rounded-full"
                                        :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random`"
                                        alt="" />
                                </div>
                                <div class="ml-4">
                                    <div class="font-medium text-gray-900">{{ user.name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                            {{ user.email }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                            <Tag :value="user.role === 'admin' ? 'Administrador' : 'Usuario'"
                                :severity="user.role === 'admin' ? 'info' : 'secondary'" />
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                            <Tag :value="user.status === 'active' ? 'Activo' : 'Inactivo'"
                                :severity="user.status === 'active' ? 'success' : 'danger'" />
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <Button type="button" icon="pi pi-ellipsis-v" text rounded
                                @click="toggleActionMenu($event, user)" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Menu ref="actionMenu" :model="actionMenuItems" popup />

        <!-- Pagination -->
        <div
            class="mt-6 flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 rounded-lg shadow-sm">
            <div class="flex flex-1 justify-between sm:hidden">
                <button
                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Anterior
                </button>
                <button
                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Siguiente
                </button>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando
                        <span class="font-medium">1</span>
                        a
                        <span class="font-medium">10</span>
                        de
                        <span class="font-medium">{{ users.length }}</span>
                        resultados
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <Button type="button" icon="pi pi-angle-left" text rounded aria-label="Anterior" />
                        <Button type="button" label="1" outlined class="rounded-none" />
                        <Button type="button" label="2" outlined class="rounded-none" />
                        <Button type="button" icon="pi pi-angle-right" text rounded aria-label="Siguiente" />
                    </nav>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Tag from 'primevue/tag'
import PrimeMenu, { type MenuMethods } from 'primevue/menu'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

interface User {
    id: number
    name: string
    email: string
    role: 'admin' | 'user'
    status: 'active' | 'inactive'
}

const search = ref('')
const selectedFilter = ref<'all' | 'admins' | 'active'>('all')
const filterMenu = ref<MenuMethods | null>(null)
const actionMenu = ref<MenuMethods | null>(null)
const activeUser = ref<User | null>(null)

// Datos de ejemplo (en producción vendrían de las props)
const users = ref<User[]>([
    { id: 1, name: 'Juan Pérez', email: 'juan@example.com', role: 'admin', status: 'active' },
    { id: 2, name: 'María García', email: 'maria@example.com', role: 'user', status: 'active' },
    { id: 3, name: 'Carlos López', email: 'carlos@example.com', role: 'user', status: 'active' },
    { id: 4, name: 'Ana Martínez', email: 'ana@example.com', role: 'admin', status: 'inactive' },
    { id: 5, name: 'Pedro Sánchez', email: 'pedro@example.com', role: 'user', status: 'active' },
])

const filterMenuItems = [
    { label: 'Todos los usuarios', icon: 'pi pi-users', command: () => setFilter('all') },
    { label: 'Solo administradores', icon: 'pi pi-shield', command: () => setFilter('admins') },
    { label: 'Solo usuarios activos', icon: 'pi pi-check-circle', command: () => setFilter('active') },
]

const actionMenuItems = computed(() => [
    {
        label: 'Ver detalles',
        icon: 'pi pi-eye',
        command: () => {
            if (activeUser.value) {
                router.visit(`/users/${activeUser.value.id}`)
            }
        },
    },
    {
        label: 'Editar',
        icon: 'pi pi-pencil',
        command: () => {
            if (activeUser.value) {
                editUser(activeUser.value.id)
            }
        },
    },
    {
        label: 'Eliminar',
        icon: 'pi pi-trash',
        command: () => {
            if (activeUser.value) {
                deleteUser(activeUser.value.id)
            }
        },
    },
])

const currentFilterLabel = computed(() => {
    switch (selectedFilter.value) {
        case 'admins':
            return 'Administradores'
        case 'active':
            return 'Usuarios activos'
        default:
            return 'Todos'
    }
})

const filteredUsers = computed(() => {
    return users.value.filter(user => {
        const matchesSearch =
            !search.value ||
            user.name.toLowerCase().includes(search.value.toLowerCase()) ||
            user.email.toLowerCase().includes(search.value.toLowerCase())

        if (!matchesSearch) {
            return false
        }

        if (selectedFilter.value === 'admins') {
            return user.role === 'admin'
        }

        if (selectedFilter.value === 'active') {
            return user.status === 'active'
        }

        return true
    })
})

const toggleFilterMenu = (event: MouseEvent): void => {
    filterMenu.value?.toggle(event)
}

const toggleActionMenu = (event: MouseEvent, user: User): void => {
    activeUser.value = user
    actionMenu.value?.toggle(event)
}

const setFilter = (filter: 'all' | 'admins' | 'active'): void => {
    selectedFilter.value = filter
}

const editUser = (id: number): void => {
    router.visit(`/users/${id}/edit`)
}

const deleteUser = (id: number): void => {
    if (confirm('¿Estás seguro de eliminar este usuario?')) {
        // Aquí iría la lógica de eliminación
        console.log('Eliminar usuario:', id)
    }
}
</script>
