<?php

use App\Http\Controllers\Api\AgentAuthController;
use App\Http\Controllers\Api\AgentDashboardController;
use App\Http\Controllers\Api\AgentScanController;
use App\Http\Controllers\Api\AgentVenteAuthController;
use App\Http\Controllers\Api\AgentVenteDashboardController;
use App\Http\Controllers\Api\AgentVenteNotificationController;
use App\Http\Controllers\Api\AgentVenteSaleController;
use Illuminate\Support\Facades\Route;

Route::post('/agent/login', [AgentAuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware(['auth:sanctum', 'agent.api', 'abilities:agent:read'])->group(function () {
    Route::get('/agent/me', [AgentAuthController::class, 'me']);
    Route::get('/agent/dashboard', [AgentDashboardController::class, 'show']);
    Route::post('/agent/scan/pin', [AgentScanController::class, 'pin']);
    Route::post('/agent/logout', [AgentAuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'agent.api', 'abilities:agent:scan'])->group(function () {
    Route::post('/agent/scan/verify', [AgentScanController::class, 'verify'])->middleware('throttle:120,1');
    Route::get('/agent/scan/history', [AgentScanController::class, 'history']);
    Route::post('/agent/scan/exit', [AgentScanController::class, 'exit']);
});

// ============================================================
// API agent de vente (compte distinct des agents de scan)
// ============================================================
Route::prefix('agent-vente')->name('agent-vente.api.')->group(function () {
    Route::post('/login', [AgentVenteAuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware(['auth:sanctum', 'agent.vente.api', 'abilities:agent_vente:read'])->group(function () {
        Route::get('/me', [AgentVenteAuthController::class, 'me']);
        Route::get('/dashboard', [AgentVenteDashboardController::class, 'show']);
        Route::get('/tarifs', [AgentVenteSaleController::class, 'tarifs']);
        Route::get('/statistics', [AgentVenteSaleController::class, 'statistics']);

        Route::get('/sales', [AgentVenteSaleController::class, 'history']);
        Route::get('/sales/{ticket}', [AgentVenteSaleController::class, 'show'])->name('sales.show');
        Route::get('/sales/{ticket}/pdf', [AgentVenteSaleController::class, 'pdf'])->name('sales.pdf');

        Route::get('/notifications', [AgentVenteNotificationController::class, 'index']);
        Route::post('/notifications/read-all', [AgentVenteNotificationController::class, 'readAll']);
        Route::post('/notifications/{notification}/read', [AgentVenteNotificationController::class, 'read']);

        Route::post('/password', [AgentVenteAuthController::class, 'changePassword'])->middleware('throttle:10,1');
        Route::post('/refresh-token', [AgentVenteAuthController::class, 'refreshToken'])->middleware('throttle:10,1');

        Route::post('/logout', [AgentVenteAuthController::class, 'logout']);
    });

    Route::middleware(['auth:sanctum', 'agent.vente.api', 'abilities:agent_vente:sell'])->group(function () {
        Route::post('/sales/promo', [AgentVenteSaleController::class, 'verifierCodePromo'])->middleware('throttle:60,1');
        Route::post('/sales', [AgentVenteSaleController::class, 'store'])->middleware('throttle:60,1');
        Route::post('/sales/{ticket}/payment', [AgentVenteSaleController::class, 'payment'])
            ->middleware('throttle:30,1')
            ->name('sales.payment');
        Route::post('/sales/{ticket}/confirm', [AgentVenteSaleController::class, 'confirm'])
            ->middleware('throttle:30,1')
            ->name('sales.confirm');
        Route::post('/sales/{ticket}/sync', [AgentVenteSaleController::class, 'sync'])
            ->middleware('throttle:60,1')
            ->name('sales.sync');
        Route::post('/sales/{ticket}/resend', [AgentVenteSaleController::class, 'resend'])
            ->middleware('throttle:10,1')
            ->name('sales.resend');
        Route::post('/sales/{ticket}/cancel', [AgentVenteSaleController::class, 'cancel'])
            ->name('sales.cancel');
    });
});
