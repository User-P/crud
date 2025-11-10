<template>
    <div class="diagram-wrapper h-[80vh] w-full border-2 border-dark rounded-xl overflow-hidden">
        <VueFlow :nodes="nodes" :edges="edges" :fit-view-on-init="true" :pan-on-drag="false" :zoom-on-scroll="false"
            :default-edge-options="defaultEdgeOptions" class="diagram-flow">
            <template #node-card="nodeProps">
                <SpecialNode v-bind="nodeProps" />
            </template>
        </VueFlow>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { MarkerType, VueFlow } from '@vue-flow/core'
import SpecialNode from './SpecialNode.vue'
import { useDiagramEdges } from './composables/useDiagramEdges'
import { useDiagramNodes } from './composables/useDiagramNodes'
import { fetchDiagramData, type DiagramApiResponse } from './services/diagram.service'

const apiData = ref<DiagramApiResponse | null>(null)

const { nodes, rowConnections } = useDiagramNodes(apiData)
const { edges } = useDiagramEdges(rowConnections)

onMounted(async () => {
    apiData.value = await fetchDiagramData()
})

const defaultEdgeOptions = {
    type: 'smoothstep',
    markerEnd: {
        type: MarkerType.ArrowClosed,
        color: '#7ac143',
        width: 28,
        height: 28
    },
    style: {
        stroke: '#7ac143',
        strokeWidth: 3
    }
} as const
</script>

<style>
@import '@vue-flow/core/dist/style.css';
@import '@vue-flow/core/dist/theme-default.css';

.diagram-wrapper {
    background: #f0f4fa;
}

.diagram-flow {
    background: linear-gradient(180deg, #f7f9fc 0%, #eef2f7 100%);
}

.diagram-flow .vue-flow__node {
    background: transparent;
    border: none;
}

.diagram-flow .vue-flow__edge-path {
    stroke-width: 3;
}
</style>
