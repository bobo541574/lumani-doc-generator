<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;

Route::get('/{any?}', [DashboardController::class, 'index'])
    ->where('any', '.*')
    ->middleware('web');
