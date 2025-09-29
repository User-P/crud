# Gestión de Usuarios y Estadísticas

Aplicación Laravel 11 que expone un panel de APIs para administrar usuarios, sincronizar países desde una API pública y consultar estadísticas agregadas. Además incorpora jobs asíncronos, eventos y pruebas automáticas que cubren los módulos críticos de la prueba técnica.

## Arquitectura

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

## Próximos Pasos Recomendados

1. Añadir un front-end que consuma los endpoints expuestos.
2. Incorporar notificaciones reales (mail/Slack) en el listener de estadísticas.
3. Extender las pruebas a endpoints de países y estadísticas para cobertura adicional.
