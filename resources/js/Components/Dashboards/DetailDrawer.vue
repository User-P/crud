<template>
    <Teleport to="body">
        <Transition name="drawer-fade">
            <div
                v-if="visible"
                class="fixed inset-0 z-50 flex justify-end"
                aria-modal="true"
                role="dialog"
                aria-labelledby="drawer-title"
                @click.self="close"
            >
                <div class="drawer-overlay absolute inset-0 backdrop-blur-md" @click="close" />
                <div
                    class="drawer-panel relative flex h-full w-full max-w-lg flex-col sm:max-w-xl"
                    @click.stop
                >
                    <div class="drawer-header flex shrink-0 items-center justify-between border-b px-6 py-4">
                        <h2 id="drawer-title" class="text-lg font-semibold text-(--th-text-primary)">
                            {{ title }}
                        </h2>
                        <button
                            type="button"
                            class="rounded-xl p-2 text-(--th-btn-color) transition-colors hover:bg-(--th-btn-hover-bg) hover:text-(--th-btn-hover-color) focus:outline-none focus:ring-2 focus:ring-(--p-focus-ring-color) focus:ring-offset-2 focus:ring-offset-(--th-ring-offset)"
                            aria-label="Cerrar"
                            @click="close"
                        >
                            <Icon icon="heroicons:x-mark" class="h-5 w-5" />
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6">
                        <slot />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { Icon } from '@iconify/vue'

defineProps<{
    visible: boolean
    title: string
}>()

const emit = defineEmits<{
    (e: 'close'): void
}>()

const close = () => emit('close')
</script>

<style scoped>
.drawer-fade-enter-active,
.drawer-fade-leave-active {
    transition: opacity 0.2s ease;
}
.drawer-fade-enter-from,
.drawer-fade-leave-to {
    opacity: 0;
}
</style>
