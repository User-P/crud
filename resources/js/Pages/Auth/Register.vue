<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-2xl">
      <Card class="shadow-2xl">
        <template #title>
          <div class="text-center space-y-2">
            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
              <i class="pi pi-user-plus" />
              Registro local temporal
            </span>
            <div>
              <h1 class="text-2xl font-semibold text-gray-900">Crear cuenta</h1>
              <p class="text-sm text-gray-500">Úsala mientras finalizamos la integración con Okta</p>
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
          <form @submit.prevent="submit" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <label for="name" class="text-xs uppercase tracking-wide text-indigo-700 font-semibold">Nombre completo</label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  autocomplete="name"
                  class="w-full rounded-lg border border-indigo-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                  required
                />
                <p v-if="form.errors.name" class="text-xs text-red-600 font-semibold">{{ form.errors.name }}</p>
              </div>

              <div class="space-y-2">
                <label for="employee_number" class="text-xs uppercase tracking-wide text-indigo-700 font-semibold">Número de empleado (opcional)</label>
                <input
                  id="employee_number"
                  v-model="form.employee_number"
                  type="text"
                  autocomplete="off"
                  class="w-full rounded-lg border border-indigo-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                />
                <p v-if="form.errors.employee_number" class="text-xs text-red-600 font-semibold">{{ form.errors.employee_number }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <label for="email" class="text-xs uppercase tracking-wide text-indigo-700 font-semibold">Correo</label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  autocomplete="email"
                  class="w-full rounded-lg border border-indigo-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                  required
                />
                <p v-if="form.errors.email" class="text-xs text-red-600 font-semibold">{{ form.errors.email }}</p>
              </div>

              <div class="space-y-2">
                <label for="password" class="text-xs uppercase tracking-wide text-indigo-700 font-semibold">Contraseña</label>
                <input
                  id="password"
                  v-model="form.password"
                  type="password"
                  autocomplete="new-password"
                  class="w-full rounded-lg border border-indigo-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                  required
                />
                <p v-if="form.errors.password" class="text-xs text-red-600 font-semibold">{{ form.errors.password }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <label for="password_confirmation" class="text-xs uppercase tracking-wide text-indigo-700 font-semibold">Confirmar contraseña</label>
                <input
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  type="password"
                  autocomplete="new-password"
                  class="w-full rounded-lg border border-indigo-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                  required
                />
              </div>

              <div class="flex items-end">
                <Message severity="info" class="w-full text-sm">
                  Usa una contraseña de al menos 8 caracteres. Esta cuenta es solo para pruebas locales.
                </Message>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <Button
                type="button"
                label="Volver al login"
                icon="pi pi-arrow-left"
                class="w-full !bg-white !text-indigo-700 !border-indigo-200"
                severity="secondary"
                outlined
                @click="goToLogin"
              />
              <Button
                type="submit"
                label="Crear cuenta y acceder"
                icon="pi pi-check"
                class="w-full !bg-indigo-600 !border-indigo-600"
                :loading="form.processing"
              />
            </div>

            <p class="text-center text-xs text-gray-400">
              Versión {{ laravelVersion }} • Registro local habilitado
            </p>
          </form>
        </template>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Message from 'primevue/message'

interface PageProps {
  laravelVersion?: string
  flash?: {
    success?: string | null
    error?: string | null
  }
  auth?: {
    register_url?: string
    login_url?: string
  }
}

const page = usePage<PageProps>()

const flashSuccess = computed(() => page.props.flash?.success ?? null)
const flashError = computed(() => page.props.flash?.error ?? null)
const registerUrl = computed(() => page.props.auth?.register_url ?? '/register')
const loginUrl = computed(() => page.props.auth?.login_url ?? '/login')
const laravelVersion = computed(() => page.props.laravelVersion ?? '')

const form = useForm({
  name: '',
  email: '',
  employee_number: '',
  password: '',
  password_confirmation: '',
})

const goToLogin = (): void => {
  window.location.href = loginUrl.value
}

const submit = (): void => {
  form.post(registerUrl.value, {
    preserveScroll: true,
  })
}
</script>
