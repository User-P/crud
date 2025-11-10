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
import { ref, type CSSProperties } from 'vue'
import { MarkerType, Position, VueFlow } from '@vue-flow/core'
import type { Edge, HandleType, Node } from '@vue-flow/core'
import SpecialNode from './SpecialNode.vue'

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
    showCoords?: boolean
    handles?: DiagramHandle[]
}

type RowDefinition = {
    key: string
    model: DiagramNodeData
    users: DiagramNodeData
    sources: DiagramNodeData
    outcome: DiagramNodeData
}

type DiagramHandle = {
    id: string
    type: HandleType
    position: Position
    class?: string
    style?: CSSProperties
}

type HandleConfig = Omit<DiagramHandle, 'id'> & { key: string }

type NodePlacementOptions = {
    sourcePosition?: Position
    targetPosition?: Position
}

const baseNodeStyle = { width: '260px' }

const SHOW_COORDS = true

const handleId = (nodeId: string, key: string) => `${nodeId}-${key}`

const createHandles = (nodeId: string, configs: HandleConfig[] = []): DiagramHandle[] =>
    configs.map((config) => ({
        id: handleId(nodeId, config.key),
        type: config.type,
        position: config.position,
        class: config.class ?? 'diagram-node__handle',
        style: config.style
    }))

const createNode = (
    id: string,
    position: { x: number; y: number },
    data: DiagramNodeData,
    styleOverrides: Record<string, string | number> = {},
    options: NodePlacementOptions = {}
): Node<DiagramNodeData> => ({
    id,
    type: 'card',
    position,
    data,
    draggable: true,
    selectable: false,
    connectable: false,
    focusable: false,
    style: { ...baseNodeStyle, ...styleOverrides },
    ...options
})

const createEdge = (id: string, source: string, target: string, options: Partial<Edge> = {}): Edge => ({
    id,
    source,
    target,
    type: 'smoothstep',
    ...options
})

const staticNodes: Node<DiagramNodeData>[] = [
    createNode(
        'header',
        { x: 420, y: -60 },
        {
            variant: 'header',
            title: 'Datalake i3',
            subtitle: 'Modelos de Información',
            showCoords: SHOW_COORDS,
            handles: createHandles('header', [{ key: 'bottom', type: 'source', position: Position.Bottom }])
        },
        { width: '460px', height: '120px' },
        { sourcePosition: Position.Bottom }
    ),
    createNode(
        'general',
        { x: 320, y: 70 },
        {
            variant: 'primary',
            title: 'GENERAL',
            subtitle: 'Usuarios Monitoreados',
            value: '32,795 / 1,425',
            description: 'Prom del 15 al 19',
            footer:
                'Plantilla | DLP | CASB | Cyberark | Colas Impresión<br/>Políticas DLP – 10 | Reglas DLP – 27',
            showCoords: SHOW_COORDS,
            handles: createHandles('general', [
                { key: 'top', type: 'target', position: Position.Top },
                { key: 'left', type: 'target', position: Position.Left }
            ])
        },
        { width: '360px' },
        { targetPosition: Position.Top }
    ),
    createNode(
        'infinite',
        { x: 780, y: 70 },
        {
            variant: 'primary',
            title: 'INFINITE',
            subtitle: 'Usuarios Monitoreados',
            value: '173 / 4',
            description: 'Prom del 15 al 19',
            footer: 'Modelo General<br/>Políticas DLP – 3 | Reglas DLP – 3',
            showCoords: SHOW_COORDS,
            handles: createHandles('infinite', [
                { key: 'top', type: 'target', position: Position.Top },
                { key: 'left', type: 'target', position: Position.Left }
            ])
        },
        { width: '360px' },
        { targetPosition: Position.Top }
    ),
    createNode(
        'score',
        { x: 150, y: 40 },
        {
            variant: 'model',
            title: 'Score Infractores DLP',
            badge: 'COMPLETADO',
            showCoords: SHOW_COORDS,
            handles: createHandles('score', [{ key: 'right', type: 'source', position: Position.Right }])
        },
        {},
        { sourcePosition: Position.Right }
    ),
    createNode(
        'ml-banner',
        { x: -120, y: 210 },
        {
            variant: 'sidebar',
            title: 'MODELOS DE ML',
            showCoords: SHOW_COORDS
        },
        { width: '120px', height: '500px' }
    )
]

const rowStartY = 150
const rowGap = 115

