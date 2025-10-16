<template>
  <AdminLayout
    title="Países"
    subtitle="Gestiona los países del sistema"
    :breadcrumbs="[
      { name: 'Dashboard', href: '/dashboard' },
      { name: 'Países' },
    ]"
  >
    <div class="text-center py-12">
      <GlobeAmericasIcon class="mx-auto h-12 w-12 text-gray-400" />
      <h3 class="mt-2 text-sm font-semibold text-gray-900">Países</h3>
      <p class="mt-1 text-sm text-gray-500">Funcionalidad próximamente...</p>
      <div class="mt-6">
        <button
          type="button"
          class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
        >
          <PlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
          Nuevo País
        </button>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { GlobeAmericasIcon, PlusIcon } from '@heroicons/vue/24/outline'
</script>
