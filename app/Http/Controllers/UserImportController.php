<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserImportRequest;
use App\Imports\UsersImport;
use App\Models\User;
use App\Rules\EmailVerifiedAtFormat;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class UserImportController extends Controller
{
    /**
     * Mostrar el formulario de importación de usuarios.
     */
    public function create(): View
    {

        return view('users.import', [
            'emailFormatHint' => EmailVerifiedAtFormat::HUMAN_READABLE_EXPECTATION,
        ]);
    }

    /**
     * Procesar el archivo subido y ejecutar la importación.
     */
    public function store(UserImportRequest $request): RedirectResponse
    {

        $import = new UsersImport();

        try {
            Excel::import($import, $request->file('users_file'));
        } catch (Throwable $exception) {
            Log::error('Error al importar usuarios', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return redirect()
                ->route('users.import.create')
                ->with('importError', 'Ocurrió un error al procesar el archivo. Revisa el log para más detalles.');
        }

        return redirect()
            ->route('users.import.create')
            ->with('status', 'Importación completada correctamente.')
            ->with('importSummary', $import->getSummary())
            ->with('importFailures', $import->getFormattedFailures());
    }
}
