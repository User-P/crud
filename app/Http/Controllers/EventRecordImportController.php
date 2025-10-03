<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventRecordImportRequest;
use App\Imports\EventRecordsImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class EventRecordImportController extends Controller
{
    public function store(EventRecordImportRequest $request): JsonResponse
    {
        $import = new EventRecordsImport();

        try {
            Excel::import($import, $request->file('records_file'));
        } catch (Throwable $exception) {
            Log::error('Error al importar registros', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No fue posible procesar el archivo. Revisa los logs.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registros importados correctamente.',
            'data' => [
                'summary' => $import->getSummary(),
                'failures' => $import->getFormattedFailures(),
            ],
        ]);
    }
}
