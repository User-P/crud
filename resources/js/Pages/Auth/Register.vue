<template>
    <div
        class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center px-4 py-10">
        <div class="w-full grid gap-8 md:grid-cols-2">
            <div class="hidden md:block text-white space-y-6">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold">
                    <i class="pi pi-sparkles" />
                    Nuevo en la plataforma
                </span>
                <div>
                    <h1 class="text-3xl font-semibold">
                        {{ twoFactorSetup ? 'Confirma tu seguridad' : 'Crea tu cuenta' }}
                    </h1>
                    <p class="mt-2 text-sm text-slate-300">
                        Centraliza la administración de usuarios, eventos y estadísticas en un solo lugar.
                    </p>
                </div>
                <ul class="space-y-3 text-sm text-slate-200">
                    <li class="flex items-start gap-3">
                        <i class="pi pi-check-circle text-emerald-400 mt-0.5" />
                        Panel construido con PrimeVue & Tailwind
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="pi pi-check-circle text-emerald-400 mt-0.5" />
                        Integra Inertia, Laravel 11 e Inertia
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="pi pi-check-circle text-emerald-400 mt-0.5" />
                        Listo para 2FA y futuras mejoras
                    </li>
                </ul>
            </div>

            <Card class="shadow-2xl">
                <template #title>
                    <div class="text-center space-y-2">
                        <span :class="[
                            'inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold',
                            twoFactorSetup ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-600',
                        ]">
                            <i :class="twoFactorSetup ? 'pi pi-shield' : 'pi pi-user-plus'" />
                            {{ twoFactorSetup ? 'Verificación de seguridad' : 'Registro de usuarios' }}
                        </span>
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-900">
                                {{ twoFactorSetup ? 'Escanea y confirma tu código' : 'Comencemos' }}
                            </h2>
                            <p class="text-sm text-gray-500">
                                {{
                                    twoFactorSetup
                                        ? 'Generamos tu secreto único. Escanea, ingresa el código y completa el registro.'
                                        : 'Completa los datos para crear tu cuenta'
                                }}
                            </p>
                        </div>
                    </div>
                </template>

                <template #subtitle>
                    <Message v-if="generalError" severity="error" class="w-full">
                        {{ generalError }}
                    </Message>
                    <Message v-else-if="finalizeError" severity="error" class="w-full">
                        {{ finalizeError }}
                    </Message>
                </template>

                <template #content>
                    <template v-if="!twoFactorSetup">
                        <form class="space-y-5" @submit.prevent="submit">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nombre
                                    completo</label>
                                <span class="p-input-icon-left w-full mt-2">
                                    <i class="pi pi-user" />
                                    <InputText id="name" v-model="form.name" class="w-full" autocomplete="name"
                                        :invalid="Boolean(form.errors.name)" placeholder="Laura Martínez" />
                                </span>
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Correo
                                    electrónico</label>
                                <span class="p-input-icon-left w-full mt-2">
                                    <i class="pi pi-envelope" />
                                    <InputText id="email" v-model="form.email" type="email" class="w-full"
                                        autocomplete="email" :invalid="Boolean(form.errors.email)"
                                        placeholder="correo@empresa.com" />
                                </span>
                                <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}
                                </p>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                                <Password id="password" v-model="form.password" class="w-full mt-2" toggleMask
                                    :feedback="true" inputClass="w-full" :invalid="Boolean(form.errors.password)"
                                    placeholder="••••••••" promptLabel="Ingresa una contraseña" weakLabel="Débil"
                                    mediumLabel="Media" strongLabel="Fuerte" />
                                <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password
                                }}</p>
                            </div>

                            <div>
                                <label for="password_confirmation"
                                    class="block text-sm font-medium text-gray-700">Confirmación</label>
                                <Password id="password_confirmation" v-model="form.password_confirmation"
                                    class="w-full mt-2" toggleMask :feedback="false" inputClass="w-full"
                                    :invalid="Boolean(form.errors.password_confirmation)" placeholder="••••••••" />
                                <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.password_confirmation }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-gray-100 bg-gray-50/60 p-4 space-y-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Activar autenticación de 2
                                            factores</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Si la activas ahora, el registro se completará solo después de confirmar el
                                            código de tu app.
                                        </p>
                                    </div>
                                    <InputSwitch v-model="form.enable_two_factor" />
                                </div>

                                <div v-if="form.enable_two_factor"
                                    class="rounded-xl border border-indigo-100 bg-white/80 p-4 text-sm text-indigo-900 space-y-2">
                                    <p class="font-semibold flex items-center gap-2">
                                        <i class="pi pi-lock text-indigo-500" />
                                        ¿Qué sucederá?
                                    </p>
                                    <ul class="list-disc pl-5 space-y-1 text-indigo-900/80">
                                        <li>Generamos tu secreto y código QR inmediatamente.</li>
                                        <li>Verás los códigos de recuperación antes de terminar.</li>
                                        <li>Podrás cancelar si deseas completar el registro sin 2FA.</li>
                                    </ul>
                                </div>
                            </div>

                            <Button type="submit" :loading="form.processing" label="Crear cuenta" icon="pi pi-check"
                                class="w-full !bg-emerald-600 !border-emerald-600" />
                        </form>

                        <p class="mt-6 text-center text-sm text-gray-500">
                            ¿Ya tienes una cuenta?
                            <Link href="/login" class="font-semibold text-emerald-600 hover:text-emerald-500">
                            Inicia sesión
                            </Link>
                        </p>
                    </template>

                    <template v-else>
                        <div class="space-y-6">
                            <div
                                class="rounded-xl border border-dashed border-purple-300 bg-purple-50/70 p-5 text-sm text-purple-900">
                                Hemos reservado tu cuenta para <strong>{{ twoFactorSetup.email }}</strong>. Usa tu app
                                de autenticación para escanear el código y escribe el código de 6 dígitos para
                                finalizar.
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div
                                    class="flex flex-col items-center justify-center rounded-lg border border-gray-200 bg-gray-50 p-4">
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
                                        {{ twoFactorSetup.secret }}
                                    </p>
                                    <p class="mt-2 text-xs text-gray-500">
                                        Ingresa esta clave en tu app si no puedes escanear el QR.
                                    </p>
                                </div>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900">Códigos de recuperación</h3>
                                        <p class="text-xs text-gray-500">Almacénalos en un lugar seguro. Cada uno se usa
                                            una sola vez.</p>
                                    </div>
                                </div>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <code v-for="code in recoveryCodes" :key="code"
                                        class="rounded-md bg-white px-3 py-2 font-mono text-sm tracking-widest text-gray-900 shadow">
                    {{ code }}
                  </code>
                                </div>
                            </div>

                            <form class="space-y-4" @submit.prevent="confirmRegistration">
                                <label for="two_factor_code" class="block text-sm font-medium text-gray-700">Código de 6
                                    dígitos</label>
                                <InputText id="two_factor_code" v-model="finalizeForm.code"
                                    class="w-full text-center tracking-[0.5em] text-lg" maxlength="6"
                                    inputmode="numeric" autocomplete="one-time-code" placeholder="000000"
                                    :invalid="Boolean(finalizeForm.errors.code)" />

                                <div class="flex flex-wrap gap-3">
                                    <Button type="submit" :loading="finalizeForm.processing" label="Confirmar registro"
                                        icon="pi pi-check" class="flex-1 !bg-purple-600 !border-purple-600" />
                                    <Button type="button" label="Cancelar" icon="pi pi-times" class="flex-1"
                                        severity="secondary" :disabled="cancelForm.processing" @click="cancelPending" />
                                </div>
                            </form>
                        </div>
                    </template>
                </template>
            </Card>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import QRCode from 'qrcode'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import Message from 'primevue/message'
