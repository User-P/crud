# Guía rápida de SAML 2.0 con Okta

Este repo de pruebas trae rutas y controlador de ejemplo para integrar SAML 2.0 (Okta como IdP, Laravel como SP). Usa el paquete `aacotroneo/laravel-saml2` + OneLogin. La lógica principal vive en `app/Http/Controllers/Auth/SamlController.php` y las rutas se activan solo si `SAML_ENABLED=true`. La configuración principal está en `config/saml2_settings.php` y el IdP Okta en `config/saml2/okta_idp_settings.php`.

## 1. Paquete necesario

- Instala el SP SAML: `composer require aacotroneo/laravel-saml2` (usa la versión compatible con tu Laravel).  
- Si Composer bloquea la instalación por advisories de Symfony/Laravel, actualiza dependencias a versiones sin CVE o configura `composer audit` según la política de tu equipo.

## 2. Variables de entorno

Agrega/ajusta en `.env` (todas deben ser HTTPS/TLS 1.2+):

- `SAML_ENABLED=true`
- `SAML_IDP_NAME=okta` (nombre del IdP en config; genera `config/saml2/okta_idp_settings.php`)
- `SAML_SP_ENTITY_ID=` identificador de la app (solo letras, sin espacios/números según el requerimiento).
- `SAML_SP_ACS=` URL ACS, ej. `https://tu-app.com/saml/acs`
- `SAML_SP_SLS=` URL de logout si lo usas, ej. `https://tu-app.com/saml/sls`
- `SAML_SP_CERT` / `SAML_SP_PRIVATE_KEY` certificado y llave privada (2048 bits) exclusivos para SAML, uno por ambiente.
- `SAML_IDP_ENTITY_ID` / `SAML_IDP_SSO_URL` / `SAML_IDP_SLO_URL` / `SAML_IDP_CERT` datos entregados por Okta.
- `SAML_EMPLOYEE_ATTR=employeeNumber` (o el nombre exacto del atributo que Okta envía).
- `SAML_SIGN_REQUESTS`, `SAML_WANT_ASSERTIONS_SIGNED`, `SAML_WANT_ASSERTIONS_ENCRYPTED` en `true` para cumplir con firma y cifrado.

`config/saml2_settings.php` y `config/saml2/okta_idp_settings.php` usan estas variables y generan metadata en `/saml/metadata`.

## 3. Rutas disponibles (solo si `SAML_ENABLED=true`)

- `GET /saml/login` → redirige a Okta (IdP).
- `POST /saml/acs` → ACS: valida firma/cifrado y procesa la aserción.
- `GET /saml/metadata` → metadata del SP para registrar en Okta.
- `GET /saml/sls` → opcional, procesa Single Logout.

Si quieres que el login por defecto sea SAML, apunta tu botón `/login` a `route('saml.login')` y deshabilita las rutas OAuth/OIDC actuales.

## 4. Modelo de usuario

- Se añadió la columna `employee_number` (ver migración `database/migrations/2025_10_04_000001_add_employee_number_to_users_table.php`).
- El ACS busca usuarios por `employee_number`. Si no existe, llama a `lookupDirectoryProfile()` para completar datos (ahí debes conectar tu BD o SailPoint). Se genera password aleatorio y se marca `email_verified_at`.

## 5. Configuración en Okta (IdP)

1. Crear app SAML 2.0.  
2. ACS: `https://tu-app.com/saml/acs` (POST). Entity ID: el valor de `SAML_SP_ENTITY_ID` (solo letras).  
3. Atributo/Claim: enviar únicamente `employeeNumber` con el nombre exacto configurado en `SAML_EMPLOYEE_ATTR`.  
4. Subir el certificado público de tu SP (SAML_SP_CERT) y habilitar cifrado de aserciones.  
5. Copiar `IdP Entity ID`, `SSO URL`, `SLO URL` y el certificado IdP en `.env`.  
6. Descargar el metadata XML de tu app (opcional) y compara con `/saml/metadata` de este SP.

## 6. Seguridad

- Se fuerza firma de requests/responses y cifrado de aserciones en `config/saml2/okta_idp_settings.php`.
- Usa certificados distintos al de HTTPS (uno por ambiente).
- Asegura que `APP_URL` y las URLs SAML usen HTTPS con TLS 1.2+.
- Ajusta la política de `RelayState`/`intended` según tu flujo interno.

## 7. Flujo esperado

1. Usuario visita `/saml/login` → redirección a Okta.  
2. Okta autentica y responde al ACS con aserción cifrada/firmada.  
3. `/saml/acs` valida firma/cifrado, lee `employeeNumber`, busca usuario en tu BD (o SailPoint), lo crea/actualiza y hace `Auth::login`.  
4. Redirección al destino almacenado en sesión (`/dashboard` por defecto).

## 8. Próximos pasos en el repo real

- Instalar el paquete, correr `php artisan vendor:publish --provider="Aacotroneo\\Saml2\\Saml2ServiceProvider"` si quieres partir de la config del vendor (opcional; aquí ya tienes config personalizada).
- Subir tus llaves/cerificados SAML y probar `/saml/metadata` desde Okta.  
- Probar ACS con un usuario de pruebas que tenga `employeeNumber` y verifica que la app cree o sincronice al usuario.  
- Ajustar `lookupDirectoryProfile()` para leer datos reales (BD/SailPoint) y asignar roles según tu modelo.
