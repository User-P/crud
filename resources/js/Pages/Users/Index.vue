<template>
  <AdminLayout
    title="Usuarios"
    subtitle="Gestiona todos los usuarios del sistema"
    :breadcrumbs="[
      { name: 'Dashboard', href: '/dashboard' },
      { name: 'Usuarios' },
    ]"
  >
    <!-- Actions bar -->
    <div class="mb-6 flex items-center justify-between">
      <div class="flex flex-1 items-center gap-x-4">
        <div class="relative flex-1 max-w-md">
          <MagnifyingGlassIcon
            class="pointer-events-none absolute inset-y-0 left-0 h-full w-5 text-gray-400 pl-3"
            aria-hidden="true"
          />
          <input
            v-model="search"
            type="search"
            placeholder="Buscar usuarios..."
            class="block w-full rounded-md border-0 py-2 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
          />
        </div>
        <Menu as="div" class="relative">
          <MenuButton class="flex items-center gap-x-1 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Filtros
            <ChevronDownIcon class="-mr-1 h-5 w-5 text-gray-400" aria-hidden="true" />
          </MenuButton>
          <MenuItems class="absolute left-0 z-10 mt-2 w-56 origin-top-left rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
            <div class="py-1">
              <MenuItem v-slot="{ active }">
                <a
                  href="#"
                  :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']"
                >
                  Todos los usuarios
                </a>
              </MenuItem>
              <MenuItem v-slot="{ active }">
                <a
                  href="#"
                  :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']"
                >
                  Solo administradores
                </a>
              </MenuItem>
              <MenuItem v-slot="{ active }">
                <a
                  href="#"
                  :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']"
                >
                  Solo usuarios activos
                </a>
              </MenuItem>
            </div>
          </MenuItems>
        </Menu>
      </div>
      <button
        type="button"
        class="ml-4 rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
        @click="router.visit('/users/create')"
      >
        <UserPlusIcon class="inline-block h-5 w-5 mr-2 -mt-0.5" />
        Nuevo Usuario
      </button>
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
                  <img
                    class="h-10 w-10 rounded-full"
                    :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random`"
                    alt=""
                  />
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
              <span
                :class="[
                  user.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800',
                  'inline-flex rounded-full px-2 text-xs font-semibold leading-5',
                ]"
              >
                {{ user.role === 'admin' ? 'Administrador' : 'Usuario' }}
              </span>
            </td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
              <span
                :class="[
                  user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800',
                  'inline-flex rounded-full px-2 text-xs font-semibold leading-5',
                ]"
              >
                {{ user.status === 'active' ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
              <Menu as="div" class="relative inline-block text-left">
                <MenuButton class="flex items-center rounded-md text-gray-400 hover:text-gray-600">
                  <span class="sr-only">Abrir opciones</span>
                  <EllipsisVerticalIcon class="h-5 w-5" aria-hidden="true" />
                </MenuButton>
                <MenuItems class="absolute right-0 z-10 mt-2 w-32 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                  <div class="py-1">
                    <MenuItem v-slot="{ active }">
                      <a
                        href="#"
                        :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']"
                        @click.prevent="editUser(user.id)"
                      >
                        Editar
                      </a>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                      <a
                        href="#"
                        :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-red-700']"
                        @click.prevent="deleteUser(user.id)"
                      >
                        Eliminar
                      </a>
                    </MenuItem>
                  </div>
                </MenuItems>
              </Menu>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 rounded-lg shadow-sm">
      <div class="flex flex-1 justify-between sm:hidden">
        <button
          class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
          Anterior
        </button>
        <button
          class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
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
            <button
              class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
            >
              <span class="sr-only">Anterior</span>
              <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
            </button>
            <button
              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
            >
              1
            </button>
            <button
              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
            >
              2
            </button>
            <button
              class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
            >
              <span class="sr-only">Siguiente</span>
              <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
            </button>
          </nav>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import {
  MagnifyingGlassIcon,
  UserPlusIcon,
  ChevronDownIcon,
  EllipsisVerticalIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
} from '@heroicons/vue/24/outline'

interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'user'
  status: 'active' | 'inactive'
}

const search = ref('')

// Datos de ejemplo (en producción vendrían de las props)
const users = ref<User[]>([
  { id: 1, name: 'Juan Pérez', email: 'juan@example.com', role: 'admin', status: 'active' },
  { id: 2, name: 'María García', email: 'maria@example.com', role: 'user', status: 'active' },
  { id: 3, name: 'Carlos López', email: 'carlos@example.com', role: 'user', status: 'active' },
  { id: 4, name: 'Ana Martínez', email: 'ana@example.com', role: 'admin', status: 'inactive' },
  { id: 5, name: 'Pedro Sánchez', email: 'pedro@example.com', role: 'user', status: 'active' },
])

const filteredUsers = computed(() => {
  if (!search.value) return users.value
  return users.value.filter(user =>
    user.name.toLowerCase().includes(search.value.toLowerCase()) ||
    user.email.toLowerCase().includes(search.value.toLowerCase())
  )
})

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
