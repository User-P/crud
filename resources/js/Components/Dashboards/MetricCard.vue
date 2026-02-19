<template>
    <button
        type="button"
        class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 text-left shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        @click="$emit('click')"
    >
        <div class="flex items-start justify-between">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-110"
                :class="v.iconBg"
            >
                <component :is="icon" class="h-6 w-6" :class="v.iconColor" aria-hidden="true" />
            </div>
        </div>
        <p class="mt-4 text-3xl font-bold tracking-tight text-slate-900">
            {{ value }}
        </p>
        <p class="mt-1 text-sm font-medium text-slate-500">
            {{ label }}
        </p>
        <div
            class="absolute bottom-0 left-0 right-0 h-1 rounded-b-2xl transition-all duration-300 group-hover:h-1.5"
            :class="v.bar"
        />
    </button>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Component } from 'vue'

interface Props {
    label: string
    value: string | number
    icon: Component
    /** 'blue' | 'green' | 'red' - color de la barra inferior e ícono */
    variant?: 'blue' | 'green' | 'red'
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'blue',
})

defineEmits<{
    (e: 'click'): void
}>()

const variants: Record<'blue' | 'green' | 'red', { iconBg: string; iconColor: string; bar: string }> = {
    blue: {
        iconBg: 'bg-blue-50',
        iconColor: 'text-blue-600',
        bar: 'bg-blue-500',
    },
    green: {
        iconBg: 'bg-emerald-50',
        iconColor: 'text-emerald-600',
        bar: 'bg-emerald-500',
    },
    red: {
        iconBg: 'bg-rose-50',
        iconColor: 'text-rose-600',
        bar: 'bg-rose-500',
    },
}

const v = computed(() => variants[props.variant])
</script>
