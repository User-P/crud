# Migración de dashboards a otro proyecto

Guía para llevar los **dashboards de métricas** (Index, Vista general, Usuarios activos/inactivos, Días suspendidos, Usuarios nuevos, Vista consolidada) a otro proyecto **Laravel + Vue 3 + PrimeVue** y que se vean igual que en este repositorio.

---

## Requisitos del proyecto destino

- **Laravel** (con rutas web y/o API que devuelvan Inertia)
- **Vue 3** + **TypeScript**
- **Inertia.js** (`@inertiajs/vue3`)
- **PrimeVue 4** (`primevue`, `@primeuix/themes`, `primeicons`)
- **Tailwind CSS 4** (con `@tailwindcss/vite` o equivalente)
- **Vite** (con `laravel-vite-plugin` y `@vitejs/plugin-vue`)
- **ECharts** (`echarts`)
- **Iconify** (`@iconify/vue`)
- **Pinia** (opcional; si el proyecto ya usa otro store, los dashboards no lo requieren)

---

## 1. Archivos a copiar

### 1.1 Estilos globales (obligatorio)

| Origen | Destino | Descripción |
|--------|---------|-------------|
| `resources/css/app.css` | `resources/css/app.css` (o integrar en el tuyo) | Variables `--th-*` y `--p-*`, tema light/dark, estilos `.dashboard-tile`, `.billboard-hero`, PrimeVue overrides, scrollbar, etc. |

**Importante:** Si tu proyecto ya tiene su propio `app.css`, debes **fusionar** (no reemplazar) al menos:

- La sección **TEMA** (`:root` y `[data-theme="dark"]`) con todas las variables `--th-*` y `--p-primary-*`.
- La sección **DASHBOARD** (`.dashboard-tile`, `.billboard-hero__bg`, `.billboard-hero__blob`).
- La sección **DRAWER** si usas `DetailDrawer` (`.drawer-overlay`, `.drawer-panel`, `.drawer-header`).
- Los overrides de **PrimeVue** (DataTable, Dialog, InputText, Button, etc.) para que tablas y formularios sigan el mismo aspecto.

También necesitas en tu CSS global:

- `@layer tailwind-base, primevue, tailwind-utilities;`
- `@custom-variant dark (&:where([data-theme="dark"], [data-theme="dark"] *));` (si usas Tailwind 4 con variante dark por `data-theme`).

---

### 1.2 Tema PrimeVue (obligatorio para mismo look)

| Origen | Destino |
|--------|---------|
| `resources/js/primevue-preset.ts` | `resources/js/primevue-preset.ts` (o `presets/cosmos.ts`) |

En `app.ts` (o equivalente) debes usar este preset al configurar PrimeVue:

```ts
import PrimeVue from 'primevue/config'
import CosmosPreset from './primevue-preset'  // o la ruta donde lo copies

createApp(App)
  .use(PrimeVue, {
    theme: {
      preset: CosmosPreset,
      options: {
        cssLayer: { name: 'primevue', order: 'tailwind-base, primevue, tailwind-utilities' },
        darkModeSelector: '[data-theme="dark"]',
      },
    },
  })
  .mount('#app')
```

---

### 1.3 Tema ECharts (obligatorio para gráficas)

| Origen | Destino |
|--------|---------|
| `resources/js/echarts/cosmosThemes.ts` | `resources/js/echarts/cosmosThemes.ts` |

En el entrypoint (p. ej. `app.ts`) registrar los temas **antes** de montar la app:

```ts
import * as echarts from 'echarts/core'
import { cosmosLight, cosmosDark } from './echarts/cosmosThemes'

echarts.registerTheme('cosmos-light', cosmosLight)
echarts.registerTheme('cosmos-dark', cosmosDark)
```

Los componentes de gráficas (`BaseEChart`, `PieChart`, etc.) usan `cosmos-light` / `cosmos-dark` según `document.documentElement.getAttribute('data-theme')`, por lo que el tema claro/oscuro debe estar controlado por `data-theme` en el `<html>` (igual que en este proyecto).

