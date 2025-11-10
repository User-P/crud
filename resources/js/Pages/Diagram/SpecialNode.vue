<template>
    <div v-if="data?.variant === 'sidebar'" :class="['diagram-node', 'diagram-node--sidebar']">
        <span class="diagram-node__sidebar">{{ data?.title }}</span>
    </div>
    <div v-else :class="wrapperClasses">
        <span v-if="data?.badge" :class="badgeClasses">
            {{ data?.badge }}
        </span>
        <p v-if="data?.accent" class="diagram-node__accent">
            {{ data?.accent }}
        </p>
        <h3 v-if="data?.title" class="diagram-node__title" v-html="data?.title"></h3>
        <p v-if="data?.subtitle" class="diagram-node__subtitle">
            {{ data?.subtitle }}
        </p>
        <p v-if="data?.value" class="diagram-node__value">
            {{ data?.value }}
        </p>
        <p v-if="data?.description" class="diagram-node__description" v-html="data?.description"></p>
        <p v-if="data?.footer" class="diagram-node__footer" v-html="data?.footer"></p>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { NodeProps } from '@vue-flow/core'

type DiagramNodeData = {
    variant?: 'header' | 'primary' | 'sidebar' | 'model' | 'detail'
    title?: string
    subtitle?: string
    value?: string
    description?: string
    footer?: string
    badge?: string
    badgeTone?: 'green' | 'blue'
    accent?: string
}

const props = defineProps<NodeProps<DiagramNodeData>>()

const data = computed(() => props.data ?? {})

const wrapperClasses = computed(() => [
    'diagram-node',
    data.value.variant ? `diagram-node--${data.value.variant}` : 'diagram-node--detail'
])

const badgeClasses = computed(() => [
    'diagram-node__badge',
    data.value.badgeTone === 'blue' ? 'diagram-node__badge--blue' : 'diagram-node__badge--green'
])
</script>

<style scoped>
.diagram-node {
    position: relative;
    padding: 18px 22px;
    border-radius: 18px;
    color: #fff;
    font-family: 'Source Sans Pro', 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    box-shadow: 0 15px 35px rgba(3, 40, 77, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.1);
    width: 100%;
    height: 100%;
}

.diagram-node--header {
    background: #fff;
    color: #0d3962;
    border: 2px solid #84c341;
    text-align: center;
    padding: 24px;
    box-shadow: 0 15px 30px rgba(9, 39, 72, 0.1);
}

.diagram-node--primary {
    background: linear-gradient(135deg, #0a3c63, #0f5c8a);
}

.diagram-node--model {
    background: #0b3455;
}

.diagram-node--detail {
    background: #072944;
}

.diagram-node--sidebar {
    background: #6fbe44;
    color: #0a2a44;
    display: flex;
    align-items: center;
    justify-content: center;
    writing-mode: vertical-rl;
    text-orientation: upright;
    font-weight: 700;
    letter-spacing: 4px;
    font-size: 1.125rem;
    text-transform: uppercase;
    padding: 0;
    box-shadow: inset 0 0 0 6px rgba(255, 255, 255, 0.12);
}

.diagram-node__badge {
    position: absolute;
    top: 12px;
    right: 16px;
    font-size: 0.65rem;
    padding: 4px 10px;
    border-radius: 999px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.diagram-node__badge--green {
    background: #a2d955;
    color: #0b2f4d;
}

.diagram-node__badge--blue {
    background: #35a0ff;
    color: #fff;
}

.diagram-node__accent {
    font-size: 0.85rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin-bottom: 4px;
    opacity: 0.8;
}

.diagram-node__title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
}

.diagram-node--header .diagram-node__title {
    font-size: 1.4rem;
}

.diagram-node__subtitle {
    margin: 6px 0 0;
    font-size: 0.9rem;
    opacity: 0.9;
}

.diagram-node__value {
    margin: 8px 0 0;
    font-size: 1.25rem;
    font-weight: 700;
}

.diagram-node__description,
.diagram-node__footer {
    margin: 8px 0 0;
    font-size: 0.85rem;
    line-height: 1.3;
    color: #cfe4ff;
}

.diagram-node--header .diagram-node__description,
.diagram-node--header .diagram-node__subtitle,
.diagram-node--header .diagram-node__footer {
    color: #0d3962;
}
</style>
