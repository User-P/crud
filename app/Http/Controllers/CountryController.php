<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CountryController extends Controller
{
    /**
     * Obtener todos los países desde la base de datos
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Country::class);

        $countries = Country::query()
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Países obtenidos correctamente',
            'data' => $countries,
        ]);
    }

    /**
     * Sincronizar países desde la API externa
     */
    public function syncCountries(): JsonResponse
    {
        $this->authorize('sync', Country::class);

        try {
            // Consumir API de países (RestCountries)
            $response = Http::retry(3, 500)
                ->timeout(30)
                ->get('https://restcountries.com/v3.1/all');

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al conectar con la API externa',
                ], 500);
            }

            $countriesData = $response->json();
            $processed = 0;

            foreach ($countriesData as $countryData) {
                $country = $this->processCountryData($countryData);

                if ($country) {
                    Country::updateOrCreate(
                        ['code' => $country['code']],
                        $country
                    );
                    $processed++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Sincronización completada. {$processed} países procesados.",
                'data' => [
                    'total_processed' => $processed,
                    'total_in_db' => Country::count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error sincronizando países: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno al sincronizar países',
                'error' => [
                    'type' => 'sync_error',
                    'details' => app()->environment('production') ? [] : [
                        'message' => $e->getMessage(),
                        'line' => $e->getLine(),
                    ],
                ],
            ], 500);
        }
    }

    /**
     * Procesar datos de un país desde la API externa
     */
    private function processCountryData(array $data): ?array
    {
        try {
            // Validar que tenga los campos mínimos requeridos
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
            Log::warning('Error procesando país: ' . $e->getMessage(), ['data' => $data]);
            return null;
        }
    }

    /**
     * Obtener estadísticas de países
     */
    public function statistics(): JsonResponse
    {
        $this->authorize('viewStatistics', Country::class);

        $stats = [
            'total_countries' => Country::count(),
            'by_region' => Country::selectRaw('region, COUNT(*) as count')
                ->whereNotNull('region')
                ->groupBy('region')
                ->pluck('count', 'region')
                ->toArray(),
            'largest_population' => Country::orderBy('population', 'desc')
                ->first(['name', 'population'])?->toArray(),
            'smallest_population' => Country::where('population', '>', 0)
                ->orderBy('population', 'asc')
                ->first(['name', 'population'])?->toArray(),
            'last_sync' => optional(Country::latest('updated_at')->value('updated_at'))
                ?->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Estadísticas de países obtenidas correctamente',
            'data' => $stats,
        ]);
    }
}
