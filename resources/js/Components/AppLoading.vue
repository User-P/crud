<template>
    <div
        v-if="overlay"
        :class="[
            'app-loading-overlay flex flex-col items-center justify-center gap-3 bg-[var(--th-input-bg)] backdrop-blur-sm',
            fullScreen ? 'fixed inset-0 z-9999' : 'absolute inset-0 z-10 rounded-2xl'
        ]"
        role="status"
        aria-live="polite"
        :aria-label="message || 'Cargando'"
    >
        <ProgressSpinner
            style="width: 40px; height: 40px"
            stroke-width="3"
            class="text-[color:var(--th-item-active-color)]"
            aria-hidden="true"
        />
        <p v-if="message" class="text-sm font-medium text-[color:var(--th-text-secondary)]">
            {{ message }}
        </p>
    </div>
    <div v-else class="app-loading-inline flex flex-col items-center justify-center gap-2 py-6" role="status" :aria-label="message || 'Cargando'">
        <ProgressSpinner
            style="width: 32px; height: 32px"
            stroke-width="3"
            class="text-[color:var(--th-item-active-color)]"
            aria-hidden="true"
        />
        <p v-if="message" class="text-xs text-[color:var(--th-text-muted)]">
            {{ message }}
        </p>
    </div>
</template>

<script setup lang="ts">
import ProgressSpinner from 'primevue/progressspinner'

withDefaults(
    defineProps<{
        /** Si true, cubre el contenedor padre (o la pantalla si fullScreen) con fondo y spinner. Si false, solo spinner + mensaje inline. */
        overlay?: boolean
        /** Para overlay: si true, posición fixed sobre toda la pantalla (z-9999). Si false, absolute dentro del padre. */
        fullScreen?: boolean
        /** Texto opcional bajo el spinner */
        message?: string
    }>(),
    { overlay: true, fullScreen: false }
)
</script>
