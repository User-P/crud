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
                <li>Serás redirigido al IdP de Okta usando SAML 2.0.</li>
                <li>Completa el proceso de autenticación configurado por tu organización.</li>
                <li>Volverás automáticamente al dashboard cuando la sesión se valide.</li>
              </ol>
            </div>

            <div class="rounded-2xl border border-indigo-100 bg-white px-4 py-3 text-xs text-indigo-900 shadow-sm">
              <div class="font-semibold text-indigo-700 mb-2">Detalles SAML</div>
              <div class="space-y-1 text-indigo-900/80">
                <div><span class="font-semibold">ACS:</span> {{ samlAcs }}</div>
                <div><span class="font-semibold">Entity ID (SP):</span> {{ samlEntityId }}</div>
                <div><span class="font-semibold">IdP SSO:</span> {{ samlSso }}</div>
                <div v-if="metadataUrl">
                  <a :href="metadataUrl" class="text-indigo-700 font-semibold hover:underline">Ver metadata</a>
                </div>
              </div>
            </div>

            <Button
              type="button"
              label="Iniciar sesión con Okta (SAML)"
              icon="pi pi-external-link"
              class="w-full !bg-indigo-600 !border-indigo-600"
              @click="redirectToProvider"
            />

            <p class="text-center text-xs text-gray-400">
              Versión {{ laravelVersion }} • Flujo SAML activado
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
  auth?: {
    login_url: string
    metadata_url?: string | null
    sp?: {
      entity_id?: string | null
      acs?: string | null
      sls?: string | null
    }
    idp?: {
      entity_id?: string | null
      sso?: string | null
      slo?: string | null
    }
  }
}

const page = usePage<PageProps>()
const flashSuccess = computed(() => page.props.flash?.success ?? null)
const flashError = computed(() => page.props.flash?.error ?? null)

const auth = computed(() => page.props.auth ?? { login_url: '/saml/login' })
const samlAcs = computed(() => auth.value.sp?.acs ?? 'N/D')
const samlEntityId = computed(() => auth.value.sp?.entity_id ?? 'N/D')
const samlSso = computed(() => auth.value.idp?.sso ?? 'N/D')
const metadataUrl = computed(() => auth.value.metadata_url ?? null)

const redirectToProvider = (): void => {
  window.location.href = auth.value.login_url ?? '/saml/login'
}
</script>
