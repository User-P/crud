import { ref, watchEffect, type CSSProperties, type Ref } from 'vue'
import { Position } from '@vue-flow/core'
import type { Node } from '@vue-flow/core'
import type { DiagramNodeData, DiagramHandle, HandleConfig, RowDefinition } from '../types'
import { defaultDiagramData, type DiagramApiResponse } from '../services/diagram.service'

export type RowConnection = {
    id: string
    source: string
    target: string
    sourceHandle: string
    targetHandle: string
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
    options: { sourcePosition?: Position; targetPosition?: Position } = {}
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

const buildRowNodes = (rows: RowDefinition[]) => {
    const rowNodes: Node<DiagramNodeData>[] = []
    const connections: RowConnection[] = []

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

        connections.push(
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

    return { rowNodes, connections }
}

const buildNodesFromData = (data: DiagramApiResponse) => {
    const nodes: Node<DiagramNodeData>[] = [
        createNode(
            'header',
            { x: 420, y: -60 },
            withCoords(data.header, [{ key: 'bottom', type: 'source', position: Position.Bottom }], 'header'),
            { width: '460px', height: '120px' },
            { sourcePosition: Position.Bottom }
        ),
        createNode(
            'general',
            { x: 320, y: 70 },
            withCoords(
                data.general,
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
                data.infinite,
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
            withCoords(data.score, [{ key: 'right', type: 'source', position: Position.Right }], 'score'),
            {},
            { sourcePosition: Position.Right }
        ),
        createNode(
            'ml-banner',
            { x: -120, y: 210 },
            withCoords(data.sidebar),
            { width: '120px', height: '500px' }
        )
    ]

    const { rowNodes, connections } = buildRowNodes(data.rows)

    return { nodes: [...nodes, ...rowNodes], connections }
}

export function useDiagramNodes(apiData?: Ref<DiagramApiResponse | null>) {
    const nodes = ref<Node<DiagramNodeData>[]>([])
    const rowConnections = ref<RowConnection[]>([])

    const applyData = (payload?: DiagramApiResponse | null) => {
        const { nodes: builtNodes, connections } = buildNodesFromData(payload ?? defaultDiagramData)
        nodes.value = builtNodes
        rowConnections.value = connections
    }

    if (apiData) {
        watchEffect(() => {
            applyData(apiData.value)
        })
    } else {
        applyData()
    }

    return { nodes, rowConnections }
}