---

### 1.4 Tema claro/oscuro en el HTML (recomendado)

| Origen | Destino |
|--------|---------|
| Lógica en `resources/js/app.ts` (`initTheme()`) | Mismo entrypoint |
| `resources/js/composables/useTheme.ts` | `resources/js/composables/useTheme.ts` |

En `app.ts` se ejecuta algo como:

```ts
function initTheme() {
  const saved = localStorage.getItem('admin-theme') || 'system'
  const resolved = saved === 'system'
    ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
    : saved
  document.documentElement.setAttribute('data-theme', resolved)
}
initTheme()
```

Así el primer pintado ya usa light/dark y las gráficas y PrimeVue coinciden con el tema.

---

### 1.5 Layout y páginas de dashboards

| Origen | Destino |
|--------|---------|
| `resources/js/Layouts/AdminLayout.vue` | `resources/js/Layouts/AdminLayout.vue` |
| `resources/js/Pages/Dashboards/Index.vue` | `resources/js/Pages/Dashboards/Index.vue` |
| `resources/js/Pages/Dashboards/VistaGeneral.vue` | `resources/js/Pages/Dashboards/VistaGeneral.vue` |
| `resources/js/Pages/Dashboards/UsuariosActivosInactivos.vue` | `resources/js/Pages/Dashboards/UsuariosActivosInactivos.vue` |
| `resources/js/Pages/Dashboards/DiasSuspendidos.vue` | `resources/js/Pages/Dashboards/DiasSuspendidos.vue` |
| `resources/js/Pages/Dashboards/UsuariosNuevos.vue` | `resources/js/Pages/Dashboards/UsuariosNuevos.vue` |
| `resources/js/Pages/Dashboards/VistaConsolidada.vue` | `resources/js/Pages/Dashboards/VistaConsolidada.vue` |
| `resources/js/Pages/Dashboards/components/DashboardBentoGrid.vue` | `resources/js/Pages/Dashboards/components/DashboardBentoGrid.vue` |
| `resources/js/Pages/Dashboards/composables/useUsers.ts` | `resources/js/Pages/Dashboards/composables/useUsers.ts` |

`AdminLayout` depende de `Sidebar`, `Navbar` y `useTheme`. Si en el otro proyecto ya tienes un layout distinto, puedes crear una variante que solo envuelva el contenido de dashboards con las mismas clases (p. ej. `cosmos-app`) y breadcrumbs/título para que los estilos `--th-*` y `.dashboard-tile` sigan aplicándose.

---

### 1.6 Componentes compartidos (dashboards y gráficas)

Copiar toda la carpeta o los archivos listados:

| Origen | Uso |
|--------|-----|
| `resources/js/Components/Dashboards/MetricCard.vue` | KPIs en Vista general, Activos/inactivos, Usuarios nuevos, Vista consolidada |
| `resources/js/Components/Dashboards/ExpandableChart.vue` | Wrapper de gráficas con botón “Expandir” |
| `resources/js/Components/Dashboards/DashboardHeader.vue` | Cabecera con título, subtítulo, slot para acciones (p. ej. fecha) |
| `resources/js/Components/Dashboards/Sparkline.vue` | Minigráfico en MetricCard |
| `resources/js/Components/Dashboards/DetailMetricTable.vue` | Tabla de detalle con búsqueda, ordenación y exportación CSV (y ZIP por partes si hay muchas filas) |
| `resources/js/Components/Dashboards/DetailDrawer.vue` | Panel lateral de detalle (opcional si no usas drill-down) |
| `resources/js/Components/Charts/BaseEChart.vue` | Base ECharts con tema cosmos-light/dark y ResizeObserver |
| `resources/js/Components/Charts/PieChart.vue` | Gráfico circular |
| `resources/js/Components/Charts/HorizontalBarChart.vue` | Barras horizontales |
| `resources/js/Components/Charts/SemaphoreBarChart.vue` | Barras tipo semáforo (Días suspendidos) |

