<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CameraController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RentalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
// Public Auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Katalog Kamera (Public)
Route::get('/cameras', [CameraController::class, 'index']);
Route::get('/cameras/{id}', [CameraController::class, 'show']);

// Public Webhook Notification Midtrans IPN (Bebas CSRF & Sanctum)
Route::post('/payments/midtrans-notification', [PaymentController::class, 'handleNotification']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Profile & Logout
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // In-App Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // Transaksi Penyewaan (Akses Bersama: Customer & Admin)
    Route::get('/rentals', [RentalController::class, 'index']);
    Route::get('/rentals/{id}', [RentalController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | Customer Only Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:customer')->group(function () {
        Route::post('/rentals', [RentalController::class, 'store']);
        Route::post('/payments/snap-token', [PaymentController::class, 'generateSnapToken']);
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Only Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        // Master Katalog Kamera
        Route::post('/cameras', [CameraController::class, 'store']);
        Route::match(['post', 'put', 'patch'], '/cameras/{id}', [CameraController::class, 'update']);
        Route::delete('/cameras/{id}', [CameraController::class, 'destroy']);

        // Transaksi Status & Schedule Admin
        Route::patch('/rentals/{id}/status', [RentalController::class, 'updateStatus']);
        Route::get('/admin/rentals/schedule', [RentalController::class, 'schedule']);
    });
});