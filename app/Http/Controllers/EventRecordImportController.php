<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventRecordImportRequest;
use App\Imports\EventRecordsMainImport;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class EventRecordImportController extends Controller
{
    public function create(): View
    {
        return view('records.import');
    }

    public function store(EventRecordImportRequest $request)
    {
        $import = new EventRecordsMainImport();

        try {
            Excel::import($import, $request->file('records_file'));
        } catch (Throwable $exception) {

            dd($exception);
            // return response()->json([
            //     'success' => false,
            //     'message' => 'No fue posible procesar el archivo. Revisa los logs.',
            // ], 500);
        }

        dd($import->getSummary(), $import->failures());
        // return response()->json([
        //     'success' => true,
        //     'message' => 'Registros importados correctamente.',
        //     'data' => [
        //         'summary' => $import->getSummary(),
        //         'failures' => $import->getFormattedFailures(),
        //     ],
        // ]);
    }
}
