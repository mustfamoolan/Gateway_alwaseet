<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\GatewayController;

Route::prefix('gateway')->middleware(['check.api.key', 'throttle:waseet-gateway'])->group(function () {
    Route::post('/create-order', [GatewayController::class, 'createOrder']);
    Route::post('/edit-order', [GatewayController::class, 'editOrder']);
    Route::get('/order-status/{id}', [GatewayController::class, 'getOrderStatus']);
    
    // Supplementary data (Cached can be added later)
    Route::get('/cities', [GatewayController::class, 'getCities']);
    Route::get('/regions', [GatewayController::class, 'getRegions']);
    Route::get('/package-sizes', [GatewayController::class, 'getPackageSizes']);
});
