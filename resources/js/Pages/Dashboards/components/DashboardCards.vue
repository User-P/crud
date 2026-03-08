<template>
    <a :href="href"
        class="cosmos-surface group flex flex-col rounded-2xl p-6 transition-all duration-300 hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-indigo-400/40 focus:ring-offset-2 focus:ring-offset-transparent"
        @click.prevent="onNavigate(href)">
        <div class="flex items-center justify-between">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl transition-transform duration-300 group-hover:scale-105 backdrop-blur"
                :class="iconBg">
                <Icon :icon="icon" :class="iconColor" height="24px" />
            </div>

            <span
                class="rounded-full bg-white/60 px-2.5 py-0.5 text-xs font-medium text-slate-600 backdrop-blur group-hover:bg-white/80">
                {{ badge }}
            </span>
        </div>

        <h3 class="mt-4 text-lg font-semibold text-slate-800">
            {{ name }}
        </h3>

        <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-600">
            {{ description }}
        </p>

        <span class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
            Abrir dashboard
            <Icon icon="heroicons:arrow-right" class="ml-1 h-4 w-4 transition-transform group-hover:translate-x-0.5" />
        </span>
    </a>
</template>

<script setup lang="ts">
import { Icon } from '@iconify/vue';

interface Props {
    href: string;
    name: string;
    description: string;
    badge: string | number;
    icon: string;
    iconBg?: string;
    iconColor?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'navigate', href: string): void;
}>();

function onNavigate(href: string) {
    emit('navigate', href);
}
</script>
