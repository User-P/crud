/**
 * Meta tipada para columnas de TanStackTable (filtros, editable, etc.)
 */

export interface FilterOption {
    label: string
    value: string | number
}

export interface ColumnMetaBase {
    filterType?: 'text' | 'select' | 'numberRange' | 'dateRange'
    filterPlaceholder?: string
    filterMinPlaceholder?: string
    filterMaxPlaceholder?: string
    filterFromPlaceholder?: string
    filterToPlaceholder?: string
    filterOptions?: FilterOption[]
    /** Si true, la celda se puede editar inline (requiere slot #cell o editOptions). */
    editable?: boolean
    /** Tipo de editor inline (si no se provee editOptions). */
    editType?: 'text' | 'number' | 'date' | 'select'
    /** Opciones para el editor inline tipo select (cuando editable y editType === 'select'). */
    editOptions?: FilterOption[]
    /** Placeholder del input cuando está vacío. */
    editPlaceholder?: string
}

export type RowClickMode = 'none' | 'expand' | 'drawer' | 'custom'
