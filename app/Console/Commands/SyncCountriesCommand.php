<?php

namespace App\Console\Commands;

use App\Models\Country;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncCountriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'countries:sync
                           {--force : Forzar sincronización completa}
                           {--limit=50 : Límite de países a procesar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar países desde API externa (RestCountries)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🌍 Iniciando sincronización de países...');

        $startTime = microtime(true);
        $force = $this->option('force');
        $limit = (int) $this->option('limit');

        try {
            // Verificar si ya hay datos y no es forzado
            if (!$force && Country::count() > 0) {
                if (!$this->confirm('Ya existen países en la base de datos. ¿Continuar?')) {
                    $this->info('Sincronización cancelada.');
                    return Command::SUCCESS;
                }
            }

            // Consumir API
            $this->info('📡 Conectando con RestCountries API...');
            $response = Http::retry(3, 500)
                ->timeout(60)
                ->get('https://restcountries.com/v3.1/lang/spanish');

            if (!$response->successful()) {
                $this->error('❌ Error al conectar con la API externa');
                return Command::FAILURE;
            }

            $countriesData = $response->json();
            $total = count($countriesData);

            $this->info("📊 Total de países obtenidos: {$total}");

            // Aplicar límite si se especifica
            if ($limit > 0 && $limit < $total) {
                $countriesData = array_slice($countriesData, 0, $limit);
                $this->info("⚡ Procesando solo {$limit} países por el límite especificado");
            }

            // Barra de progreso
            $bar = $this->output->createProgressBar(count($countriesData));
            $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

            $processed = 0;
            $errors = 0;

            foreach ($countriesData as $countryData) {
                $bar->setMessage("Procesando: " . ($countryData['name']['common'] ?? 'N/A'));

                try {
                    $country = $this->processCountryData($countryData);

                    if ($country) {
                        Country::updateOrCreate(
                            ['code' => $country['code']],
                            $country
                        );
                        $processed++;
                    } else {
                        $errors++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    Log::warning('Error procesando país: ' . $e->getMessage());
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            // Estadísticas finales
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            $totalInDb = Country::count();

            $this->info("✅ Sincronización completada en {$duration}s");
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Países procesados', $processed],
                    ['Errores', $errors],
                    ['Total en BD', $totalInDb],
                    ['Tiempo transcurrido', "{$duration}s"],
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error crítico: ' . $e->getMessage());
            Log::error('Error en sincronización de países', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Procesar datos de un país desde la API externa
     */
    private function processCountryData(array $data): ?array
    {
        try {
            // Validar campos mínimos
            if (!isset($data['name']['common']) || !isset($data['cca2'])) {
                return null;
            }

            return [
                'name' => $data['name']['common'],
                'code' => $data['cca2'],
                'capital' => isset($data['capital'][0]) ? $data['capital'][0] : null,
                'population' => $data['population'] ?? 0,
                'region' => $data['region'] ?? null,
                'subregion' => $data['subregion'] ?? null,
                'flag_url' => $data['flags']['png'] ?? null,
                'currencies' => isset($data['currencies']) ? array_keys($data['currencies']) : [],
                'languages' => isset($data['languages']) ? array_values($data['languages']) : [],
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
