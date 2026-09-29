<?php

use App\Http\Controllers\PortalAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal-api')->group(function (): void {
    Route::post('/login', [PortalAuthController::class, 'login']);
    Route::get('/me', [PortalAuthController::class, 'me']);
    Route::middleware('auth')->group(function (): void {
        Route::get('/overview', [PortalAuthController::class, 'overview']);
        Route::post('/logout', [PortalAuthController::class, 'logout']);
    });
});

Route::get('/', function () {
    return view('app');
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
