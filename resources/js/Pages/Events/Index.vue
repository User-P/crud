<template>
  <AdminLayout
    title="Eventos"
    subtitle="Gestiona los eventos del sistema"
    :breadcrumbs="[
      { name: 'Dashboard', href: '/dashboard' },
      { name: 'Eventos' },
    ]"
  >
    <div class="text-center py-12">
      <Icon icon="heroicons:calendar-days" class="mx-auto h-12 w-12 text-gray-400" />
      <h3 class="mt-2 text-sm font-semibold text-gray-900">Eventos</h3>
      <p class="mt-1 text-sm text-gray-500">Funcionalidad próximamente...</p>
      <div class="mt-6">
        <button
          type="button"
          class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
        >
          <Icon icon="heroicons:plus" class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
          Nuevo Evento
        </button>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Icon } from '@iconify/vue'
</script>
