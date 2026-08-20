<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuditLogController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:3,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

    Route::middleware('permission:manage-user')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::put('/users/{user}/role', [UserController::class, 'assignRole']);
        Route::put('/users/{user}/status', [UserController::class, 'toggleStatus']);
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
    });
});