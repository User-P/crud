<template>
  <AdminLayout
    title="Configuración"
    subtitle="Administra preferencias generales de tu cuenta"
    :breadcrumbs="[
      { name: 'Dashboard', href: '/dashboard' },
      { name: 'Configuración' },
    ]"
  >
    <div class="space-y-6">
      <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg">
        <div class="px-4 py-6 sm:p-8">
          <div class="max-w-2xl">
            <h2 class="text-base font-semibold leading-7 text-gray-900">Perfil</h2>
            <p class="mt-1 text-sm leading-6 text-gray-600">
              Esta información proviene de Okta. Cualquier cambio debe realizarse en el directorio corporativo.
            </p>

            <dl class="mt-8 divide-y divide-gray-100">
              <div class="py-4 flex justify-between text-sm">
                <dt class="text-gray-500">Nombre</dt>
                <dd class="font-semibold text-gray-900">{{ user?.name ?? '—' }}</dd>
              </div>
              <div class="py-4 flex justify-between text-sm">
                <dt class="text-gray-500">Correo</dt>
                <dd class="font-semibold text-gray-900">{{ user?.email ?? '—' }}</dd>
              </div>
              <div class="py-4 flex justify-between text-sm">
                <dt class="text-gray-500">Proveedor</dt>
                <dd class="font-semibold text-gray-900">{{ authProvider.name }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>

      <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg">
        <div class="px-4 py-6 sm:p-8 space-y-4">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-700">
              <i class="pi pi-lock text-lg" />
            </div>
            <div>
              <h2 class="text-base font-semibold leading-7 text-gray-900">Seguridad centralizada</h2>
              <p class="mt-1 text-sm text-gray-600">
                La autenticación multifactor, políticas de contraseña y ciclo de vida de la sesión se administran directamente en Okta.
              </p>
            </div>
          </div>

          <div class="rounded-lg border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 space-y-2">
            <p>
              <strong>Dominio:</strong> {{ authProvider.domain ?? 'No configurado' }}
            </p>
            <p>
              <strong>Scopes solicitados:</strong> {{ authProvider.scopes }}
            </p>
            <p class="text-xs text-gray-500">
              Para modificar políticas de acceso, contacta al administrador del tenant en Okta.
            </p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

interface AuthProvider {
  name: string
  domain?: string | null
  scopes: string
}

interface SettingsProps {
  authProvider: AuthProvider
  user: {
    name: string
    email: string
  } | null
}

const props = defineProps<SettingsProps>()

const user = computed(() => props.user)
const authProvider = computed(() => props.authProvider)
</script>
