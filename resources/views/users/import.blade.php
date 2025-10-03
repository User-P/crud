<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar usuarios</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f5f6f8; margin: 0; padding: 2rem; }
        .container { max-width: 960px; margin: 0 auto; background-color: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.1); }
        h1 { margin-top: 0; color: #1f2937; }
        p { color: #4b5563; }
        form { margin-top: 1.5rem; display: grid; gap: 1rem; }
        label { font-weight: 600; color: #1f2937; }
        input[type="file"], button { padding: 0.75rem; border-radius: 6px; border: 1px solid #d1d5db; }
        button { background-color: #2563eb; color: #fff; border: none; cursor: pointer; font-weight: 600; }
        button:hover { background-color: #1d4ed8; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert.success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert.error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .grid { display: grid; gap: 1rem; }
        .grid.two { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #e5e7eb; padding: 0.75rem; text-align: left; font-size: 0.95rem; }
        th { background-color: #f3f4f6; color: #1f2937; }
        code { background-color: #eef2ff; padding: 0.15rem 0.4rem; border-radius: 4px; font-size: 0.9rem; }
        ul { margin: 0 0 1rem 1.2rem; color: #4b5563; }
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-top: 1rem; }
        .summary-card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; background-color: #f9fafb; text-align: center; }
        .summary-card strong { display: block; font-size: 1.6rem; color: #111827; }
        .failures { margin-top: 2rem; }
        .failures h2 { margin-bottom: 0.5rem; }
        footer { margin-top: 2rem; font-size: 0.85rem; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Importar usuarios desde Excel</h1>
        <p>Selecciona un archivo Excel o CSV con la estructura indicada abajo. El sistema validará cada fila antes de crear o actualizar usuarios.</p>

        @if(session('status'))
            <div class="alert success">{{ session('status') }}</div>
        @endif

        @if(session('importError'))
            <div class="alert error">{{ session('importError') }}</div>
        @endif

        @if($errors->any())
            <div class="alert error">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('importSummary'))
            @php($summary = session('importSummary'))
            <section>
                <h2>Resumen de importación</h2>
                <div class="summary-grid">
                    <div class="summary-card">
                        <span>Nuevos usuarios</span>
                        <strong>{{ $summary['created'] }}</strong>
                    </div>
                    <div class="summary-card">
                        <span>Actualizados</span>
                        <strong>{{ $summary['updated'] }}</strong>
                    </div>
                    <div class="summary-card">
                        <span>Sin cambios</span>
                        <strong>{{ $summary['unchanged'] }}</strong>
                    </div>
                    <div class="summary-card">
                        <span>Filas con errores</span>
                        <strong>{{ $summary['skipped'] }}</strong>
                    </div>
                    <div class="summary-card">
                        <span>Filas procesadas</span>
                        <strong>{{ $summary['processed'] }}</strong>
                    </div>
                </div>
            </section>
        @endif

        <form method="POST" action="{{ route('users.import.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid">
                <div>
                    <label for="users_file">Archivo de usuarios</label>
                    <input id="users_file" name="users_file" type="file" accept=".xlsx,.xls,.csv" required>
                    <small>Máximo 5MB. Se aceptan archivos .xlsx, .xls o .csv.</small>
                </div>
            </div>
            <button type="submit">Importar usuarios</button>
        </form>

        <section>
            <h2>Formato esperado</h2>
            <p>El archivo debe incluir una fila de encabezados con las siguientes columnas:</p>
            <table>
                <thead>
                    <tr>
                        <th>Columna</th>
                        <th>Requerido</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>name</code></td>
                        <td>Sí</td>
                        <td>Nombre completo del usuario.</td>
                    </tr>
                    <tr>
                        <td><code>email</code></td>
                        <td>Sí</td>
                        <td>Correo electrónico único, validado con estándar RFC.</td>
                    </tr>
                    <tr>
                        <td><code>password</code></td>
                        <td>Condicional</td>
                        <td>Obligatoria para nuevos usuarios. Se cifra automáticamente si la columna contiene texto plano. Deja vacío para conservar la contraseña actual al actualizar un registro existente.</td>
                    </tr>
                    <tr>
                        <td><code>role</code></td>
                        <td>No</td>
                        <td>Valores permitidos: <code>admin</code> o <code>user</code>. Si se omite, se asigna <code>user</code>.</td>
                    </tr>
                    <tr>
                        <td><code>country_id</code></td>
                        <td>No</td>
                        <td>ID de país existente en la tabla <code>countries</code>.</td>
                    </tr>
                    <tr>
                        <td><code>email_verified_at</code></td>
                        <td>No</td>
                        <td>Marca al usuario como verificado. Usa el formato {{ $emailFormatHint }} o una fecha ISO 8601 (ej. <code>2024-09-29T18:30:00</code>). Deja vacío si el correo no está verificado.</td>
                    </tr>
                </tbody>
            </table>
            <p>Si trabajas en Excel, configura la columna <code>email_verified_at</code> como <strong>texto</strong> o <strong>fecha personalizada</strong> con el patrón <code>aaaa-mm-dd hh:mm:ss</code> para evitar que Excel modifique el valor. También se aceptan valores numéricos de fecha/hora generados por Excel.</p>
        </section>

        @if(session('importFailures') && count(session('importFailures')))
            <section class="failures">
                <h2>Filas con errores</h2>
                <p>Corrige los errores detectados y vuelve a importar solo las filas afectadas.</p>
                <table>
                    <thead>
                        <tr>
                            <th>Fila</th>
                            <th>Columna</th>
                            <th>Errores</th>
                            <th>Valores originales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(session('importFailures') as $failure)
                            <tr>
                                <td>{{ $failure['row'] }}</td>
                                <td><code>{{ $failure['attribute'] }}</code></td>
                                <td>
                                    <ul>
                                        @foreach($failure['errors'] as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    <pre>{{ json_encode($failure['values'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        <footer>
            <p>Consejo: conserva un respaldo del archivo original antes de importar. Las filas válidas se procesan automáticamente; las que tengan errores se omitirán sin afectar al resto.</p>
        </footer>
    </div>
</body>
</html>
