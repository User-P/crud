import { computed, type ComputedRef, type Ref } from 'vue'
import type { DetailMetricColumn } from './types'
import type { TableSort } from './types'
import type { DetailMetricTableProps } from './detailMetricTableProps'

export function useDetailMetricTableStyles(params: {
    props: Readonly<DetailMetricTableProps>
    sorting: Ref<TableSort>
    isRowSelected: (datasetIndex: number) => boolean
    /** Si el consumidor pasó `#selection-actions`, mostrar barra aunque el resto de controles estén ocultos. */
    hasSelectionActionsSlot?: ComputedRef<boolean>
}) {
    const showToolbar = computed(() => {
        const slotBar = params.hasSelectionActionsSlot?.value && params.props.enableRowSelection
        return (
            params.props.showSearch ||
            params.props.showExportButton ||
            params.props.showSearchMatches ||
            params.props.showProcessingStatus ||
            (params.props.enableRowSelection && params.props.showSelectionCount) ||
            (params.props.enableRowSelection && params.props.showSelectAllFilteredButton) ||
            !!slotBar
        )
    })

    const tableViewportStyle = computed(() => ({ maxHeight: params.props.maxBodyHeight }))

    const headerClass = computed(() =>
        params.props.stickyHeader
            ? 'sticky top-0 z-10 border-b border-[var(--th-border)] bg-[var(--th-input-bg)]'
            : 'border-b border-[var(--th-border)] bg-[var(--th-input-bg)]'
    )

    const bodyCellClass = computed(() => (params.props.compact ? 'py-2' : 'py-2.5'))

    const selectionHeaderPaddingClass = computed(() => (params.props.compact ? 'py-2.5' : 'py-3'))

    function headerCellClass(col: DetailMetricColumn): string {
        const isSorted = params.sorting.value?.key === col.key
        const basePadding = params.props.compact ? 'py-2.5' : 'py-3'
        if (!isSorted) return basePadding
        return `${basePadding} bg-[var(--th-item-hover-bg)]/30 text-[color:var(--th-item-active-color)]`
    }

    function rowClass(rowIndex: number, datasetIndex: number): string {
        const classes: string[] = []
        if (params.props.stripedRows) {
            classes.push(rowIndex % 2 === 0 ? 'bg-[var(--th-input-bg)]' : 'bg-[var(--th-item-hover-bg)]/20')
        }
        if (params.props.rowHover) {
            classes.push('hover:bg-[var(--th-item-hover-bg)]/35')
        }
        if (params.props.enableRowSelection && params.isRowSelected(datasetIndex)) {
            classes.push('ring-1 ring-inset ring-[var(--th-item-active-color)]/25')
        }
        return classes.join(' ')
    }

    return {
        showToolbar,
        tableViewportStyle,
        headerClass,
        bodyCellClass,
        selectionHeaderPaddingClass,
        headerCellClass,
        rowClass,
    }
}
