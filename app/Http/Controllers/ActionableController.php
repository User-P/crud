<?php

namespace App\Http\Controllers;

use App\Exports\ActionableExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ActionableController extends Controller
{
    public function export(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        return Excel::download(
            new ActionableExport($startDate, $endDate),
            'actionable.xlsx'
        );
    }
}
