<template>
    <div
        class="empty-state flex flex-col items-center justify-center gap-4 rounded-2xl border border-dashed border-(--th-border) bg-(--th-input-bg)/30 px-6 py-12 text-center"
    >
        <div
            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-(--th-input-bg) text-(--th-text-muted)"
            :class="iconClass"
        >
            <Icon :icon="icon" class="h-7 w-7" aria-hidden="true" />
        </div>
        <div class="space-y-1">
            <p class="text-sm font-semibold text-(--th-text-primary)">
                {{ title }}
            </p>
            <p v-if="description" class="text-xs text-(--th-text-muted) max-w-sm">
                {{ description }}
            </p>
        </div>
        <slot name="action">
            <component
                v-if="actionLabel && (actionHref || actionButton)"
                :is="actionTag"
                :href="actionHref"
                :type="actionButton ? 'button' : undefined"
                class="inline-flex items-center gap-2 rounded-xl border border-(--th-border) bg-(--th-input-bg) px-4 py-2.5 text-sm font-medium text-(--th-text-primary) shadow-sm transition-colors hover:bg-(--th-item-hover-bg) hover:text-(--th-item-active-color) focus:outline-none focus:ring-2 focus:ring-(--p-focus-ring-color) focus:ring-offset-2 focus:ring-offset-(--th-ring-offset)"
                @click.prevent="$emit('action')"
            >
                <Icon v-if="actionIcon" :icon="actionIcon" class="h-4 w-4" aria-hidden="true" />
                {{ actionLabel }}
            </component>
        </slot>
    </div>
</template>

<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { computed } from 'vue'

const props = withDefaults(
    defineProps<{
        title: string
        description?: string
        icon?: string
        iconClass?: string
        actionLabel?: string
        actionIcon?: string
        actionHref?: string
        /** Si true, el botón es <button> y emite @action */
        actionButton?: boolean
    }>(),
    { icon: 'heroicons:inbox', iconClass: '', actionButton: false }
)

const actionTag = computed(() => (props.actionButton ? 'button' : 'a'))

defineEmits<{ (e: 'action'): void }>()
</script>
