# Componentes de Tablas (TanStack)

Este folder contiene la tabla reutilizable basada en TanStack Table y componentes auxiliares (filtros/paginación).

## Archivos

- `TanStackTable.vue`: componente principal (sorting, filtros, selección, paginación, sticky header, loading, acciones por fila).
- `TablePagination.vue`: paginación simple con primera/última página y rango mostrado.
- `TableFilters.vue`: filtros por columna (inputs por columna filtrable).

## Uso básico

```vue
<script setup lang="ts">
import { ref } from 'vue'
import { faker } from '@faker-js/faker'
import { type ColumnDef } from '@tanstack/vue-table'
import TanStackTable from '@/Components/Tables/TanStackTable.vue'
import TablePagination from '@/Components/Tables/TablePagination.vue'
import TableFilters from '@/Components/Tables/TableFilters.vue'

type Row = { id: string; name: string; email: string }

const data = ref<Row[]>(Array.from({ length: 20 }, () => ({
  id: faker.string.uuid(),
  name: faker.person.fullName(),
  email: faker.internet.email(),
})))

const columns: ColumnDef<Row>[] = [
  {
    accessorKey: 'id',
    header: 'ID',
    cell: (info) => info.getValue(),
    enableColumnFilter: true,
    filterFn: 'includesString',
    meta: { filterPlaceholder: 'Filtrar ID' },
  },
  {
    accessorKey: 'name',
    header: 'Nombre',
    cell: (info) => info.getValue(),
    enableColumnFilter: true,
    filterFn: 'includesString',
    meta: { filterPlaceholder: 'Filtrar nombre' },
  },
  {
    accessorKey: 'email',
    header: 'Email',
    cell: (info) => info.getValue(),
    enableColumnFilter: true,
    filterFn: 'includesString',
    meta: { filterPlaceholder: 'Filtrar email' },
  },
]
</script>

<template>
  <TanStackTable
    :data="data"
    :columns="columns"
    enable-sorting
    enable-pagination
    enable-global-filter
    enable-column-filters
    selectable
    :page-size="10"
    show-sticky-header
  >
    <template #toolbar="{ table }">
      <div class="flex items-center gap-2">
        <TableFilters :table="table" />
      </div>
    </template>

    <template #pagination="{ table }">
      <TablePagination :table="table" :page-size-options="[5, 10, 25]" />
    </template>

    <template #row-actions="{ original }">
      <button type="button">Editar {{ original.name }}</button>
    </template>
  </TanStackTable>
</template>
```

## TanStackTable.vue

### Props

- `data` (**required**): arreglo de datos.
- `columns` (**required**): definición de columnas `ColumnDef<T>`.
- `getRowId` (opcional): función para generar ID de fila estable.
- `enableSorting` (opcional): activa ordenamiento.
- `enablePagination` (opcional): activa paginación.
- `pageSize` (opcional): tamaño de página inicial.
- `emptyText` (opcional): texto cuando no hay datos.
- `loading` (opcional): muestra estado de carga y bloquea selección.
- `selectable` (opcional): activa selección de filas.
- `enableGlobalFilter` (opcional): activa filtro global (input interno).
- `enableColumnFilters` (opcional): activa filtros por columna (para `TableFilters`).
- `showStickyHeader` (opcional): sticky header en el `<thead>`.
- `rowActionsLabel` (opcional): texto del header para la columna de acciones.

### Slots

- `toolbar`: recibe `{ table }`.
- `pagination`: recibe `{ table }`.
- `empty`: contenido cuando no hay datos.
- `row-actions`: recibe `{ row, original, table }` para acciones por fila.

### Exposed (defineExpose)

- `table`: instancia de TanStack.
- `selectedCount`: cantidad de filas seleccionadas.

### Notas de comportamiento

- Al cambiar `data`, se reinicia la selección y vuelve a la página 1.
- Al cambiar el filtro global, vuelve a la página 1.
- El filtro global usa `includesString`.

## TableFilters.vue

- Renderiza inputs para columnas con `enableColumnFilter: true`.
- Usa `columnDef.meta.filterPlaceholder` si existe.
- Limpia todos los filtros con `resetColumnFilters()`.

## TablePagination.vue

- Controles: primera/anterior/siguiente/última.
- Selector de tamaño de página.
- Muestra rango y total filtrado vs total real.

## Recomendaciones

- Para filtros avanzados (select, rango, fecha), crea un `TableFiltersAdvanced.vue` y usa `column.setFilterValue()`.
- Para acciones por fila, usa el slot `row-actions` y dispara modales o eventos.
- Mantén `getRowId` si los datos vienen de backend para conservar selección.
