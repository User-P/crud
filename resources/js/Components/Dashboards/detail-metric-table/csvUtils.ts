import type { DetailMetricColumn } from './types'

export function escapeCsvCell(value: string): string {
    if (/[",\n\r]/.test(value)) return `"${value.replace(/"/g, '""')}"`
    return value
}

export function buildCsvChunk(
    chunkRows: Record<string, unknown>[],
    columns: DetailMetricColumn[],
    getCellValue: (row: Record<string, unknown>, key: string) => unknown
): string {
    const header = columns.map((c) => escapeCsvCell(c.exportLabel ?? c.header)).join(',')
    const lines = chunkRows.map((row) =>
        columns
            .map((c) => {
                const raw = getCellValue(row, c.key)
                const str = c.exportFormat
                    ? c.exportFormat(raw, row as Record<string, unknown>)
                    : c.format
                      ? c.format(raw, row as Record<string, unknown>)
                      : raw == null
                        ? ''
                        : String(raw)
                return escapeCsvCell(str)
            })
            .join(',')
    )
    return [header, ...lines].join('\r\n')
}

export function downloadBlob(blob: Blob, filename: string) {
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = filename
    a.click()
    URL.revokeObjectURL(url)
}
