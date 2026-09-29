<?php

use App\Http\Controllers\Api\SaasDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/overview', [SaasDashboardController::class, 'overview']);
    Route::get('/super-admin/overview', [SaasDashboardController::class, 'superAdminOverview']);
    Route::get('/customer/overview', [SaasDashboardController::class, 'customerOverview']);
    Route::get('/documentation', [SaasDashboardController::class, 'documentation']);
});