Además, para **tendencias de UX y carga** (skeleton loaders, empty states, micro-interacciones):

| Origen | Uso |
|--------|-----|
| `resources/js/Components/AppSkeleton.vue` | Placeholder de carga (variantes: card, row, text) en lugar de solo spinner |
| `resources/js/Components/EmptyState.vue` | Estado vacío reutilizable (icono, título, descripción, CTA opcional) |

Si en el proyecto destino ya tienes componentes de gráficas propios, puedes mantenerlos pero tendrás que adaptar las páginas para usar tus componentes o reemplazar las referencias para que usen `BaseEChart` + temas `cosmos-light` / `cosmos-dark` si quieres el mismo aspecto.

---

### 1.7 Loading global (pantalla completa)

Sistema de loading reutilizable que se muestra sobre toda la pantalla durante peticiones o acciones asíncronas (p. ej. al hacer clic en una MetricCard mientras se “pinta” el detalle).

| Origen | Destino | Descripción |
|--------|---------|-------------|
| `resources/js/Components/AppLoading.vue` | `resources/js/Components/AppLoading.vue` | Spinner (PrimeVue ProgressSpinner) con mensaje opcional; modos **overlay** (cubre contenedor o pantalla) e **inline**. Prop `fullScreen` para overlay a pantalla completa. |
| `resources/js/Components/GlobalLoadingLayer.vue` | `resources/js/Components/GlobalLoadingLayer.vue` | Capa que lee el estado de `useGlobalLoading()` y muestra `AppLoading` en modo full-screen cuando está activo. |
| `resources/js/composables/useGlobalLoading.ts` | `resources/js/composables/useGlobalLoading.ts` | Composable con estado global: `isLoading`, `message`, `show(msg?)`, `hide()`. Cualquier componente puede llamar `useGlobalLoading().show('Cargando…')` y `hide()` al terminar. |

**Integración en el entrypoint:** en `app.ts` (o donde montes la app con `createInertiaApp`), el árbol de la app debe incluir la capa de loading global para que el overlay se vea por encima de todo. Ejemplo:

```ts
import { createApp, h, Fragment } from 'vue'
import GlobalLoadingLayer from './Components/GlobalLoadingLayer.vue'

createInertiaApp({
  setup({ el, App, props, plugin }) {
    createApp({
      render() {
        return h(Fragment, null, [
          h(App, props),
          h(GlobalLoadingLayer),
        ])
      },
    })
      .use(plugin)
      // ... resto (Pinia, PrimeVue, etc.)
      .mount(el)
  },
})
```

**Uso en cualquier página o componente:**

```ts
import { useGlobalLoading } from '@/composables/useGlobalLoading'

const { show, hide } = useGlobalLoading()

// Al iniciar una petición
show('Guardando…')   // o show() para "Cargando…"
await api.post('/algo', data)
hide()
```

En las vistas de dashboards (VistaGeneral, VistaConsolidada) el clic en una MetricCard llama a `showGlobalLoading('Cargando detalle…')` y tras un breve delay a `hideGlobalLoading()`, de modo que el usuario ve el loading a pantalla completa mientras se actualiza la tabla de detalle.

**Dependencia:** `AppLoading.vue` usa `ProgressSpinner` de PrimeVue; no requiere dependencias adicionales si ya usas PrimeVue.

---

### 1.8 Dependencias del layout (si usas AdminLayout tal cual)

Para que `AdminLayout.vue` funcione sin cambios necesitas:

| Origen | Destino |
|--------|---------|
| `resources/js/Components/Sidebar.vue` | `resources/js/Components/Sidebar.vue` |
| `resources/js/Components/Navbar.vue` | `resources/js/Components/Navbar.vue` |

Y en el layout: `PrimeSidebar` de PrimeVue (ya lo tienes si usas PrimeVue). Las clases del sidebar/navbar dependen de las variables `--th-sidebar-*`, `--th-nav-*`, `--th-item-*`, etc. definidas en `app.css`.

