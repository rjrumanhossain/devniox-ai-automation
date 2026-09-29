<?php

use App\Http\Controllers\Api\SaasDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/overview', [SaasDashboardController::class, 'overview']);
    Route::get('/documentation', [SaasDashboardController::class, 'documentation']);
});
