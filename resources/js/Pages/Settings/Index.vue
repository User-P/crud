<template>
  <AdminLayout
    title="Configuración"
    subtitle="Administra preferencias y seguridad de tu cuenta"
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

      <!-- Two Factor Authentication -->
      <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg">
        <div class="px-4 py-6 sm:p-8 space-y-6">
          <div class="flex flex-wrap items-center gap-4 justify-between">
            <div>
              <h2 class="text-base font-semibold leading-7 text-gray-900">Autenticación en dos pasos</h2>
              <p class="mt-1 text-sm leading-6 text-gray-600 max-w-2xl">
                Añade una capa adicional de seguridad utilizando una app como Google Authenticator, Authy o Microsoft Authenticator.
              </p>
            </div>
            <span :class="[
                'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold',
                statusBadge.classes,
              ]">
              {{ statusBadge.label }}
            </span>
          </div>

          <div v-if="twoFactor?.pending" class="space-y-6">
            <div class="rounded-lg border border-dashed border-indigo-200 bg-indigo-50/50 p-4">
              <h3 class="text-sm font-semibold text-indigo-900">Configura tu app de autenticación</h3>
              <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-indigo-900/80">
                <li>Escanea el código QR o copia el secreto manualmente.</li>
                <li>Introduce el código de 6 dígitos que genera tu app.</li>
                <li>Guarda los códigos de recuperación en un lugar seguro.</li>
              </ol>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
              <div class="flex flex-col items-center justify-center rounded-lg border border-gray-200 bg-gray-50 p-4">
                <span class="text-xs uppercase tracking-wide text-gray-500">Código QR</span>
                <img v-if="qrDataUrl" :src="qrDataUrl" alt="Código QR 2FA"
                  class="mt-3 h-48 w-48 rounded-lg border border-gray-200 bg-white p-2 shadow-inner" />
                <p v-else class="mt-3 text-sm text-gray-500 text-center">
                  {{ qrErrorMessage ?? 'Generando código...' }}
                </p>
              </div>
              <div class="rounded-lg border border-gray-200 bg-white p-4">
                <span class="text-xs uppercase tracking-wide text-gray-500">Clave manual</span>
                <p class="mt-3 font-mono text-lg tracking-widest text-gray-900">
                  {{ twoFactor.secret ?? '--------' }}
                </p>
                <p class="mt-2 text-xs text-gray-500">
                  Ingresa esta clave en tu app si no puedes escanear el QR.
                </p>
              </div>
            </div>

            <form class="space-y-4" @submit.prevent="confirmTwoFactor">
              <label for="code" class="block text-sm font-medium text-gray-700">
                Código de 6 dígitos
              </label>
              <input
                id="code"
                v-model="confirmForm.code"
                type="text"
                inputmode="numeric"
                maxlength="6"
                placeholder="000000"
                class="block w-48 rounded-md border-0 py-2 text-center text-lg tracking-widest text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600"
                :class="{ 'ring-red-400': confirmForm.errors.code }"
              />
              <p v-if="confirmForm.errors.code" class="text-sm text-red-500">{{ confirmForm.errors.code }}</p>

              <div class="flex flex-wrap gap-3">
                <button
                  type="submit"
                  class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                  :disabled="confirmForm.processing"
                >
                  Confirmar código
                </button>
                <button
                  type="button"
                  class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                  :disabled="disableForm.processing"
                  @click="disableTwoFactor"
                >
                  Cancelar configuración
                </button>
              </div>
            </form>
          </div>

          <div v-else-if="twoFactor?.enabled" class="space-y-6">
            <div class="rounded-lg border border-emerald-100 bg-emerald-50/80 p-4 text-sm text-emerald-900">
              2FA activa desde
              <strong>{{ confirmedAt }}</strong>.
              Cada vez que inicies sesión se te pedirá un código temporal.
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <h3 class="text-sm font-semibold text-gray-900">Códigos de recuperación</h3>
                  <p class="text-xs text-gray-500">Guárdalos en un gestor seguro; cada uno se usa una sola vez.</p>
                </div>
                <button
                  type="button"
                  class="text-sm font-semibold text-indigo-600 hover:text-indigo-500"
                  @click="toggleCodes"
                >
                  {{ showCodes ? 'Ocultar códigos' : 'Mostrar códigos' }}
                </button>
              </div>

              <div v-if="showCodes" class="mt-4">
                <div class="grid gap-3 sm:grid-cols-2">
                  <code
                    v-for="code in recoveryCodes"
                    :key="code"
                    class="rounded-md bg-white px-3 py-2 font-mono text-sm tracking-widest text-gray-900 shadow"
                  >
                    {{ code }}
                  </code>
                </div>
                <div class="mt-4 flex flex-wrap gap-3 text-sm">
                  <button
                    type="button"
                    class="inline-flex items-center rounded-md bg-white px-3 py-2 font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                    @click="copyCodes"
                  >
                    Copiar todos
                  </button>
                  <button
                    type="button"
                    class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 font-semibold text-white shadow-sm hover:bg-gray-800"
                    :disabled="recoveryForm.processing"
                    @click="regenerateCodes"
                  >
                    Regenerar códigos
                  </button>
                  <span v-if="copied" class="text-emerald-600">¡Copiados!</span>
                </div>
              </div>
            </div>

            <div class="flex flex-wrap gap-3">
              <button
                type="button"
                class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                :disabled="disableForm.processing"
                @click="disableTwoFactor"
              >
                Desactivar 2FA
              </button>
            </div>
          </div>

          <div v-else class="space-y-4">
            <p class="text-sm text-gray-600">
              Mantén tu cuenta protegida aunque tu contraseña se filtre. Activa 2FA para requerir un código temporal cada vez que entres.
            </p>
            <button
              type="button"
              class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
              :disabled="enableForm.processing"
              @click="enableTwoFactor"
            >
              Activar autenticación de 2 factores
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import QRCode from 'qrcode'
import AdminLayout from '@/Layouts/AdminLayout.vue'

