import { h, type VNode } from 'vue'
import type { DetailMetricColumn } from './types'

export function getCellValue(row: Record<string, unknown>, key: string): unknown {
    return row[key]
}

export function formatCellValue(row: Record<string, unknown>, col: DetailMetricColumn): string {
    const raw = getCellValue(row, col.key)
    if (col.format) return col.format(raw, row as Record<string, unknown>)
    return raw == null ? '' : String(raw)
}

export function renderCell(col: DetailMetricColumn, row: Record<string, unknown>): VNode {
    const raw = getCellValue(row, col.key)
    const vnode = col.cellRender?.(raw, row as Record<string, unknown>)
    if (vnode == null) return h('span', formatCellValue(row, col))
    if (typeof vnode === 'string' || typeof vnode === 'number') return h('span', String(vnode))
    return vnode as VNode
}
