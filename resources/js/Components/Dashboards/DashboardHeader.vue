<template>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600"
            >
                <component :is="icon" class="h-6 w-6" aria-hidden="true" />
            </div>
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">
                    {{ title }}
                </h2>
                <p v-if="subtitle" class="mt-0.5 text-sm text-slate-500">
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
import type { Component } from 'vue'

defineProps<{
    title: string
    subtitle?: string
    icon: Component
}>()
</script>
