<?php

namespace App\Http\Controllers;

use App\Exports\EventRecordsTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class TemplateDownloadController extends Controller
{
    public function __invoke()
    {
        return Excel::download(new EventRecordsTemplateExport(), 'event-records-template.xlsx');
    }
}
