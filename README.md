# Gestión de Usuarios y Estadísticas

Aplicación Laravel 11 con frontend Vue 3 (Inertia.js) que expone un panel de APIs para administrar usuarios, sincronizar países desde una API pública y consultar estadísticas agregadas. Incluye jobs asíncronos, eventos, pruebas automáticas y un **panel de administración con dashboards de métricas**, gráficas interactivas y layout premium.

## Stack

| Capa | Tecnología |
|------|------------|
| Backend | Laravel 11, Sanctum, PHP 8.x |
| Frontend | Vue 3, TypeScript, Inertia.js |
| Estilos | Tailwind CSS 4 |
| Gráficas | ECharts |
| Build | Vite 7 |

## Requisitos y ejecución

```bash
# Backend
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve

# Frontend (en otra terminal)
npm install
npm run dev
```

Acceso típico: `http://localhost:8000` (login) → Dashboard y menú lateral para navegar.

---

## Arquitectura (backend)

- **Autenticación y Usuarios**
  - Rutas REST `api/v1/users` protegidas con Laravel Sanctum y políticas (`UserPolicy`).
  - Formularios validados mediante `FormRequest` personalizados (`StoreUserRequest`, `UpdateUserRequest`) que devuelven errores consistentes en JSON.
  - `UserResource` normaliza la salida JSON, incluyendo metadatos (roles, verificación, país, timestamps).

- **Consumo de API externa (Países)**
  - `CountryController` permite listar, sincronizar (`/countries/sync`) y generar estadísticas de países (`/countries/statistics`).
  - La sincronización consume https://restcountries.com usando `Http::retry`, persiste los datos con `updateOrCreate` y registra métricas básicas.
  - `CountryPolicy` restringe la sincronización y estadísticas a usuarios administradores.

- **Estadísticas y Procesos Asíncronos**
  - `StatisticsController` expone endpoints para métricas de usuarios, actividad y dashboard administrativo.
  - El `CalculateDailyStatisticsJob` construye snapshots en la tabla `daily_statistics`, despacha el evento `DailyStatisticsCalculated` y notifica de forma asíncrona a través del listener `NotifyAdminsStatisticsCalculated`.
  - Se incluye el comando `php artisan statistics:calculate` para ejecutar el job de inmediato o encolado, y `php artisan countries:sync` para integrar con la API pública.

## Migraciones Principales

| Archivo | Descripción |
| --- | --- |
| `0001_01_01_000000_create_users_table.php` | Esquema base de usuarios. |
| `2025_09_28_195647_add_role_to_users_table.php` | Roles (`admin`/`user`). |
| `2025_09_28_233116_create_countries_table.php` | Catálogo de países. |
| `2025_09_29_013111_create_daily_statistics_table.php` | Snapshot diario de métricas. |
| `2025_09_29_030000_add_country_id_to_users_table.php` | Relación opcional usuario-país. |

## Comandos Disponibles

```bash
# Calcula estadísticas diarias para ayer (inmediato)
php artisan statistics:calculate

# Calcula estadísticas para una fecha específica y encola el job
php artisan statistics:calculate --date=2025-09-28 --queue

# Sincroniza países desde RestCountries (con reintentos y barra de progreso)
php artisan countries:sync --force --limit=100
```

## Endpoints REST Destacados

| Método | Ruta | Descripción |
| --- | --- | --- |
| POST | `/api/v1/auth/register` | Registro público de usuarios. |
| POST | `/api/v1/auth/login` | Inicio de sesión y emisión de token. |
| GET | `/api/v1/auth/me` | Datos del usuario autenticado. |
| GET | `/api/v1/users` | Listado paginado de usuarios (solo admins). |
| POST | `/api/v1/countries/sync` | Sincroniza países externos (admins). |
| GET | `/api/v1/statistics/dashboard` | Resumen ejecutivo (admins). |
| GET | `/api/v1/statistics/historical/{days?}` | Serie histórica basada en snapshots. |

Todas las respuestas siguen un formato JSON uniforme gracias al middleware `ForceJsonResponse` y el handler `ApiErrorController`.

## Pruebas Automatizadas

Se añadieron pruebas de PHPUnit para los módulos clave:

- `tests/Feature/UserRegistrationTest.php`: cubre registro público, reglas de validación, emisión de tokens y restricciones de rol.
- `tests/Feature/DailyStatisticsJobTest.php`: valida la persistencia del snapshot diario y el despacho del evento cuando se ejecuta el job.

Ejecuta la suite con:

```bash
php artisan test
```

