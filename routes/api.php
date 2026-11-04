<?php

use App\Http\Controllers\Api\AgentAuthController;
use App\Http\Controllers\Api\AgentDashboardController;
use App\Http\Controllers\Api\AgentScanController;
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

use App\Http\Controllers\Api\AgentOfflineController;
Route::middleware(['auth:sanctum', 'agent.api', 'abilities:agent:read'])->prefix('agent/offline')->group(function () {
    Route::get('/state', [AgentOfflineController::class, 'state']);
    Route::get('/manifest', [AgentOfflineController::class, 'manifest']);
    Route::get('/delta', [AgentOfflineController::class, 'delta']);
    Route::post('/activate', [AgentOfflineController::class, 'activate']);
    Route::post('/revocation/ack', [AgentOfflineController::class, 'revocationAck']);
    Route::post('/heartbeat', [AgentOfflineController::class, 'heartbeat']);
});
Route::middleware(['auth:sanctum', 'agent.api', 'abilities:agent:read'])->post('/agent/offline/sync', [AgentOfflineController::class, 'sync']);

