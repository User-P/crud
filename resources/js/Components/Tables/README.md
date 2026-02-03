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
    -   `TanStackTable.vue` (InputText para filtro global y edición inline, Select/InputNumber/DatePicker según meta).
    -   `TableFilters.vue` (InputText + Button).
    -   `TableFiltersAdvanced.vue` (InputText + Select + InputNumber + DatePicker + Button).
    -   `TablePagination.vue` (Button + Select).
    -   `TanStackTable.vue` (Checkbox para selección).
-   Asegúrate de tener PrimeVue y sus estilos cargados en el proyecto.

## Ejemplo completo (demo)

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
    score: number;
    department: "Ventas" | "Marketing" | "Soporte" | "Finanzas" | "TI";
    city: string;
    createdAt: string;
};

const DEPARTMENTS: Row["department"][] = ["Ventas", "Marketing", "Soporte", "Finanzas", "TI"];
const STATUS: Row["status"][] = ["Activo", "Inactivo"];

const makeRows = (count = 20): Row[] =>
    Array.from({ length: count }, () => ({
        id: faker.string.uuid(),
        name: faker.person.fullName(),
        email: faker.internet.email(),
        status: faker.helpers.arrayElement(STATUS),
        age: faker.number.int({ min: 18, max: 65 }),
        score: faker.number.int({ min: 0, max: 100 }),
        department: faker.helpers.arrayElement(DEPARTMENTS),
        city: faker.location.city(),
        createdAt: faker.date.past({ years: 1 }).toISOString().slice(0, 10),
    }));

const data = ref<Row[]>(makeRows(50));
const loading = ref(false);

const regenerate = async () => {
    loading.value = true;
    await new Promise((resolve) => setTimeout(resolve, 350));
    data.value = makeRows(50);
    loading.value = false;
};

const updateRow = (rowId: string, patch: Partial<Row>) => {
    data.value = data.value.map((row) => (row.id === rowId ? { ...row, ...patch } : row));
};

const onUpdateCell = (payload: { rowId: string; columnId: string; value: unknown; oldValue: unknown }) => {
    const { rowId, columnId, value, oldValue } = payload;
    if (Object.is(value, oldValue)) return;
    switch (columnId) {
        case "status":
            if (value === "Activo" || value === "Inactivo") updateRow(rowId, { status: value });
            return;
        case "department":
            if (typeof value === "string") updateRow(rowId, { department: value as Row["department"] });
            return;
        case "name":
            if (typeof value === "string") updateRow(rowId, { name: value });
            return;
        case "city":
            if (typeof value === "string") updateRow(rowId, { city: value });
            return;
        case "score":
            if (typeof value === "number") updateRow(rowId, { score: value });
            return;
        case "createdAt":
            if (typeof value === "string") updateRow(rowId, { createdAt: value });
            return;
    }
};

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
        meta: { filterPlaceholder: "Filtrar nombre", editable: true, editPlaceholder: "Editar nombre" },
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
            filterOptions: STATUS.map((s) => ({ label: s, value: s })),
            editable: true,
            editOptions: STATUS.map((s) => ({ label: s, value: s })),
        },
    },
    {
        accessorKey: "department",
        header: "Departamento",
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: "equalsString",
        meta: {
            filterType: "select",
            filterOptions: DEPARTMENTS.map((d) => ({ label: d, value: d })),
            editable: true,
            editOptions: DEPARTMENTS.map((d) => ({ label: d, value: d })),
        },
    },
    {
        accessorKey: "age",
        header: "Edad",
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: "inNumberRange",
        meta: { filterType: "numberRange", filterMinPlaceholder: "Min", filterMaxPlaceholder: "Max" },
    },
    {
        accessorKey: "score",
        header: "Score",
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: "inNumberRange",
        meta: {
            filterType: "numberRange",
            filterMinPlaceholder: "Min",
            filterMaxPlaceholder: "Max",
            editable: true,
            editType: "number",
            editPlaceholder: "0-100",
        },
    },
    {
        accessorKey: "city",
        header: "Ciudad",
        cell: (info) => info.getValue(),
        enableColumnFilter: true,
        filterFn: "includesString",
        meta: { filterPlaceholder: "Filtrar ciudad", editable: true, editPlaceholder: "Editar ciudad" },
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
            editable: true,
            editType: "date",
            editPlaceholder: "YYYY-MM-DD",
        },
    },
];
</script>