> Las pruebas utilizan `RefreshDatabase`, por lo que aplican todas las migraciones antes de cada escenario.

## Consideraciones de Seguridad

- Todas las rutas `api/v1/*` (excepto autenticación) están protegidas con `auth:sanctum` y políticas.
- Validaciones estrictas para entradas de usuario (regex de nombre, fuerza de contraseña, `email:rfc,dns`).
- Manejo centralizado de errores JSON con trazas limitadas en producción.

---

## Panel de administración y dashboards (frontend)

El frontend usa **Inertia.js** con **Vue 3** y **TypeScript**. Las páginas de administración comparten un layout unificado con sidebar, navbar y área de contenido.

### Layout y navegación

- **AdminLayout** (`Layouts/AdminLayout.vue`): layout principal con sidebar contraíble/ocultable y zona de contenido que se adapta al estado de la barra.
- **Sidebar** (`Components/Sidebar.vue`):
  - Tres estados: expandida, contraída (solo iconos) y oculta.
  - Al pasar el ratón sobre la barra contraída, se expande temporalmente para navegar sin clic.
  - Al elegir un ítem del menú se puede contraer automáticamente.
  - Transiciones suaves (300ms) y secciones: Principal, Datos, Análisis, Sistema.
- **Navbar** (`Components/Navbar.vue`): tres zonas — menú + logo “Analytics”, barra de búsqueda centrada, notificaciones y menú de usuario.

### Dashboards de métricas

Rutas bajo `/dashboards` (menú **Dashboards de métricas**):

| Ruta | Página | Contenido |
|------|--------|-----------|
| `/dashboards` | Index | Enlaces a las subvistas |
| `/dashboards/vista-general` | VistaGeneral | KPI principal (hero), MetricCards con sparklines, drill-down al clic |
| `/dashboards/usuarios-activos-inactivos` | UsuariosActivosInactivos | KPIs, PieChart, HorizontalBarChart por estatus |
| `/dashboards/dias-suspendidos` | DiasSuspendidos | Semáforo de riesgo (SemaphoreBarChart) por rango de días |

### Componentes de dashboards

- **DashboardHeader**: título, subtítulo, icono y slot para acciones (p. ej. selector de fechas).
- **MetricCard**: tarjeta KPI con valor, tendencia (sube/baja/neutral), porcentaje, sparkline opcional y texto de comparación; clic abre detalle.
- **Sparkline**: minigráfico de tendencia (línea) para métricas.
- **DetailDrawer**: panel lateral que se abre al clic en una MetricCard para mostrar detalle (gráfica placeholder + tabla).
- **ExpandableChart**: wrapper para cualquier gráfica; añade botón “Expandir” que abre la gráfica en overlay a pantalla casi completa para verla con más claridad. Al expandir, el mismo componente ECharts se redimensiona vía `ResizeObserver` en `BaseEChart`.

### Gráficas (ECharts)

- **BaseEChart**: contenedor ECharts con `ResizeObserver` para redimensionar al cambiar el tamaño del contenedor; usado por el resto de gráficas.
- **PieChart** / **HorizontalBarChart** / **SemaphoreBarChart**: componentes específicos para los dashboards.
- Las gráficas en “Usuarios activos/inactivos” y “Días suspendidos” están envueltas en **ExpandableChart** para poder abrirlas en grande.

### Presentación de datos (data storytelling)

- Insights en una línea debajo de los headers (resúmenes breves).
- F-pattern: KPIs arriba, gráficas en el medio, controles y filtros en la parte superior.
- Leyendas de riesgo y semáforos donde aplica (p. ej. días suspendidos).
- Animaciones suaves con `@formkit/auto-animate` en listas y grids de tarjetas.

### Estilos y tema

- Variables CSS (`@theme`) para colores de superficie y sidebar (paleta coherente).

### Otras páginas del frontend

- **Tablas**: `/tables` con TanStack Table, filtros y paginación.
- **Gráficas**: `/charts` con ejemplos de ECharts.
- **Usuarios**, **Countries**, **Events**, **Statistics**, **Settings**, **Diagram**, **Organization chart**: módulos existentes que pueden usar el mismo `AdminLayout` y sidebar.

---

## Próximos Pasos Recomendados

1. Conectar los dashboards a datos reales (endpoints de estadísticas e históricos).
2. Incorporar notificaciones reales (mail/Slack) en el listener de estadísticas.
3. Extender las pruebas a endpoints de países y estadísticas para cobertura adicional.
4. Sustituir el placeholder del DetailDrawer por gráficas y tablas reales por tipo de métrica.
