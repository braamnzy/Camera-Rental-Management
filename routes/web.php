<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Blade Views Engine)
|--------------------------------------------------------------------------
*/

// Redirect awal ke Katalog
Route::get('/', function () {
    return redirect('/catalog');
});

// Auth Views
Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');

Route::post('/admin/users/{user}/approve', [AdminDashboardController::class, 'approveUser'])->name('admin.users.approve');
Route::post('/admin/users/{user}/reject', [AdminDashboardController::class, 'rejectUser'])->name('admin.users.reject');
// Customer Portal Views
Route::view('/catalog', 'customer.catalog')->name('catalog.index');
Route::view('/catalog/{id}', 'customer.detail')->name('catalog.show');
Route::view('/booking/{id}', 'customer.booking-form')->name('booking.form');
Route::view('/my-rentals', 'customer.my-rentals')->name('my-rentals');

// Admin Portal Views
Route::prefix('admin')->group(function () {
    Route::view('/dashboard', 'admin.dashboard')->name('admin.dashboard');
    Route::view('/cameras', 'admin.cameras.index')->name('admin.cameras.index');
    Route::view('/cameras/create', 'admin.cameras.create')->name('admin.cameras.create');
    Route::view('/cameras/{id}/edit', 'admin.cameras.edit')->name('admin.cameras.edit');
    Route::view('/rentals', 'admin.rentals.index')->name('admin.rentals.index');
    Route::view('/schedule', 'admin.rentals.schedule')->name('admin.schedule');
});