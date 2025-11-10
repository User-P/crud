import { ref } from 'vue'
import type { Edge } from '@vue-flow/core'
import { diagramRowConnections, handleId } from './useDiagramNodes'

type EdgeDescriptor = {
    id: string
    source: string
    target: string
    sourceHandle?: string
    targetHandle?: string
}

const staticEdges: EdgeDescriptor[] = [
    {
        id: 'e-header-general',
        source: 'header',
        target: 'general',
        sourceHandle: handleId('header', 'bottom'),
        targetHandle: handleId('general', 'top')
    },
    {
        id: 'e-header-infinite',
        source: 'header',
        target: 'infinite',
        sourceHandle: handleId('header', 'bottom'),
        targetHandle: handleId('infinite', 'top')
    },
    {
        id: 'e-score-general',
        source: 'score',
        target: 'general',
        sourceHandle: handleId('score', 'right'),
        targetHandle: handleId('general', 'left')
    },
    {
        id: 'e-score-infinite',
        source: 'score',
        target: 'infinite',
        sourceHandle: handleId('score', 'right'),
        targetHandle: handleId('infinite', 'left')
    }
]

const createEdge = (descriptor: EdgeDescriptor): Edge => ({
    id: descriptor.id,
    source: descriptor.source,
    target: descriptor.target,
    type: 'smoothstep',
    sourceHandle: descriptor.sourceHandle,
    targetHandle: descriptor.targetHandle
})

const edges = ref<Edge[]>([...staticEdges, ...diagramRowConnections].map(createEdge))

export function useDiagramEdges() {
    return { edges }
}
