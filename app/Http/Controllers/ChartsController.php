<?php

namespace App\Http\Controllers;

use App\Services\MapeoFuentes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ChartsController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Charts/Index');
    }
}