---

### 1.9 DetailMetricTable: exportación CSV y ZIP por partes

`DetailMetricTable.vue` admite exportación a CSV. Si hay muchas filas, puedes limitar el tamaño por archivo con la prop **`maxRowsPerCsvFile`** (p. ej. `500000`): la exportación se divide en varios CSV y se entrega un único **ZIP** con `parte-1-de-N.csv`, …, y un `LEEME.txt` con instrucciones. Dependencia: **JSZip** (`npm i jszip`). Si no usas exportación por partes, no hace falta instalar JSZip.

---

### 1.10 Opcional: selector de fechas

Algunas vistas usan un selector de fecha en el header:

| Origen | Destino |
|--------|---------|
| `resources/js/Components/Tables/Pickers/CustomPicker.vue` | Mismo path o ajustar import en las páginas |

Si no lo copias, quita o sustituye el uso de `CustomPicker` en `VistaGeneral`, `DiasSuspendidos`, `UsuariosNuevos`, etc.

---

### 1.11 Tendencias de diseño (glass, bento, grain, stagger, lift)

El sistema aplica varias tendencias actuales de UI:

- **Glassmorphism**: Sidebar, navbar, tiles y cards usan `backdrop-blur` y fondos semitransparentes (variables `--th-*` en `app.css`).
- **Bento grid**: `DashboardBentoGrid.vue` con una card destacada (row-span) y el resto compactas.
- **Textura grain**: Overlay sutil de ruido (`.cosmos-app::after`) para dar profundidad; se desactiva con `prefers-reduced-motion`.
- **Stagger reveal**: Clases `.cosmos-reveal` y `.cosmos-reveal--stagger-1` … `--stagger-8` para animación de entrada escalonada (keyframes `cosmos-fade-in-up`).
- **Hover lift**: Clases `.cosmos-lift` y `.dashboard-tile` con `translateY(-4px)` y sombra en hover; respetan `prefers-reduced-motion: reduce`.
- **Barra de progreso en navegación**: En `app.ts` se puede configurar `progress: { delay: 200, color: '#0b4261', includeCSS: true, showSpinner: false }` en `createInertiaApp` para mostrar una barra al cambiar de página (depende de la implementación por defecto de Inertia; si se desactiva con `progress: false`, se puede usar NProgress y eventos del router).

---

## 2. Rutas (Laravel)

En `routes/web.php` (o donde definas rutas Inertia) añade rutas equivalentes. Ejemplo:

```php
Route::get('/dashboards', fn () => Inertia::render('Dashboards/Index'))->name('dashboards.index');
Route::get('/dashboards/vista-general', fn () => Inertia::render('Dashboards/VistaGeneral'))->name('dashboards.vista-general');
Route::get('/dashboards/usuarios-activos-inactivos', fn () => Inertia::render('Dashboards/UsuariosActivosInactivos'))->name('dashboards.usuarios-activos-inactivos');
Route::get('/dashboards/dias-suspendidos', fn () => Inertia::render('Dashboards/DiasSuspendidos'))->name('dashboards.dias-suspendidos');
Route::get('/dashboards/usuarios-nuevos', fn () => Inertia::render('Dashboards/UsuariosNuevos'))->name('dashboards.usuarios-nuevos');
Route::get('/dashboards/vista-consolidada', fn () => Inertia::render('Dashboards/VistaConsolidada'))->name('dashboards.vista-consolidada');
```

Ajusta el prefijo y los nombres según tu aplicación.

---

## 3. Resolución de páginas (Vite / Inertia)

Este proyecto usa:

```ts
const pages = import.meta.glob<PageModule>('./Pages/**/*.vue', { eager: true })
return pages[`./Pages/${name}.vue`].default
```

Asegúrate de que las rutas Inertia resuelvan a `Pages/Dashboards/Index.vue`, `Pages/Dashboards/VistaGeneral.vue`, etc. Si en el otro proyecto la estructura es distinta (p. ej. `views/pages/dashboards/...`), ajusta las rutas de Inertia y los imports dentro de los `.vue` para que sigan pudiendo importar `@/Layouts/AdminLayout.vue`, `@/Components/...`, etc. (según tu alias `@`).

