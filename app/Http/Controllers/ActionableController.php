<?php

namespace App\Http\Controllers;

use App\Exports\ActionableExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ActionableController extends Controller
{
    public function export(Request $request){

        return Excel::download(new ActionableExport(), 'actionable.xlsx');
    }
}
