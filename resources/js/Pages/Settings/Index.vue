<template>
  <AdminLayout
    title="Configuración"
    subtitle="Ajusta la configuración del sistema"
    :breadcrumbs="[
      { name: 'Dashboard', href: '/dashboard' },
      { name: 'Configuración' },
    ]"
  >
    <div class="space-y-6">
      <!-- General Settings -->
      <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg">
        <div class="px-4 py-6 sm:p-8">
          <div class="max-w-2xl">
            <h2 class="text-base font-semibold leading-7 text-gray-900">Configuración General</h2>
            <p class="mt-1 text-sm leading-6 text-gray-600">
              Ajusta la configuración básica del sistema.
            </p>

            <form class="mt-10 space-y-8">
              <div>
                <label for="site-name" class="block text-sm font-medium leading-6 text-gray-900">
                  Nombre del Sitio
                </label>
                <div class="mt-2">
                  <input
                    type="text"
                    name="site-name"
                    id="site-name"
                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    placeholder="Admin Panel"
                  />
                </div>
              </div>

              <div>
                <label for="admin-email" class="block text-sm font-medium leading-6 text-gray-900">
                  Email del Administrador
                </label>
                <div class="mt-2">
                  <input
                    type="email"
                    name="admin-email"
                    id="admin-email"
                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    placeholder="admin@example.com"
                  />
                </div>
              </div>

              <div class="flex items-center">
                <input
                  id="maintenance-mode"
                  name="maintenance-mode"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                />
                <label for="maintenance-mode" class="ml-3 block text-sm leading-6 text-gray-900">
                  Modo Mantenimiento
                </label>
              </div>

              <div class="flex justify-end gap-x-3">
                <button
                  type="button"
                  class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                  Guardar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue'
</script>
