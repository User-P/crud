export interface DetailMetricColumn<T = Record<string, unknown>> {
    key: string
    header: string
    exportLabel?: string
    sortable?: boolean
    format?: (value: unknown, row: T) => string
    exportFormat?: (value: unknown, row: T) => string
    class?: string
    numeric?: boolean
}

export type TableSort = { key: string; desc: boolean } | null
