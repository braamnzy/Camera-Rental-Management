<?php

use Illuminate\Support\Facades\Route;

// Halaman Katalog Utama
Route::get('/', function () {
    return redirect('/catalog');
});

Route::get('/catalog', function () {
    return view('customer.catalog');
});

// Halaman Detail Kamera
Route::get('/catalog/{id}', function () {
    return view('customer.detail');
});

// Halaman Login & Register
Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/register', function () {
    return view('auth.register'); // Jika kamu membuat form register
});

// Halaman Booking Midtrans
Route::get('/booking/{id}', function () {
    return view('customer.booking-form');
});

// Halaman Riwayat Sewa
Route::get('/my-rentals', function () {
    return view('customer.my-rentals'); // Tugasmu selanjutnya
});
