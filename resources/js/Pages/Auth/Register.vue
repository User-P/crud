<template>
    <div
        class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-2xl grid gap-8 md:grid-cols-2">
            <div class="hidden md:block text-white space-y-6">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold">
                    <i class="pi pi-sparkles" />
                    Nuevo en la plataforma
                </span>
                <div>
                    <h1 class="text-3xl font-semibold">Crea tu cuenta</h1>
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
                        Integra Inertia y Laravel 11
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
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-600">
                            <i class="pi pi-user-plus" />
                            Registro de usuarios
                        </span>
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-900">Comencemos</h2>
                            <p class="text-sm text-gray-500">Completa los datos para crear tu cuenta</p>
                        </div>
                    </div>
                </template>

                <template #subtitle>
                    <Message v-if="generalError" severity="error" class="w-full">
                        {{ generalError }}
                    </Message>
                </template>

                <template #content>
                    <form class="space-y-5" @submit.prevent="submit">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nombre completo</label>
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
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                            <Password id="password" v-model="form.password" class="w-full mt-2" toggleMask
                                :feedback="true" inputClass="w-full" :invalid="Boolean(form.errors.password)"
                                placeholder="••••••••" promptLabel="Ingresa una contraseña" weakLabel="Débil"
                                mediumLabel="Media" strongLabel="Fuerte" />
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}
                            </p>
                        </div>

                        <div>
                            <label for="password_confirmation"
                                class="block text-sm font-medium text-gray-700">Confirmación</label>
                            <Password id="password_confirmation" v-model="form.password_confirmation"
                                class="w-full mt-2" toggleMask :feedback="false" inputClass="w-full"
                                :invalid="Boolean(form.errors.password_confirmation)" placeholder="••••••••" />
                            <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-500">{{
                                form.errors.password_confirmation }}</p>
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
            </Card>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import Message from 'primevue/message'


const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const generalError = computed(() =>
    form.errors.name ||
    form.errors.email ||
    form.errors.password ||
    form.errors.password_confirmation
)

const submit = (): void => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>
