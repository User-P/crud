import { ref, type CSSProperties } from 'vue'
import { Position } from '@vue-flow/core'
import type { HandleType, Node } from '@vue-flow/core'

export type DiagramHandle = {
    id: string
    type: HandleType
    position: Position
    class?: string
    style?: CSSProperties
}

export type DiagramNodeData = {
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

export type RowConnection = {
    id: string
    source: string
    target: string
    sourceHandle: string
    targetHandle: string
}

type HandleConfig = Omit<DiagramHandle, 'id'> & { key: string }

type NodePlacementOptions = {
    sourcePosition?: Position
    targetPosition?: Position
}

type RowDefinition = {
    key: string
    model: DiagramNodeData
    users: DiagramNodeData
    sources: DiagramNodeData
    outcome: DiagramNodeData
}

const baseNodeStyle: CSSProperties = { width: '260px' }
const rowStartY = 150
const rowGap = 115
const SHOW_COORDS = true

export const handleId = (nodeId: string, key: string) => `${nodeId}-${key}`

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
    styleOverrides: CSSProperties = {},
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

const withCoords = (data: DiagramNodeData, handles: HandleConfig[] = [], nodeId?: string) => ({
    ...data,
    showCoords: data.showCoords ?? SHOW_COORDS,
    handles: nodeId ? createHandles(nodeId, handles) : undefined
})

const staticNodes: Node<DiagramNodeData>[] = [
    createNode(
        'header',
        { x: 420, y: -60 },
        withCoords(
            {
                variant: 'header',
                title: 'Datalake i3',
                subtitle: 'Modelos de Información'
            },
            [{ key: 'bottom', type: 'source', position: Position.Bottom }],
            'header'
        ),
        { width: '460px', height: '120px' },
        { sourcePosition: Position.Bottom }
    ),
    createNode(
        'general',
        { x: 320, y: 70 },
        withCoords(
            {
                variant: 'primary',
                title: 'GENERAL',
                subtitle: 'Usuarios Monitoreados',
                value: '32,795 / 1,425',
                description: 'Prom del 15 al 19',
                footer: 'Plantilla | DLP | CASB | Cyberark | Colas Impresión<br/>Políticas DLP – 10 | Reglas DLP – 27'
            },
            [
                { key: 'top', type: 'target', position: Position.Top },
                { key: 'left', type: 'target', position: Position.Left }
            ],
            'general'
        ),
        { width: '360px' },
        { targetPosition: Position.Top }
    ),
    createNode(
        'infinite',
        { x: 780, y: 70 },
        withCoords(
            {
                variant: 'primary',
                title: 'INFINITE',
                subtitle: 'Usuarios Monitoreados',
                value: '173 / 4',
                description: 'Prom del 15 al 19',
                footer: 'Modelo General<br/>Políticas DLP – 3 | Reglas DLP – 3'
            },
            [
                { key: 'top', type: 'target', position: Position.Top },
                { key: 'left', type: 'target', position: Position.Left }
            ],
            'infinite'
        ),
        { width: '360px' },
        { targetPosition: Position.Top }
    ),
    createNode(
        'score',
        { x: 150, y: 40 },
        withCoords(
            {
                variant: 'model',
                title: 'Score Infractores DLP',
                badge: 'COMPLETADO'
            },
            [{ key: 'right', type: 'source', position: Position.Right }],
            'score'
        ),
        {},
        { sourcePosition: Position.Right }
    ),
    createNode(
        'ml-banner',
        { x: -120, y: 210 },
        withCoords({
            variant: 'sidebar',
            title: 'MODELOS DE ML'
        }),
        { width: '120px', height: '500px' }
    )
]

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
const rowConnections: RowConnection[] = []

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
            withCoords(row.model, [{ key: 'right', type: 'source', position: Position.Right }], modelId),
            {},
            { sourcePosition: Position.Right }
        ),
        createNode(
            usersId,
            { x: 520, y },
            withCoords(
                row.users,
                [
                    { key: 'left', type: 'target', position: Position.Left },
                    { key: 'right', type: 'source', position: Position.Right }
                ],
                usersId
            ),
            {},
            { targetPosition: Position.Left, sourcePosition: Position.Right }
        ),
        createNode(
            sourcesId,
            { x: 840, y },
            withCoords(
                row.sources,
                [
                    { key: 'left', type: 'target', position: Position.Left },
                    { key: 'right', type: 'source', position: Position.Right }
                ],
                sourcesId
            ),
            {},
            { targetPosition: Position.Left, sourcePosition: Position.Right }
        ),
        createNode(
            outcomeId,
            { x: 1150, y },
            withCoords(row.outcome, [{ key: 'left', type: 'target', position: Position.Left }], outcomeId),
            {},
            { targetPosition: Position.Left }
        )
    )

    rowConnections.push(
        {
            id: `e-${row.key}-model-users`,
            source: modelId,
            target: usersId,
            sourceHandle: handleId(modelId, 'right'),
            targetHandle: handleId(usersId, 'left')
        },
        {
            id: `e-${row.key}-users-sources`,
            source: usersId,
            target: sourcesId,
            sourceHandle: handleId(usersId, 'right'),
            targetHandle: handleId(sourcesId, 'left')
        },
        {
            id: `e-${row.key}-sources-outcome`,
            source: sourcesId,
            target: outcomeId,
            sourceHandle: handleId(sourcesId, 'right'),
            targetHandle: handleId(outcomeId, 'left')
        }
    )
})

const nodes = ref<Node<DiagramNodeData>[]>([...staticNodes, ...rowNodes])

export const diagramRowConnections = rowConnections

export function useDiagramNodes() {
    return { nodes }
}