---

## 4. Alias y paths

Los componentes usan alias `@/` para:

- `@/Layouts/AdminLayout.vue`
- `@/Components/Dashboards/...`
- `@/Components/Charts/...`
- `@/composables/useTheme`
- `@/composables/useGlobalLoading`
- `@/composables/useUsers` (no existe en raíz; está en `Pages/Dashboards/composables/useUsers.ts`)

En este repo los imports desde las páginas de Dashboards son relativos a `Pages/Dashboards/` para el composable (`./composables/useUsers`) y `@/` para Layout y Components. En el proyecto destino, configura en `vite.config` (o `tsconfig`) el alias `@` apuntando a `resources/js` (o la raíz de tu frontend) para que `@/Components/...` y `@/Layouts/...` resuelvan bien.

---

## 5. Datos (API o dummy)

El composable `useUsers.ts` incluye datos dummy y funciones que “llaman” a APIs. Para migrar:

- Opción A: Dejar el composable como está y seguir usando datos dummy hasta conectar tu backend.
- Opción B: Sustituir en `useUsers.ts` las llamadas por `axios` o `router.get()` a tus endpoints (p. ej. `/api/statistics/...`) y mapear la respuesta al formato que esperan las páginas (primary/secondary cards, charts, etc.).

Las vistas esperan estructuras como `users.value.primary`, `users.value.secondary`, `charts.value`, `suspended.value`, `usersAdd.value`, `detailsByCard`, etc. Mantén esos nombres o adapta las páginas al nuevo formato.

---

## 6. Checklist rápido

- [ ] **CSS**: variables `--th-*` y `--p-*`, bloques dashboard, drawer y PrimeVue en `app.css` (o fusionados en tu CSS).
- [ ] **PrimeVue**: `primevue-preset.ts` registrado en la config de PrimeVue con `darkModeSelector: '[data-theme="dark"]'`.
- [ ] **ECharts**: `cosmosThemes.ts` registrado como `cosmos-light` y `cosmos-dark` en el entrypoint.
- [ ] **Tema**: `data-theme` en `<html>` inicializado al arrancar (p. ej. desde `app.ts` + `useTheme`).
- [ ] **Layout**: `AdminLayout.vue` (y Sidebar/Navbar si usas el mismo layout).
- [ ] **Páginas**: Todas las vistas bajo `Pages/Dashboards/` y el componente `DashboardBentoGrid` + composable `useUsers`.
- [ ] **Componentes**: MetricCard, ExpandableChart, DashboardHeader, Sparkline, DetailMetricTable; BaseEChart, PieChart, HorizontalBarChart, SemaphoreBarChart; DetailDrawer si lo usas.
- [ ] **Loading global**: AppLoading, GlobalLoadingLayer, useGlobalLoading; integración en app.ts (Fragment + GlobalLoadingLayer) si quieres loading a pantalla completa en peticiones / clic en cards.
- [ ] **Skeleton y empty state**: AppSkeleton (variant card/row/text) y EmptyState para cargas y tablas vacías.
- [ ] **Accesibilidad**: Si usas animaciones, mantener `prefers-reduced-motion` (ya aplicado en app.css).
- [ ] **Rutas**: Rutas web que hagan `Inertia::render('Dashboards/...')`.
- [ ] **Alias**: `@` apuntando a la raíz del frontend para Layouts y Components.
- [ ] **Datos**: Mantener dummy o conectar `useUsers` a tu API.

Con esto, los dashboards deberían verse igual que en este proyecto (mismo tema, tiles tipo bento, gráficas con paleta DSI y comportamiento expandible). Si tu proyecto ya tiene otro layout o tema, tendrás que reemplazar o adaptar solo la capa de layout y/o variables de color, manteniendo la estructura de componentes y de estilos aquí descrita.
