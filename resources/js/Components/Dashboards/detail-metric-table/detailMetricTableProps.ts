import type { DetailMetricColumn } from './types'

/** Props públicas de `DetailMetricTable` (tipado centralizado). */
export interface DetailMetricTableProps {
    rows: Record<string, unknown>[]
    columns: DetailMetricColumn[]
    searchPlaceholder?: string
    exportLabel?: string
    rowsPerPage?: number
    rowsPerPageOptions?: number[]
    showSearch?: boolean
    showExportButton?: boolean
    showSearchMatches?: boolean
    showProcessingStatus?: boolean
    allowClearSearch?: boolean
    showFooter?: boolean
    showRecordCount?: boolean
    showPagination?: boolean
    showPageIndicator?: boolean
    showRowsPerPageSelector?: boolean
    stickyHeader?: boolean
    stripedRows?: boolean
    rowHover?: boolean
    compact?: boolean
    maxBodyHeight?: string
    maxRowsPerCsvFile?: number
    enableRowSelection?: boolean
    /** Botón en la barra para seleccionar todas las filas filtradas (todas las páginas), no solo la vista actual. */
    showSelectAllFilteredButton?: boolean
    showSelectionCount?: boolean
    clearSelectionOnDataChange?: boolean
    clearSelectionOnFilterChange?: boolean
}

export const DETAIL_METRIC_TABLE_DEFAULTS: Required<
    Pick<
        DetailMetricTableProps,
        | 'searchPlaceholder'
        | 'rowsPerPage'
        | 'showSearch'
        | 'showExportButton'
        | 'showSearchMatches'
        | 'showProcessingStatus'
        | 'allowClearSearch'
        | 'showFooter'
        | 'showRecordCount'
        | 'showPagination'
        | 'showPageIndicator'
        | 'showRowsPerPageSelector'
        | 'stickyHeader'
        | 'stripedRows'
        | 'rowHover'
        | 'compact'
        | 'maxBodyHeight'
        | 'maxRowsPerCsvFile'
        | 'enableRowSelection'
        | 'showSelectAllFilteredButton'
        | 'showSelectionCount'
        | 'clearSelectionOnDataChange'
        | 'clearSelectionOnFilterChange'
    >
> = {
    searchPlaceholder: 'Buscar en la tabla…',
    rowsPerPage: 10,
    showSearch: true,
    showExportButton: true,
    showSearchMatches: true,
    showProcessingStatus: true,
    allowClearSearch: true,
    showFooter: true,
    showRecordCount: true,
    showPagination: true,
    showPageIndicator: true,
    showRowsPerPageSelector: true,
    stickyHeader: true,
    stripedRows: true,
    rowHover: true,
    compact: false,
    maxBodyHeight: '62vh',
    maxRowsPerCsvFile: 0,
    enableRowSelection: false,
    showSelectAllFilteredButton: true,
    showSelectionCount: true,
    clearSelectionOnDataChange: true,
    clearSelectionOnFilterChange: true,
}
