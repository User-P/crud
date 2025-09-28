<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'api works';
});

Route::prefix('v1')->group(function () {
    Route::resource('user', UserController::class);
});
