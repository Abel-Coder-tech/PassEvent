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