<template>
    <TanStackTable
        :data="data"
        :columns="columns"
        :loading="loading"
        enable-sorting
        enable-pagination
        enable-global-filter
        enable-column-filters
        selectable
        row-click="drawer"
        drawer-title="Detalle del registro"
        skeleton-loading
        :skeleton-rows="8"
        :page-size="10"
        show-sticky-header
        @update:cell="onUpdateCell"
    >
        <template #toolbar="{ table }">
            <div class="flex flex-col gap-2 w-full">
                <div class="flex items-center gap-2">
                    <Button type="button" class="p-button-sm p-button-secondary" label="Regenerar datos"
                        @click="regenerate" />
                </div>
                <TableFiltersAdvanced :table="table" />
            </div>
        </template>

        <template #pagination="{ table }">
            <TablePagination :table="table" :page-size-options="[5, 10, 25]" />
        </template>

        <template #expanded-row="{ original }">
            <div class="text-xs font-mono bg-gray-100 rounded p-3 overflow-auto max-h-48">
                <pre>{{ JSON.stringify(original, null, 2) }}</pre>
            </div>
        </template>

        <template #drawer="{ original, close }">
            <div class="space-y-3 text-sm">
                <div class="font-medium">{{ original.name }}</div>
                <div class="text-gray-600">{{ original.email }}</div>
                <div>Depto: {{ original.department }} · Score: {{ original.score }}</div>
                <Button type="button" class="p-button-sm" label="Cerrar" @click="close" />
            </div>
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
-   `rowClick` (opcional): comportamiento al hacer clic en una fila: `'none'` | `'expand'` | `'drawer'` | `'custom'`. Con `'expand'` se expande/contrae si existe slot `expanded-row`; con `'drawer'` se abre el drawer con el registro; con `'custom'` se emite `@row-click`.
-   `drawerTitle` (opcional): título del drawer cuando `rowClick="drawer"`.
-   `skeletonLoading` (opcional): si `true` y `loading` es `true`, muestra filas skeleton en lugar del mensaje "Cargando...".
-   `skeletonRows` (opcional): número de filas skeleton (por defecto `pageSize` o 10).

### Slots

-   `toolbar`: recibe `{ table }`.
-   `pagination`: recibe `{ table }`.
-   `empty`: contenido cuando no hay datos.
-   `row-actions`: recibe `{ row, original, table }` para acciones por fila.
-   **`expanded-row`**: recibe `{ row, original, table }`. Contenido que se muestra al expandir la fila (subtabla, timeline, notas, JSON raw, etc.). Si existe este slot, se muestra la columna de expandir/contraer.
-   **`drawer`**: recibe `{ row, original, close }`. Panel lateral (modo analista) para ver el registro sin salir de la tabla. Se abre con `rowClick="drawer"` o llamando a `openDrawer(row)` desde la ref.
-   **`cell`**: recibe `{ cell, row, value, isEditing, editingValue, startEdit, save, cancel, meta }`. Para celdas editables inline: usa `meta.editable` y opcionalmente `meta.editOptions` (select). Si no usas el slot, el componente muestra por defecto un input o select según `meta.editOptions`. Al guardar se emite `@update:cell` para validar y actualizar datos.

### Eventos

-   `@row-click`: cuando `rowClick="custom"` y el usuario hace clic en la fila. Payload: `{ row, original }`.
-   `@update:cell`: al guardar una celda editable inline. Payload: `{ rowId, columnId, value, oldValue, original }`. La página debe validar y actualizar `data` (y/o enviar al backend).

### Exposed (defineExpose)

-   `table`: instancia de TanStack.
-   `selectedCount`: cantidad de filas seleccionadas.
-   `totalRows`: total de filas antes de filtros.
-   `filteredTotal`: total de filas después de filtros.
-   `closeDrawer`: cierra el drawer.
-   `openDrawer(row)`: abre el drawer con la fila indicada.

### Notas de comportamiento

-   Al cambiar `data`, se reinicia la selección y vuelve a la página 1.
-   Al cambiar el filtro global, vuelve a la página 1.
-   Si los filtros reducen el total de páginas, se ajusta el `pageIndex` automáticamente.
-   El click en elementos interactivos (button, input, select, etc.) no dispara `rowClick`. Usa `data-stop-row-click` en wrappers si necesitas evitarlo manualmente.
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

### Meta soportada (TableFiltersAdvanced)

-   `filterType`: `'text' | 'select' | 'numberRange' | 'dateRange'`.
-   `filterPlaceholder`: placeholder para texto.
-   `filterOptions`: opciones `{ label, value }` para `select`.
-   `filterMinPlaceholder` / `filterMaxPlaceholder`: placeholders para rango numérico.
-   `filterFromPlaceholder` / `filterToPlaceholder`: placeholders para rango de fechas (se combinan como `Desde - Hasta` si no se define `filterPlaceholder`).

### Meta para celdas editables (TanStackTable)

En la definición de columnas, usa `meta` para activar edición inline:

-   `meta.editable: true`: la celda se puede editar al hacer clic.
-   `meta.editType: 'text' | 'number' | 'date' | 'select'`: tipo de editor inline (si no se definen `editOptions`).
-   `meta.editOptions`: array `{ label, value }[]` para mostrar un Select en lugar de InputText.
-   `meta.editPlaceholder`: placeholder del input cuando está vacío.

Escucha `@update:cell` para validar y actualizar la fuente de datos (y/o llamar al API).
Para `editType: 'date'`, el editor usa DatePicker y emite `YYYY-MM-DD` (local).
