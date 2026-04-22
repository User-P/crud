import type { VNode } from 'vue'

export interface DetailMetricColumn<T = Record<string, unknown>> {
    key: string
    header: string
    exportLabel?: string
    sortable?: boolean
    format?: (value: unknown, row: T) => string
    exportFormat?: (value: unknown, row: T) => string
    /**
     * Renderizado avanzado de celda (badges, iconos, layouts).
     * Si existe, tiene prioridad sobre `format` en la UI (no afecta CSV salvo que lo manejes en exportFormat).
     */
    cellRender?: (value: unknown, row: T) => VNode | string | number | null | undefined
    class?: string
    numeric?: boolean
}

export type TableSort = { key: string; desc: boolean } | null
