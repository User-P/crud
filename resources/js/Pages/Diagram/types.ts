import type { CSSProperties } from 'vue'
import type { HandleType, Position } from '@vue-flow/core'

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

export type DiagramHandle = {
    id: string
    type: HandleType
    position: Position
    class?: string
    style?: CSSProperties
}

export type HandleConfig = Omit<DiagramHandle, 'id'> & { key: string }

export type RowDefinition = {
    key: string
    model: DiagramNodeData
    users: DiagramNodeData
    sources: DiagramNodeData
    outcome: DiagramNodeData
}
