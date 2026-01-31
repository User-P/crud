# Componentes de Tablas (TanStack)

Este folder contiene la tabla reutilizable basada en TanStack Table y componentes auxiliares (filtros/paginación).

## Archivos

-   `TanStackTable.vue`: componente principal (sorting, filtros, selección, paginación, sticky header, loading, acciones por fila).
-   `TablePagination.vue`: paginación simple con primera/última página y rango mostrado.
-   `TableFilters.vue`: filtros por columna (inputs por columna filtrable).
-   `TableFiltersAdvanced.vue`: filtros avanzados por columna (texto, select, rangos numéricos y fechas). El filtro `dateRange` usa `DatePicker` de PrimeVue en `selection-mode="range"` y guarda el valor como `{ from, to }` en formato **YYYY-MM-DD** (ver `dateFilterUtils.ts`) para evitar problemas de zona horaria.
-   `dateFilterUtils.ts`: utilidades para filtros de rango de fechas (parseo y formato YYYY-MM-DD).

## Dependencias UI

-   Se usan componentes PrimeVue en los helpers:
    -   `TanStackTable.vue` (InputText para filtro global).
    -   `TableFilters.vue` (InputText + Button).
    -   `TableFiltersAdvanced.vue` (InputText + Select + InputNumber + DatePicker + Button).
    -   `TablePagination.vue` (Button + Select).
    -   `TanStackTable.vue` (Checkbox para selección).
-   Asegúrate de tener PrimeVue y sus estilos cargados en el proyecto.

## Uso básico

```vue
<script setup lang="ts">
import { ref } from "vue";
import { faker } from "@faker-js/faker";
import { type ColumnDef } from "@tanstack/vue-table";
import TanStackTable from "@/Components/Tables/TanStackTable.vue";
import TablePagination from "@/Components/Tables/TablePagination.vue";
import TableFiltersAdvanced from "@/Components/Tables/TableFiltersAdvanced.vue";
import Button from "primevue/button";

type Row = {
    id: string;
    name: string;
    email: string;
    status: "Activo" | "Inactivo";
    age: number;
    createdAt: string;
};

const data = ref<Row[]>(
    Array.from({ length: 20 }, () => ({
        id: faker.string.uuid(),
        name: faker.person.fullName(),
        email: faker.internet.email(),
        status: faker.helpers.arrayElement(["Activo", "Inactivo"]),
        age: faker.number.int({ min: 18, max: 65 }),
        createdAt: faker.date.past({ years: 1 }).toISOString().slice(0, 10),
    }))
);

const columns: ColumnDef<Row>[] = [
    {
        accessorKey: "id",
        header: "ID",
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: "includesString",
        meta: { filterPlaceholder: "Filtrar ID" },
    },
    {
        accessorKey: "name",
        header: "Nombre",
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: "includesString",
        meta: { filterPlaceholder: "Filtrar nombre" },
    },
    {
        accessorKey: "email",
        header: "Email",
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: "includesString",
        meta: { filterPlaceholder: "Filtrar email" },
    },
    {
        accessorKey: "status",
        header: "Estado",
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: "equalsString",
        meta: {
            filterType: "select",
            filterOptions: [
                { label: "Activo", value: "Activo" },
                { label: "Inactivo", value: "Inactivo" },
            ],
        },
    },
    {
        accessorKey: "age",
        header: "Edad",
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: "numberRange",
        meta: {
            filterType: "numberRange",
            filterMinPlaceholder: "Min",
            filterMaxPlaceholder: "Max",
        },
    },
    {
        accessorKey: "createdAt",
        header: "Alta",
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: "dateRange",
        meta: {
            filterType: "dateRange",
            filterFromPlaceholder: "Desde",
            filterToPlaceholder: "Hasta",
        },
    },
];
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
                <TableFiltersAdvanced :table="table" />
            </div>
        </template>

        <template #pagination="{ table }">
            <TablePagination :table="table" :page-size-options="[5, 10, 25]" />
        </template>

        <template #row-actions="{ original }">
            <Button type="button" class="p-button-sm" label="Editar" />
        </template>
    </TanStackTable>
</template>
```

## TanStackTable.vue

### Props

