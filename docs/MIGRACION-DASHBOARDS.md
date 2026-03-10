# Migración de dashboards a otro proyecto

Guía paso a paso para llevar los **dashboards de métricas** a un proyecto Laravel + Vue 3 + Inertia.js + PrimeVue que ya esté funcionando.

> **Stack real de este repositorio**
> Laravel · Vue 3 · TypeScript · Inertia.js v2 · PrimeVue v4 · Tailwind CSS **v3.4** · ECharts v6 · Iconify · Pinia

---

## Paso 1 — Instalar dependencias en el proyecto destino

```bash
npm install \
  primevue@^4.4.1 \
  @primeuix/themes@^1.2.5 \
  primeicons@^7.0.0 \
  echarts@^6.0.0 \
  @iconify/vue@^5.0.0 \
  pinia@^3.0.4 \
  @vueuse/core@^14.2.1 \
  @tanstack/vue-table@^8.21.3 \
  @formkit/auto-animate@^0.9.0 \
  vue-draggable-plus@^0.6.1 \
  jszip
```

---

## Paso 2 — Copiar archivos CSS

| Origen | Destino |
|--------|---------|
| `resources/css/dashboards-glass.css` | `resources/css/dashboards-glass.css` |
| `resources/css/app.css` | Fusionar en tu `app.css` (ver nota abajo) |

**Si tu proyecto ya tiene `app.css`**, fusiona las siguientes secciones del `app.css` de este repo:

- Bloque `:root { ... }` — variables `--th-*`, `--p-*` y `--glass-*`
- Bloque `[data-theme="dark"] { ... }` — las mismas variables en dark
- Sección "TIPOGRAFÍA" — reglas `.dashboard-app p`, `.dashboard-app h1`, etc.
- Sección "LAYOUT" — `.dashboard-app .sidebar`, `.dashboard-app .nav`
- Sección "UI" — `.gradient-text`, `.group-label`, `.crumb-*`, `.subtitle`, etc.
- Sección "DRAWER" — `.drawer-overlay`, `.drawer-panel`, `.drawer-header`
- Sección "PRIMEVUE" — todos los overrides `.p-menu`, `.p-button`, `.p-inputtext`, `.p-datatable`, `.p-dialog`, `.p-toast`, `.p-tabs`, etc.
- Sección "SCROLLBAR"

**Orden obligatorio de imports en `app.css`** (el `@import` debe preceder a `@tailwind`):

```css
@import "./dashboards-glass.css";

@tailwind base;
@tailwind components;
@tailwind utilities;

/* ... resto de tus variables y reglas ... */
```

---

## Paso 3 — Configurar Tailwind CSS (v3)

En `tailwind.config.ts` (o `.js`) del proyecto destino asegúrate de tener:

```ts
export default {
    content: [
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.{vue,js,ts}",
    ],
    darkMode: ["selector", '[data-theme="dark"]'],
    theme: {
        extend: {},
    },
    plugins: [],
}
```

El modo dark por `selector` con `[data-theme="dark"]` es el que usan las variables CSS y el preset de PrimeVue.

---

## Paso 4 — Copiar archivos JS/TS

### 4.1 Preset de PrimeVue y temas de ECharts

| Origen | Destino |
|--------|---------|
| `resources/js/primevue-preset.ts` | `resources/js/primevue-preset.ts` |
| `resources/js/echarts/cosmosThemes.ts` | `resources/js/echarts/cosmosThemes.ts` |

### 4.2 Composables compartidos

| Origen | Destino |
|--------|---------|
| `resources/js/composables/useTheme.ts` | `resources/js/composables/useTheme.ts` |
| `resources/js/composables/useGlobalLoading.ts` | `resources/js/composables/useGlobalLoading.ts` |

### 4.3 Componentes de UI compartidos

| Origen | Destino |
|--------|---------|
| `resources/js/Components/AppSkeleton.vue` | `resources/js/Components/AppSkeleton.vue` |
| `resources/js/Components/AppLoading.vue` | `resources/js/Components/AppLoading.vue` |
| `resources/js/Components/GlobalLoadingLayer.vue` | `resources/js/Components/GlobalLoadingLayer.vue` |