interface TwoFactorProps {
  enabled: boolean
  pending: boolean
  secret: string | null
  qr_url: string | null
  recovery_codes: string[]
  confirmed_at: string | null
}

const props = defineProps<{ twoFactor: TwoFactorProps | null }>()

const twoFactor = computed(() => props.twoFactor ?? {
  enabled: false,
  pending: false,
  secret: null,
  qr_url: null,
  recovery_codes: [],
  confirmed_at: null,
})

const statusBadge = computed(() => {
  if (twoFactor.value.enabled) {
    return { label: 'Activado', classes: 'bg-emerald-100 text-emerald-700' }
  }
  if (twoFactor.value.pending) {
    return { label: 'Pendiente de confirmar', classes: 'bg-yellow-100 text-yellow-800' }
  }
  return { label: 'Desactivado', classes: 'bg-gray-100 text-gray-700' }
})

const confirmedAt = computed(() => {
  if (!twoFactor.value.confirmed_at) return 'fecha pendiente'
  return new Intl.DateTimeFormat('es-MX', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(twoFactor.value.confirmed_at))
})

const recoveryCodes = computed(() => twoFactor.value.recovery_codes ?? [])
const showCodes = ref(false)
const copied = ref(false)
const qrDataUrl = ref<string | null>(null)
const qrErrorMessage = ref<string | null>(null)
const isClient = typeof window !== 'undefined'

const enableForm = useForm({})
const confirmForm = useForm({ code: '' })
const disableForm = useForm({})
const recoveryForm = useForm({})

const enableTwoFactor = (): void => {
  enableForm.post('/two-factor', { preserveScroll: true })
}

const confirmTwoFactor = (): void => {
  confirmForm.post('/two-factor/confirm', {
    preserveScroll: true,
    onSuccess: () => confirmForm.reset(),
  })
}

const disableTwoFactor = (): void => {
  disableForm.delete('/two-factor', {
    preserveScroll: true,
    onSuccess: () => {
      confirmForm.reset()
      showCodes.value = false
    },
  })
}

const regenerateCodes = (): void => {
  recoveryForm.post('/two-factor/recovery-codes', {
    preserveScroll: true,
    onSuccess: () => {
      copied.value = false
      showCodes.value = true
    },
  })
}

const toggleCodes = (): void => {
  showCodes.value = !showCodes.value
}

const copyCodes = async (): Promise<void> => {
  const hasClipboard = typeof navigator !== 'undefined' && !!navigator.clipboard
  if (!hasClipboard || recoveryCodes.value.length === 0) return
  await navigator.clipboard.writeText(recoveryCodes.value.join('\n'))
  copied.value = true
  setTimeout(() => (copied.value = false), 2000)
}

watch(
  () => twoFactor.value.qr_url,
  async (otpauth) => {
    if (!isClient) return

    if (!otpauth) {
      qrDataUrl.value = null
      qrErrorMessage.value = null
      return
    }

    try {
      qrDataUrl.value = await QRCode.toDataURL(otpauth, { width: 220, margin: 1 })
      qrErrorMessage.value = null
    } catch (error) {
      console.error('No se pudo generar el QR 2FA', error)
      qrDataUrl.value = null
      qrErrorMessage.value = 'No se pudo generar el código QR. Usa la clave manual.'
    }
  },
  { immediate: true }
)
</script>
