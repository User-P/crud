<template>
    <!-- Glass panel wrapping the whole header -->
    <div class="relative overflow-hidden rounded-2xl">
        <!-- Glass background -->
        <span
            class="glass-panel absolute inset-0 rounded-2xl transition-all duration-200"
            aria-hidden="true"
        />
        <!-- Left gradient accent strip -->
        <span
            class="absolute inset-y-0 left-0 w-[3px] rounded-l-2xl bg-gradient-to-b from-[color:var(--th-item-active-color)] to-transparent"
            aria-hidden="true"
        />
        <!-- Top-right corner ambient orb -->
        <span
            class="absolute -right-6 -top-6 h-28 w-28 rounded-full opacity-[0.07] blur-2xl dark:opacity-[0.12]"
            style="background: var(--th-item-active-color)"
            aria-hidden="true"
        />

        <div class="relative z-10 flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6">
            <!-- Left: icon + title + subtitle + optional stats chips -->
            <div class="flex items-start gap-4">
                <!-- Icon box (keeps original icon-box class for existing CSS) -->
                <div class="icon-box flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl backdrop-blur">
                    <Icon :icon="icon" class="h-6 w-6" aria-hidden="true" />
                </div>

                <div class="min-w-0 flex-1">
                    <h2 class="text-xl font-bold tracking-tight text-[color:var(--th-text-primary)]">
                        {{ title }}
                    </h2>
                    <p v-if="subtitle" class="mt-0.5 text-sm text-[color:var(--th-text-secondary)]">
                        {{ subtitle }}
                    </p>

                    <!-- Stats chips: inline pill badges below the subtitle -->
                    <div v-if="$slots.stats" class="mt-3 flex flex-wrap gap-2">
                        <slot name="stats" />
                    </div>
                </div>
            </div>

            <!-- Right: actions slot (date picker, buttons, etc.) -->
            <div v-if="$slots.actions" class="flex shrink-0 items-center gap-2">
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Icon } from '@iconify/vue'

defineProps<{
    title: string
    subtitle?: string
    /** Iconify icon id (e.g. heroicons:exclamation-triangle) */
    icon: string
}>()
</script>
