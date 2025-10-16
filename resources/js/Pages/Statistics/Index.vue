<template>
  <AdminLayout
    title="Estadísticas"
    subtitle="Visualiza las estadísticas del sistema"
    :breadcrumbs="[
      { name: 'Dashboard', href: '/dashboard' },
      { name: 'Estadísticas' },
    ]"
  >
    <div class="text-center py-12">
      <ChartBarIcon class="mx-auto h-12 w-12 text-gray-400" />
      <h3 class="mt-2 text-sm font-semibold text-gray-900">Estadísticas</h3>
      <p class="mt-1 text-sm text-gray-500">Funcionalidad próximamente...</p>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ChartBarIcon } from '@heroicons/vue/24/outline'
</script>
