<?php

use App\Http\Controllers\Api\AgentAuthController;
use Illuminate\Support\Facades\Route;

Route::post('/agent/login', [AgentAuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/agent/me', [AgentAuthController::class, 'me']);
    Route::post('/agent/logout', [AgentAuthController::class, 'logout']);
});
