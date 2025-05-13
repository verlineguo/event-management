<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

Route::controller(AuthController::class)->group(function () {
    // Show forms
    Route::get('/login', 'showLogin')->name('login');
    Route::get('/register', 'showRegister')->name('register');
    
    // Handle form submissions
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    
    // Protected routes
    Route::get('/profile', 'profile')->name('profile');
    Route::post('/logout', 'logout')->name('logout');
});

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth.jwt', 'role:1']);


Route::get('/user/dashboard', function () {
    return view('user.dashboard');
})->middleware(['auth', 'role:2']);

Route::get('/check-session', function () {
    return session()->all();
});