const rows: RowDefinition[] = [
    {
        key: 'tecleo',
        model: { variant: 'model', title: 'Tabla de Tecleo Alnova', badge: 'COMPLETADO' },
        users: {
            variant: 'detail',
            title: 'Usuarios Monitoreados',
            value: '54,123',
            description: '20,009 prom del 15 al 19'
        },
        sources: {
            variant: 'detail',
            title: 'Fuentes',
            description: 'Tabla de Tecleo financiero | Plantilla'
        },
        outcome: {
            variant: 'detail',
            title: 'Transacciones',
            value: '3714 Total / 59 modelo'
        }
    },
    {
        key: 'empleo',
        model: { variant: 'model', title: 'Búsqueda de Empleo', badge: 'COMPLETADO' },
        users: {
            variant: 'detail',
            title: 'Usuarios Monitoreados',
            value: '26,781 / 6,131'
        },
        sources: {
            variant: 'detail',
            title: 'Fuentes',
            description: 'Mónaco | Teramind | Accesos Físicos | Log Exchange | Plantilla | DLP | Teams'
        },
        outcome: {
            variant: 'detail',
            title: 'Preguntas Clave',
            value: '16'
        }
    },
    {
        key: 'sdt',
        model: { variant: 'model', title: 'SDT', badge: 'En Proceso', badgeTone: 'blue' },
        users: {
            variant: 'detail',
            title: 'Usuarios Monitoreados',
            value: '- / -'
        },
        sources: {
            variant: 'detail',
            title: 'Fuentes',
            description: 'Mónaco | Teramind | Accesos Físicos | Log Exchange | Plantilla | DLP | Teams'
        },
        outcome: {
            variant: 'detail',
            title: 'Preguntas Clave',
            value: '2'
        }
    },
    {
        key: 'banca',
        model: { variant: 'model', title: 'Banca Digital', badge: 'En Proceso', badgeTone: 'blue' },
        users: {
            variant: 'detail',
            title: 'Usuarios Monitoreados',
            value: '- / -'
        },
        sources: {
            variant: 'detail',
            title: 'Fuentes',
            description: 'Flujos seleccionados de Banca Digital'
        },
        outcome: {
            variant: 'detail',
            title: 'Flujos',
            value: '4'
        }
    }
]

const rowNodes: Node<DiagramNodeData>[] = []
const rowEdges: Edge[] = []

rows.forEach((row, index) => {
    const y = rowStartY + index * rowGap
    const modelId = `${row.key}-model`
    const usersId = `${row.key}-users`
    const sourcesId = `${row.key}-sources`
    const outcomeId = `${row.key}-outcome`

    rowNodes.push(
        createNode(
            modelId,
            { x: 150, y },
            {
                ...row.model,
                showCoords: SHOW_COORDS,
                handles: createHandles(modelId, [{ key: 'right', type: 'source', position: Position.Right }])
            },
            {},
            { sourcePosition: Position.Right }
        ),
        createNode(
            usersId,
            { x: 520, y },
            {
                ...row.users,
                showCoords: SHOW_COORDS,
                handles: createHandles(usersId, [
                    { key: 'left', type: 'target', position: Position.Left },
                    { key: 'right', type: 'source', position: Position.Right }
                ])
            },
            {},
            { targetPosition: Position.Left, sourcePosition: Position.Right }
        ),
        createNode(
            sourcesId,
            { x: 840, y },
            {
                ...row.sources,
                showCoords: SHOW_COORDS,
                handles: createHandles(sourcesId, [
                    { key: 'left', type: 'target', position: Position.Left },
                    { key: 'right', type: 'source', position: Position.Right }
                ])
            },
            {},
            { targetPosition: Position.Left, sourcePosition: Position.Right }
        ),
        createNode(
            outcomeId,
            { x: 1150, y },
            {
                ...row.outcome,
                showCoords: SHOW_COORDS,
                handles: createHandles(outcomeId, [{ key: 'left', type: 'target', position: Position.Left }])
            },
            {},
            { targetPosition: Position.Left }
        )
    )

    rowEdges.push(
        createEdge(`e-${row.key}-model-users`, modelId, usersId, {
            sourceHandle: handleId(modelId, 'right'),
            targetHandle: handleId(usersId, 'left')
        }),
        createEdge(`e-${row.key}-users-sources`, usersId, sourcesId, {
            sourceHandle: handleId(usersId, 'right'),
            targetHandle: handleId(sourcesId, 'left')
        }),
        createEdge(`e-${row.key}-sources-outcome`, sourcesId, outcomeId, {
            sourceHandle: handleId(sourcesId, 'right'),
            targetHandle: handleId(outcomeId, 'left')
        })
    )
})

const nodes = ref<Node<DiagramNodeData>[]>([...staticNodes, ...rowNodes])

const edges = ref<Edge[]>([
    createEdge('e-header-general', 'header', 'general', {
        sourceHandle: handleId('header', 'bottom'),
        targetHandle: handleId('general', 'top')
    }),
    createEdge('e-header-infinite', 'header', 'infinite', {
        sourceHandle: handleId('header', 'bottom'),
        targetHandle: handleId('infinite', 'top')
    }),
    createEdge('e-score-general', 'score', 'general', {
        sourceHandle: handleId('score', 'right'),
        targetHandle: handleId('general', 'left')
    }),
    createEdge('e-score-infinite', 'score', 'infinite', {
        sourceHandle: handleId('score', 'right'),
        targetHandle: handleId('infinite', 'left')
    }),
    ...rowEdges
])

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