-   `data` (**required**): arreglo de datos.
-   `columns` (**required**): definición de columnas `ColumnDef<T>`.
-   `getRowId` (opcional): función para generar ID de fila estable.
-   `enableSorting` (opcional): activa ordenamiento.
-   `enablePagination` (opcional): activa paginación.
-   `pageSize` (opcional): tamaño de página inicial.
-   `emptyText` (opcional): texto cuando no hay datos.
-   `loading` (opcional): muestra estado de carga y bloquea selección.
-   `selectable` (opcional): activa selección de filas.
-   `enableGlobalFilter` (opcional): activa filtro global (input interno).
-   `enableColumnFilters` (opcional): activa filtros por columna (para `TableFilters`).
-   `showStickyHeader` (opcional): mantiene el encabezado fijo al hacer scroll vertical. Solo tiene efecto si hay scroll (véase `scrollMaxHeight`).
-   `scrollMaxHeight` (opcional): altura máxima del área de la tabla (ej: `'70vh'`, `'500px'`). Por defecto `'70vh'` para permitir scroll vertical y horizontal; si se pasa `''`, la tabla crece sin límite y no hay scroll.
-   `rowActionsLabel` (opcional): texto del header para la columna de acciones.

### Slots

-   `toolbar`: recibe `{ table }`.
-   `pagination`: recibe `{ table }`.
-   `empty`: contenido cuando no hay datos.
-   `row-actions`: recibe `{ row, original, table }` para acciones por fila.

### Exposed (defineExpose)

-   `table`: instancia de TanStack.
-   `selectedCount`: cantidad de filas seleccionadas.
-   `totalRows`: total de filas antes de filtros.
-   `filteredTotal`: total de filas después de filtros.

### Notas de comportamiento

-   Al cambiar `data`, se reinicia la selección y vuelve a la página 1.
-   Al cambiar el filtro global, vuelve a la página 1.
-   Si los filtros reducen el total de páginas, se ajusta el `pageIndex` automáticamente.
-   El filtro global usa `includesString`.
-   El input del filtro global usa PrimeVue `InputText`.
-   `filterFn` soportadas por defecto: `includesString`, `equalsString`, `numberRange` / `inNumberRange`, `dateRange`. El filtro de fechas espera valores `{ from?: string, to?: string }` en formato YYYY-MM-DD.

## TableFilters.vue

-   Renderiza inputs (PrimeVue `InputText`) para columnas con `enableColumnFilter: true`.
-   Usa `columnDef.meta.filterPlaceholder` si existe.
-   Limpia todos los filtros con `resetColumnFilters()`.
-   El botón de limpiar se desactiva si no hay filtros activos.
-   Si el input queda vacío, el filtro se elimina (`undefined`).

## TablePagination.vue

-   Controles (PrimeVue `Button`): primera/anterior/siguiente/última.
-   Selector de tamaño de página con PrimeVue `Select`.
-   Muestra rango y total filtrado vs total real.

## Recomendaciones

-   Para filtros avanzados (select, rango, fecha), crea un `TableFiltersAdvanced.vue` y usa `column.setFilterValue()`.
-   Para acciones por fila, usa el slot `row-actions` y dispara modales o eventos.
-   Mantén `getRowId` si los datos vienen de backend para conservar selección.

## TableFiltersAdvanced.vue

Permite definir el tipo de filtro por columna usando `meta.filterType`:

-   `text` (default): input de texto.
-   `select`: select con opciones en `meta.filterOptions`.
-   `numberRange`: rango numérico `{ min, max }` con `filterFn: 'numberRange'`.
-   `dateRange`: rango de fechas `{ from, to }` con `filterFn: 'dateRange'`.

### Meta soportada

-   `filterType`: `'text' | 'select' | 'numberRange' | 'dateRange'`.
-   `filterPlaceholder`: placeholder para texto.
-   `filterOptions`: opciones `{ label, value }` para `select`.
-   `filterMinPlaceholder` / `filterMaxPlaceholder`: placeholders para rango numérico.
-   `filterFromPlaceholder` / `filterToPlaceholder`: placeholders para rango de fechas (se combinan como `Desde - Hasta` si no se define `filterPlaceholder`).
