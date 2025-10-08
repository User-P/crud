<?php

namespace App\Http\Controllers;

use App\Exports\MainTemplate;
use Maatwebsite\Excel\Facades\Excel;

class TemplateDownloadController extends Controller
{
    public function __invoke()
    {
        return Excel::download(new MainTemplate(), 'event-records-template.xlsx');
    }
}
