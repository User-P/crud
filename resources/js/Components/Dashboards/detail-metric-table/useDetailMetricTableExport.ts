import { ref, type Ref } from 'vue'
import type { DetailMetricColumn } from './types'
import { buildCsvChunk, downloadBlob } from './csvUtils'
import { getCellValue } from './cellUtils'

export function useDetailMetricTableExport(params: {
    rows: Ref<Record<string, unknown>[]>
    columns: Ref<DetailMetricColumn[]>
    totalRecords: Ref<number>
    requestExportIndexes: () => Promise<number[]>
    exportLabel: Ref<string | undefined>
    maxRowsPerCsvFile: Ref<number>
}) {
    const exportingCsv = ref(false)

    async function exportCSV() {
        if (params.totalRecords.value === 0) return
        exportingCsv.value = true

        try {
            const indexes = await params.requestExportIndexes()
            const rows = indexes.map((i) => params.rows.value[i]).filter((row): row is Record<string, unknown> => !!row)
            const maxPerFile = params.maxRowsPerCsvFile.value ?? 0
            const baseName = `detalle-${(params.exportLabel.value || 'datos')
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')}-${new Date().toISOString().slice(0, 10)}`

            if (maxPerFile > 0 && rows.length > maxPerFile) {
                const JSZip = (await import('jszip')).default
                const zip = new JSZip()
                const totalParts = Math.ceil(rows.length / maxPerFile)
                const partNames: string[] = []

                for (let part = 0; part < totalParts; part++) {
                    const start = part * maxPerFile
                    const chunk = rows.slice(start, start + maxPerFile)
                    const csv = buildCsvChunk(chunk, params.columns.value, getCellValue)
                    const partName = `parte-${part + 1}-de-${totalParts}.csv`
                    partNames.push(partName)
                    zip.file(partName, '\ufeff' + csv, { createFolders: false })
                }

                const leeme = [
                    'Exportación en varias partes',
                    '────────────────────────────',
                    `Total de registros: ${rows.length.toLocaleString('es')}`,
                    `Partes: ${totalParts}`,
                    '',
                    'Archivos incluidos (en orden):',
                    ...partNames.map((name, i) => `  ${i + 1}. ${name}`),
                    '',
                    'Cada archivo tiene cabecera. Para unir en Excel/LibreOffice:',
                    'abrir el primero y luego insertar las filas de parte-2, parte-3, etc.',
                ].join('\r\n')
                zip.file('LEEME.txt', leeme, { createFolders: false })

                const blob = await zip.generateAsync({ type: 'blob' })
                downloadBlob(blob, `${baseName}.zip`)
            } else {
                const csv = buildCsvChunk(rows, params.columns.value, getCellValue)
                downloadBlob(new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8' }), `${baseName}.csv`)
            }
        } finally {
            exportingCsv.value = false
        }
    }

    return { exportingCsv, exportCSV }
}