import InputSwitch from 'primevue/inputswitch'

interface TwoFactorSetup {
    email: string
    secret: string
    qr_url: string | null
    recovery_codes: string[]
}

const props = defineProps<{
    laravelVersion?: string
    twoFactorSetup?: TwoFactorSetup | null
}>()

const twoFactorSetup = computed(() => props.twoFactorSetup ?? null)
const recoveryCodes = computed(() => twoFactorSetup.value?.recovery_codes ?? [])

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    enable_two_factor: false,
})

const generalError = computed(() => {
    if (twoFactorSetup.value) {
        return null
    }

    return (
        form.errors.name ||
        form.errors.email ||
        form.errors.password ||
        form.errors.password_confirmation ||
        null
    )
})

const submit = (): void => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}

const finalizeForm = useForm({ code: '' })
const finalizeError = computed(() => finalizeForm.errors.code ?? null)
const cancelForm = useForm({})

const confirmRegistration = (): void => {
    finalizeForm.post('/register/confirm-two-factor', {
        onFinish: () => finalizeForm.reset('code'),
    })
}

const cancelPending = (): void => {
    cancelForm.delete('/register/pending-two-factor')
}

const qrDataUrl = ref<string | null>(null)
const qrErrorMessage = ref<string | null>(null)
const isClient = typeof window !== 'undefined'

watch(
    () => twoFactorSetup.value?.qr_url,
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
