<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <Card class="shadow-2xl">
                <template #title>
                    <div class="text-center space-y-2">
                        <span class="inline-flex items-center gap-2 rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-700">
                            <i class="pi pi-shield" />
                            Verificación en dos pasos
                        </span>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">Introduce tu segundo factor</h1>
                            <p class="text-sm text-gray-500">
                                {{ props.email ? `Cuenta: ${props.email}` : 'Ingresa el código mostrado en tu app.' }}
                            </p>
                        </div>
                    </div>
                </template>

                <template #subtitle>
                    <Message v-if="errorMessage" severity="error" class="w-full">
                        {{ errorMessage }}
                    </Message>
                </template>

                <template #content>
                    <div class="flex justify-center mb-6">
                        <div class="relative">
                            <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 text-lg font-semibold">
                                2FA
                            </div>
                        </div>
                    </div>

                    <form class="space-y-5" @submit.prevent="submit">
                        <div v-if="!useRecovery">
                            <label for="code" class="block text-sm font-medium text-gray-700">
                                Código de la app de autenticación
                            </label>
                            <InputText id="code" v-model="form.code" class="w-full mt-2 text-center tracking-widest text-lg"
                                maxlength="6" inputmode="numeric" autocomplete="one-time-code" placeholder="••••••"
                                :invalid="Boolean(form.errors.code)" />
                        </div>

                        <div v-else>
                            <label for="recovery_code" class="block text-sm font-medium text-gray-700">
                                Código de recuperación
                            </label>
                            <InputText id="recovery_code" v-model="form.recovery_code" class="w-full mt-2 tracking-wide"
                                autocomplete="off" placeholder="EJEMPLO-1234" />
                            <p class="mt-2 text-xs text-gray-500">Cada código de recuperación solo se puede usar una vez.</p>
                        </div>

                        <div class="flex justify-between text-xs text-gray-500">
                            <button type="button" class="font-semibold text-indigo-600 hover:text-indigo-500"
                                @click="toggleMode">
                                {{ useRecovery ? 'Usar código desde la app' : 'Usar código de recuperación' }}
                            </button>
                            <button type="button" class="font-semibold text-gray-500 hover:text-gray-700"
                                @click="backToLogin">
                                Cambiar de cuenta
                            </button>
                        </div>

                        <Button type="submit" :loading="form.processing" label="Verificar código"
                            icon="pi pi-lock-open" class="w-full !bg-purple-600 !border-purple-600" />
                    </form>
                </template>
            </Card>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Message from 'primevue/message'

interface Props {
    email?: string
}

const props = defineProps<Props>()
const useRecovery = ref(false)

const form = useForm({
    code: '',
    recovery_code: '',
})

const errorMessage = computed(() => form.errors.code || form.errors.recovery_code || null)

const submit = (): void => {
    form.post('/two-factor-challenge', {
        onSuccess: () => form.reset(),
    })
}

const toggleMode = (): void => {
    useRecovery.value = !useRecovery.value
    form.reset()
}

const backToLogin = (): void => {
    router.visit('/login')
}
</script>
