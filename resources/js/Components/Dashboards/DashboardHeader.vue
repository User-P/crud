<template>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="icon-box flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl backdrop-blur">
                <Icon :icon="icon" class="h-6 w-6" aria-hidden="true" />
            </div>
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-primary">
                    {{ title }}
                </h2>
                <p v-if="subtitle" class="mt-0.5 text-sm text-secondary">
                    {{ subtitle }}
                </p>
            </div>
        </div>
        <div v-if="$slots.actions" class="flex items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { Icon } from '@iconify/vue'

defineProps<{
    title: string
    subtitle?: string
    /** Iconify icon id (ej. heroicons:exclamation-triangle) */
    icon: string
}>()
</script>
