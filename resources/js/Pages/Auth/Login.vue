<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <Card class="shadow-2xl">
                <template #title>
                    <div class="text-center space-y-2">
                        <span class="inline-flex items-center gap-2 rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-600">
                            <i class="pi pi-shield" />
                            Seguridad del Panel
                        </span>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">Bienvenido de nuevo</h1>
                            <p class="text-sm text-gray-500">Ingresa tus credenciales para continuar</p>
                        </div>
                    </div>
                </template>

                <template #subtitle>
                    <Message v-if="generalError" severity="error" class="w-full">
                        {{ generalError }}
                    </Message>
                    <Message v-else-if="flashSuccess" severity="success" class="w-full">
                        {{ flashSuccess }}
                    </Message>
                </template>

                <template #content>
                    <form class="space-y-5" @submit.prevent="submit">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                            <span class="p-input-icon-left w-full mt-2">
                                <i class="pi pi-envelope" />
                                <InputText id="email" v-model="form.email" type="email" class="w-full" autocomplete="username"
                                    :invalid="Boolean(form.errors.email)" placeholder="escribe@tuemail.com" />
                            </span>
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                                <button type="button" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">
                                    ¿Olvidaste tu contraseña?
                                </button>
                            </div>
                            <Password id="password" v-model="form.password" class="w-full mt-2" toggleMask :feedback="false"
                                inputClass="w-full" :invalid="Boolean(form.errors.password)" placeholder="••••••••" />
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}</p>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Checkbox id="remember" v-model="form.remember" :binary="true" />
                                <label for="remember" class="text-sm text-gray-600">Recordarme</label>
                            </div>
                            <span class="text-xs text-gray-400">Versión {{ laravelVersion }}</span>
                        </div>

                        <Button type="submit" :loading="form.processing" label="Iniciar sesión"
                            icon="pi pi-sign-in" class="w-full !bg-indigo-600 !border-indigo-600" />
                    </form>

                    <p class="mt-6 text-center text-sm text-gray-500">
                        ¿Aún no tienes cuenta?
                        <Link href="/register" class="font-semibold text-indigo-600 hover:text-indigo-500">
                            Crear cuenta
                        </Link>
                    </p>
                </template>
            </Card>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'
import Message from 'primevue/message'

interface PageProps {
    laravelVersion?: string
    flash?: {
        success?: string | null
        error?: string | null
    }
}

const page = usePage<PageProps>()
const laravelVersion = computed(() => page.props.laravelVersion ?? '')
const flashSuccess = computed(() => page.props.flash?.success ?? null)

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const generalError = computed(() => form.errors.email || form.errors.password || page.props.flash?.error)

const submit = (): void => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}
</script>
