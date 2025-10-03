<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Importar registros de eventos</title>
    <style>
        :root {
            color-scheme: light dark;
        }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            padding: 2rem;
            background: #f7f7f8;
        }
        .container {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
        }
        h1 {
            margin-top: 0;
            font-size: 1.9rem;
            color: #111827;
        }
        p.description {
            color: #4b5563;
            margin-bottom: 1.5rem;
        }
        form {
            display: grid;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        label {
            font-weight: 600;
            color: #1f2937;
        }
        input[type="file"] {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #f9fafb;
        }
        button {
            justify-self: start;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }
        button[disabled] {
            cursor: progress;
            opacity: 0.7;
        }
        .alert {
            padding: 0.85rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert.success {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .alert.error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
        }
        .card strong {
            display: block;
            font-size: 1.6rem;
            margin-top: 0.35rem;
            color: #111827;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.25rem;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 0.75rem;
            text-align: left;
        }
        th {
            background: #f3f4f6;
        }
        code {
            background: #eef2ff;
            padding: 0.25rem 0.4rem;
            border-radius: 4px;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Importar registros de eventos</h1>
        <p class="description">Selecciona un archivo CSV o Excel con las columnas <code>source</code>, <code>category</code>, <code>description</code>, <code>notes</code> y <code>recorded_at</code>. El envío se realiza mediante AJAX hacia la API.</p>

        <div id="feedback" class="alert hidden"></div>

        <form id="import-form" enctype="multipart/form-data">
            @csrf
            <div>
                <label for="records_file">Archivo de registros</label>
                <input type="file" name="records_file" id="records_file" accept=".csv,.xlsx,.xls" required>
            </div>
            <button type="submit">Importar</button>
        </form>

        <section id="results" class="hidden">
            <h2>Resumen</h2>
            <div class="summary" id="summary-cards"></div>

            <div id="failures-section" class="hidden">
                <h3>Filas con errores</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Fila</th>
                            <th>Campo</th>
                            <th>Errores</th>
                            <th>Valores originales</th>
                        </tr>
                    </thead>
                    <tbody id="failures-body"></tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        const form = document.getElementById('import-form');
        const feedback = document.getElementById('feedback');
        const resultsSection = document.getElementById('results');
        const summaryCards = document.getElementById('summary-cards');
        const failuresSection = document.getElementById('failures-section');
        const failuresBody = document.getElementById('failures-body');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function setFeedback(message, type) {
            feedback.textContent = message;
            feedback.classList.remove('hidden', 'success', 'error');
            feedback.classList.add(type === 'success' ? 'success' : 'error');
        }

        function clearFeedback() {
            feedback.classList.add('hidden');
            feedback.textContent = '';
        }

        function renderSummary(summary) {
            summaryCards.innerHTML = '';
            const labels = {
                inserted: 'Insertados',
                skipped: 'Saltados',
                processed: 'Procesados'
            };

            Object.entries(summary).forEach(([key, value]) => {
                const card = document.createElement('div');
                card.className = 'card';
                card.innerHTML = `<span>${labels[key] ?? key}</span><strong>${value}</strong>`;
                summaryCards.appendChild(card);
            });
        }

        function renderFailures(failures) {
            if (!failures.length) {
                failuresSection.classList.add('hidden');
                failuresBody.innerHTML = '';
                return;
            }

            failuresSection.classList.remove('hidden');
            failuresBody.innerHTML = failures.map(failure => {
                const errors = failure.errors.map(error => `<li>${error}</li>`).join('');
                const values = JSON.stringify(failure.values, null, 2);
                return `
                    <tr>
                        <td>${failure.row}</td>
                        <td><code>${failure.attribute}</code></td>
                        <td><ul>${errors}</ul></td>
                        <td><pre>${values}</pre></td>
                    </tr>
                `;
            }).join('');
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearFeedback();

            const fileInput = document.getElementById('records_file');
            if (!fileInput.files.length) {
                setFeedback('Selecciona un archivo antes de enviar.', 'error');
                return;
            }

            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = true;

            const formData = new FormData();
            formData.append('records_file', fileInput.files[0]);

            try {
                const response = await fetch('{{ route('records.import') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData,
                });

                const payload = await response.json();

                if (!response.ok || !payload.success) {
                    setFeedback(payload.message ?? 'No fue posible completar la importación.', 'error');

                    if (payload?.data?.summary) {
                        renderSummary(payload.data.summary);
                        renderFailures(payload.data.failures ?? []);
                        resultsSection.classList.remove('hidden');
                    }

                    return;
                }

                setFeedback(payload.message ?? 'Importación finalizada.', 'success');
                renderSummary(payload.data.summary ?? {});
                renderFailures(payload.data.failures ?? []);
                resultsSection.classList.remove('hidden');
            } catch (error) {
                console.error(error);
                setFeedback('Ocurrió un error inesperado. Revisa la consola para más detalles.', 'error');
            } finally {
                submitButton.disabled = false;
            }
        });
    </script>
</body>
</html>
