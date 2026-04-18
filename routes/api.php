<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\GatewayController;

Route::post('/gateway/register', [GatewayController::class, 'registerProject']);

Route::prefix('gateway')->middleware(['check.api.key', 'throttle:waseet-gateway'])->group(function () {
    Route::post('/connect-waseet', [GatewayController::class, 'connectWaseet']);
    Route::post('/create-order', [GatewayController::class, 'createOrder']);
    Route::post('/edit-order', [GatewayController::class, 'editOrder']);
    Route::get('/order-status/{id}', [GatewayController::class, 'getOrderStatus']);
    Route::post('/track-bulk', [GatewayController::class, 'trackBulk']);
    
    // Supplementary data (Cached can be added later)
    Route::get('/cities', [GatewayController::class, 'getCities']);
    Route::get('/regions', [GatewayController::class, 'getRegions']);
    Route::get('/package-sizes', [GatewayController::class, 'getPackageSizes']);
    Route::get('/statuses', [GatewayController::class, 'getStatuses']);
});

// WhatsApp API Routes
use App\Http\Controllers\WhatsappController;
Route::post('/v1/whatsapp/send', [WhatsappController::class, 'apiSendMessage']);
Route::post('/v1/whatsapp/webhook', [WhatsappController::class, 'webhook']);
