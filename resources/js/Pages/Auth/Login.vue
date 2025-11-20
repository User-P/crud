<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-lg">
      <Card class="shadow-2xl">
        <template #title>
          <div class="text-center space-y-2">
            <span class="inline-flex items-center gap-2 rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-600">
              <i class="pi pi-shield" />
              Acceso seguro con Okta
            </span>
            <div>
              <h1 class="text-2xl font-semibold text-gray-900">Bienvenido de nuevo</h1>
              <p class="text-sm text-gray-500">Usa tu identidad corporativa para ingresar al panel</p>
            </div>
          </div>
        </template>

        <template #subtitle>
          <Message v-if="flashError" severity="error" class="w-full">
            {{ flashError }}
          </Message>
          <Message v-else-if="flashSuccess" severity="success" class="w-full">
            {{ flashSuccess }}
          </Message>
        </template>

        <template #content>
          <div class="space-y-6">
            <div class="rounded-2xl border border-dashed border-indigo-200 bg-indigo-50/60 p-4 text-sm text-indigo-900">
              <p class="font-semibold mb-2">¿Cómo funciona?</p>
              <ol class="list-decimal space-y-1 pl-5 text-indigo-900/80">
                <li>Serás redirigido al portal de inicio de sesión de Okta.</li>
                <li>Completa el proceso de autenticación configurado por tu organización.</li>
                <li>Volverás automáticamente al dashboard cuando la sesión se valide.</li>
              </ol>
            </div>

            <Button
              type="button"
              label="Iniciar sesión con Okta"
              icon="pi pi-external-link"
              class="w-full !bg-indigo-600 !border-indigo-600"
              @click="redirectToOkta"
            />

            <p class="text-center text-xs text-gray-400">
              Versión {{ laravelVersion }} • Dominio Okta: {{ oktaDomain }}
            </p>
          </div>
        </template>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Message from 'primevue/message'

interface PageProps {
  laravelVersion?: string
  flash?: {
    success?: string | null
    error?: string | null
  }
  okta?: {
    authorize_url: string
    domain?: string | null
  }
}

const page = usePage<PageProps>()
const laravelVersion = computed(() => page.props.laravelVersion ?? '')
const flashSuccess = computed(() => page.props.flash?.success ?? null)
const flashError = computed(() => page.props.flash?.error ?? null)
const oktaDomain = computed(() => page.props.okta?.domain ?? 'N/D')
const redirectToOkta = (): void => {
  window.location.href = page.props.okta?.authorize_url ?? '/auth/redirect'
}
</script>
