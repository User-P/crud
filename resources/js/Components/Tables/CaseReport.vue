<template>
  <div class="p-4 max-w-full">
    <h2 class="text-lg font-bold mb-4 text-center">INFORME DEL CASO</h2>

    <!-- Contexto del usuario -->
    <section class="border border-gray-200 rounded mb-4 bg-white p-4">
      <h3 class="text-sm font-semibold mb-2">CONTEXTO DEL USUARIO</h3>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
        <div>
          <div class="text-xs text-gray-500">Nombre</div>
          <div class="font-medium">{{ caseData.name ?? '-' }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500">Email</div>
          <div class="font-medium">{{ caseData.email ?? '-' }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500">Empleado</div>
          <div class="font-medium">{{ caseData.id ?? '-' }}</div>
        </div>

        <div>
          <div class="text-xs text-gray-500">Departamento</div>
          <div class="font-medium">{{ caseData.department ?? '-' }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500">Edad</div>
          <div class="font-medium">{{ caseData.age ?? '-' }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500">Alta</div>
          <div class="font-medium">{{ caseData.createdAt ?? '-' }}</div>
        </div>
      </div>
    </section>

    <!-- Análisis investigativo -->
    <section class="border border-gray-200 rounded mb-4 bg-white p-4">
      <h3 class="text-sm font-semibold mb-2">ANÁLISIS INVESTIGATIVO</h3>
      <div class="text-sm">
        <div class="mb-2 text-xs text-gray-500">Descripción</div>
        <p class="text-sm text-gray-700">{{ caseData.description ?? 'No hay descripción disponible para este caso.' }}
        </p>
      </div>

      <div class="mt-4">
        <h4 class="text-xs font-medium text-gray-600 mb-2">Hallazgos</h4>
        <ul class="list-disc pl-5 text-sm text-gray-700">
          <li v-for="(h, i) in (caseData.findings ?? ['Préstamo de contraseñas'])" :key="i">{{ h }}</li>
        </ul>
      </div>
    </section>

    <!-- Accionables / Tabla pequeña -->
    <section class="border border-gray-200 rounded bg-white p-4">
      <h3 class="text-sm font-semibold mb-2">ACCIONABLES</h3>
      <div class="overflow-auto">
        <table class="w-full text-sm text-left border-collapse">
          <thead>
            <tr class="text-xs text-gray-500 border-b">
              <th class="py-2">Clave</th>
              <th class="py-2">Área</th>
              <th class="py-2">Accionable</th>
              <th class="py-2">Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(a, i) in (caseData.actionables ?? defaultActionables)" :key="i" class="border-b">
              <td class="py-2">{{ a.key }}</td>
              <td class="py-2">{{ a.area }}</td>
              <td class="py-2">{{ a.action }}</td>
              <td class="py-2">{{ a.status }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ caseData: any }>()

const defaultActionables = computed(() => [
  { key: 'BL-01-UBA-SC', area: 'Seguridad', action: 'Bloqueo de Puertos USB', status: 'Solicitado' },
  { key: 'BL-02-UBA-SC', area: 'Seguridad', action: 'Bloqueo de Puertos USB', status: 'Solicitado' },
])
</script>
