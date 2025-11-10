<template>
    <div class="diagram-wrapper h-[80vh] w-full border-2 border-dark rounded-xl overflow-hidden">
        <VueFlow
            :nodes="nodes"
            :edges="edges"
            :fit-view-on-init="true"
            :pan-on-drag="false"
            :zoom-on-scroll="false"
            :default-edge-options="defaultEdgeOptions"
            class="diagram-flow"
        >
            <template #node-card="nodeProps">
                <SpecialNode v-bind="nodeProps" />
            </template>
        </VueFlow>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { MarkerType, Position, VueFlow } from '@vue-flow/core'
import type { Edge, Node } from '@vue-flow/core'
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
}

type RowDefinition = {
    key: string
    model: DiagramNodeData
    users: DiagramNodeData
    sources: DiagramNodeData
    outcome: DiagramNodeData
}

type NodePlacementOptions = {
    sourcePosition?: Position
    targetPosition?: Position
}

const baseNodeStyle = { width: '260px' }

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
    draggable: false,
    selectable: false,
    connectable: false,
    focusable: false,
    style: { ...baseNodeStyle, ...styleOverrides },
    ...options
})

const createEdge = (id: string, source: string, target: string): Edge => ({
    id,
    source,
    target,
    type: 'smoothstep'
})

const staticNodes: Node<DiagramNodeData>[] = [
    createNode(
        'header',
        { x: 420, y: -60 },
        { variant: 'header', title: 'Datalake i3', subtitle: 'Modelos de Información' },
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
                'Plantilla | DLP | CASB | Cyberark | Colas Impresión<br/>Políticas DLP – 10 | Reglas DLP – 27'
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
            footer: 'Modelo General<br/>Políticas DLP – 3 | Reglas DLP – 3'
        },
        { width: '360px' },
        { targetPosition: Position.Top }
    ),
    createNode(
        'score',
        { x: 150, y: 40 },
        { variant: 'model', title: 'Score Infractores DLP', badge: 'COMPLETADO' },
        {},
        { sourcePosition: Position.Right }
    ),
    createNode(
        'ml-banner',
        { x: -120, y: 210 },
        {
            variant: 'sidebar',
            title: 'MODELOS DE ML'
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

    rowNodes.push(
        createNode(`${row.key}-model`, { x: 150, y }, row.model, {}, { sourcePosition: Position.Right }),
        createNode(
            `${row.key}-users`,
            { x: 520, y },
            row.users,
            {},
            { targetPosition: Position.Left, sourcePosition: Position.Right }
        ),
        createNode(
            `${row.key}-sources`,
            { x: 840, y },
            row.sources,
            {},
            { targetPosition: Position.Left, sourcePosition: Position.Right }
        ),
        createNode(
            `${row.key}-outcome`,
            { x: 1150, y },
            row.outcome,
            {},
            { targetPosition: Position.Left }
        )
    )

    rowEdges.push(
        createEdge(`e-${row.key}-model-users`, `${row.key}-model`, `${row.key}-users`),
        createEdge(`e-${row.key}-users-sources`, `${row.key}-users`, `${row.key}-sources`),
        createEdge(`e-${row.key}-sources-outcome`, `${row.key}-sources`, `${row.key}-outcome`)
    )
})

const nodes = ref<Node<DiagramNodeData>[]>([...staticNodes, ...rowNodes])

const edges = ref<Edge[]>([
    createEdge('e-header-general', 'header', 'general'),
    createEdge('e-header-infinite', 'header', 'infinite'),
    createEdge('e-score-general', 'score', 'general'),
    createEdge('e-score-infinite', 'score', 'infinite'),
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