### 4.4 Componentes de gráficas

Copiar carpeta completa:

```
resources/js/Components/Charts/
├── BaseEChart.vue
├── BarChart.vue
├── LineChart.vue
├── PieChart.vue
├── HorizontalBarChart.vue
├── SemaphoreBarChart.vue
├── AdvancedChart.vue
├── GaugeChart.vue
└── RadarChart.vue
```

### 4.5 Componentes de dashboard

Copiar carpeta completa:

```
resources/js/Components/Dashboards/
├── DashboardHeader.vue
├── MetricCard.vue
├── Sparkline.vue
├── MiniChart.vue
├── ExpandableChart.vue
├── DetailMetricTable.vue
└── DetailDrawer.vue
```

### 4.6 Selector de fechas (opcional)

| Origen | Destino |
|--------|---------|
| `resources/js/Components/Tables/Pickers/CustomPicker.vue` | Mismo path |

Si no lo copias, quita el uso de `CustomPicker` en `VistaGeneral`, `DiasSuspendidos` y `UsuariosNuevos`.

### 4.7 Layout

| Origen | Destino |
|--------|---------|
| `resources/js/Layouts/AdminLayout.vue` | `resources/js/Layouts/AdminLayout.vue` |
| `resources/js/Components/Sidebar.vue` | `resources/js/Components/Sidebar.vue` |
| `resources/js/Components/Navbar.vue` | `resources/js/Components/Navbar.vue` |

Si el proyecto destino ya tiene su propio layout, puedes omitir estos tres y reemplazar `AdminLayout` en las páginas por el tuyo propio, siempre que envuelva el contenido en un elemento con la clase `dashboard-app`.

### 4.8 Páginas de dashboards

Copiar carpeta completa:

```
resources/js/Pages/Dashboards/
├── Index.vue
├── VistaGeneral.vue
├── UsuariosActivosInactivos.vue
├── DiasSuspendidos.vue
├── UsuariosNuevos.vue
├── VistaConsolidada.vue
├── components/
│   └── DashboardBentoGrid.vue
└── composables/
    └── useUsers.ts
```

---

## Paso 5 — Configurar `app.ts`

Reemplaza o adapta tu `app.ts` para incluir lo siguiente (mantén lo que ya tengas):

```ts
import '../css/app.css'
import { createApp, h, Fragment } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import PrimeVue from 'primevue/config'
import CosmosPreset from './primevue-preset'
import 'primeicons/primeicons.css'
import { createPinia } from 'pinia'
import * as echarts from 'echarts/core'
import { cosmosLight, cosmosDark } from './echarts/cosmosThemes'
import GlobalLoadingLayer from './Components/GlobalLoadingLayer.vue'

// 1. Inicializar tema ANTES del primer render
function initTheme() {
    const saved = localStorage.getItem('admin-theme') || 'system'
    const resolved = saved === 'system'
        ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
        : saved
    document.documentElement.setAttribute('data-theme', resolved)
}
initTheme()

// 2. Registrar temas de ECharts
echarts.registerTheme('cosmos-light', cosmosLight)
echarts.registerTheme('cosmos-dark', cosmosDark)

// 3. Montar app con Inertia
createInertiaApp({
    progress: { delay: 200, color: '#0b4261', includeCSS: true, showSpinner: false },
    resolve: (name) => {
        const pages = import.meta.glob<{ default: unknown }>('./Pages/**/*.vue', { eager: true })
        return pages[`./Pages/${name}.vue`]
    },
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
            .use(createPinia())
            .use(PrimeVue, {
                theme: {
                    preset: CosmosPreset,
                    options: {
                        darkModeSelector: '[data-theme="dark"]',
                    },
                },
            })
            .mount(el)
    },
})
```

---

## Paso 6 — Configurar alias `@` en Vite

En `vite.config.ts` asegúrate de tener el alias que usan los imports de los componentes:

```ts
import path from 'path'

export default defineConfig({
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    // ... resto de tu config
})
```

Y en `tsconfig.json`:

```json
{
    "compilerOptions": {
        "paths": {
            "@/*": ["resources/js/*"]
        }
    }
}
```

---

## Paso 7 — Agregar rutas en Laravel

En `routes/web.php`:

```php
use Inertia\Inertia;

Route::get('/dashboards', fn () =>
    Inertia::render('Dashboards/Index')
)->name('dashboards.index');

Route::get('/dashboards/vista-general', fn (\Illuminate\Http\Request $r) =>
    Inertia::render('Dashboards/VistaGeneral', ['unit' => $r->query('unit')])
)->name('dashboards.vista-general');

Route::get('/dashboards/usuarios-activos-inactivos', fn () =>
    Inertia::render('Dashboards/UsuariosActivosInactivos')
)->name('dashboards.usuarios-activos-inactivos');

Route::get('/dashboards/dias-suspendidos', fn () =>
    Inertia::render('Dashboards/DiasSuspendidos')
)->name('dashboards.dias-suspendidos');

Route::get('/dashboards/usuarios-nuevos', fn (\Illuminate\Http\Request $r) =>
    Inertia::render('Dashboards/UsuariosNuevos', ['unit' => $r->query('unit')])
)->name('dashboards.usuarios-nuevos');

Route::get('/dashboards/vista-consolidada', fn () =>
    Inertia::render('Dashboards/VistaConsolidada')
)->name('dashboards.vista-consolidada');
```

---

## Paso 8 — Conectar datos (API o dummy)

El composable `useUsers.ts` funciona con datos dummy por defecto. Puedes dejarlo así para verificar que todo se ve correctamente antes de conectar tu backend.

Cuando quieras conectar tu API, sustituye en `useUsers.ts` las asignaciones directas de dummy por llamadas `axios.get(...)` o `router.get(...)`, manteniendo los mismos nombres de propiedades que esperan las páginas:

| Propiedad | Usada en |
|-----------|---------|
| `users.value.primary` / `.secondary` | `VistaGeneral`, `VistaConsolidada` |
| `charts.value` | `UsuariosActivosInactivos` |
| `suspended.value` | `DiasSuspendidos` |
| `usersAdd.value` | `UsuariosNuevos` |
| `detailsByCard` | `DetailMetricTable` (drill-down) |

---

## Paso 9 — Verificar

```bash
npm run dev
```

Navega a `/dashboards` y revisa:

- [ ] Las tarjetas bento se ven con el efecto glass (fondo translúcido + blur)
- [ ] El hover de las cards hace lift (`translateY(-4px)`) con sombra
- [ ] El toggle de tema (light/dark) cambia los colores correctamente
- [ ] Las gráficas en Vista general y demás usan la paleta azul/verde DSI
- [ ] No hay errores de consola por imports no resueltos

---

## Checklist final

- [ ] `dashboards-glass.css` copiado e importado **antes** de `@tailwind` en `app.css`
- [ ] Variables `--th-*`, `--p-*`, `--glass-*` presentes en `:root` y `[data-theme="dark"]`
- [ ] `tailwind.config` con `darkMode: ["selector", '[data-theme="dark"]']`
- [ ] `primevue-preset.ts` registrado en `app.ts` con `darkModeSelector: '[data-theme="dark"]'`
- [ ] `cosmosThemes.ts` registrado como `cosmos-light` y `cosmos-dark` antes de montar la app
- [ ] `initTheme()` ejecutado antes del primer render (pone `data-theme` en `<html>`)
- [ ] `GlobalLoadingLayer` montado junto a la app en el `Fragment` de Inertia
- [ ] Alias `@` → `resources/js` configurado en Vite y tsconfig
- [ ] Todas las carpetas de `Components/Charts/`, `Components/Dashboards/` y `Pages/Dashboards/` copiadas
- [ ] Rutas Laravel agregadas y apuntando a `Dashboards/NombrePágina`
- [ ] `npm run dev` sin errores de compilación ni imports rotos
