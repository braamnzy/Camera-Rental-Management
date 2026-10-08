<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CameraController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

// Public Auth Routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Public Routes (Katalog Kamera)
Route::get('/cameras', [CameraController::class, 'index']);
Route::get('/cameras/{id}', [CameraController::class, 'show']);


// Authenticated Auth Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

// Admin Protected Routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/cameras', [CameraController::class, 'store']);
    Route::post('/cameras/{id}', [CameraController::class, 'update']); // Gunakan POST + _method=PUT di Form Data
    Route::delete('/cameras/{id}', [CameraController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Endpoints In-App Notification
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});
?>