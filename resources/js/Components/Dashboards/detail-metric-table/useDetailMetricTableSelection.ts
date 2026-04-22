import { computed, watch, type Ref } from 'vue'
import type { DetailMetricTableProps } from './detailMetricTableProps'

export function useDetailMetricTableSelection(params: {
    props: Readonly<DetailMetricTableProps>
    rows: Ref<Record<string, unknown>[]>
    columns: Ref<unknown[]>
    pageRowIndexes: Ref<number[]>
    searchText: Ref<string>
    selectedIndexes: Ref<number[]>
    onSelectionChange: (indexes: number[], rows: Record<string, unknown>[]) => void
}) {
    const selectedSet = computed(() => new Set(params.selectedIndexes.value))

    const selectedIndexesSorted = computed(() => [...params.selectedIndexes.value].sort((a, b) => a - b))

    const selectedCount = computed(() => params.selectedIndexes.value.length)

    const isAllPageSelected = computed(() => {
        const idxs = params.pageRowIndexes.value
        if (!idxs.length) return false
        return idxs.every((i) => selectedSet.value.has(i))
    })

    const isSomePageSelected = computed(() => {
        const idxs = params.pageRowIndexes.value
        if (!idxs.length) return false
        const any = idxs.some((i) => selectedSet.value.has(i))
        return any && !isAllPageSelected.value
    })

    function rowDatasetIndex(pageRowIndex: number): number {
        return params.pageRowIndexes.value[pageRowIndex] ?? pageRowIndex
    }

    function isRowSelected(datasetIndex: number): boolean {
        return selectedSet.value.has(datasetIndex)
    }

    function setSelectedIndexes(next: number[]) {
        const max = params.rows.value.length
        const unique = Array.from(new Set(next.filter((i) => i >= 0 && i < max))).sort((a, b) => a - b)
        params.selectedIndexes.value = unique
        params.onSelectionChange(
            unique,
            unique.map((i) => params.rows.value[i]).filter((r): r is Record<string, unknown> => !!r)
        )
    }

    function toggleRowSelected(datasetIndex: number, selected: boolean) {
        const next = new Set(params.selectedIndexes.value)
        if (selected) next.add(datasetIndex)
        else next.delete(datasetIndex)
        setSelectedIndexes([...next])
    }

    function toggleSelectAllPage(selected: boolean) {
        const idxs = params.pageRowIndexes.value
        const next = new Set(params.selectedIndexes.value)
        if (selected) idxs.forEach((i) => next.add(i))
        else idxs.forEach((i) => next.delete(i))
        setSelectedIndexes([...next])
    }

    function clearSelection() {
        setSelectedIndexes([])
    }

    watch(
        () => [params.rows.value, params.columns.value] as const,
        () => {
            if (params.props.enableRowSelection && params.props.clearSelectionOnDataChange) clearSelection()
        }
    )

    watch(
        () => params.searchText.value,
        () => {
            if (params.props.enableRowSelection && params.props.clearSelectionOnFilterChange) clearSelection()
        }
    )

    watch(
        () => params.rows.value.length,
        () => {
            if (!params.props.enableRowSelection) return
            const max = params.rows.value.length
            const pruned = params.selectedIndexes.value.filter((i) => i >= 0 && i < max)
            if (pruned.length !== params.selectedIndexes.value.length) setSelectedIndexes(pruned)
        }
    )

    return {
        selectedIndexesSorted,
        selectedCount,
        isAllPageSelected,
        isSomePageSelected,
        rowDatasetIndex,
        isRowSelected,
        toggleRowSelected,
        toggleSelectAllPage,
        clearSelection,
    }
}
