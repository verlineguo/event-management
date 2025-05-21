<?php

use App\Http\Controllers\AdminMainController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuestMainController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');


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

Route::prefix('admin')->middleware(['auth.jwt', 'role:admin'])->group(function () {
    Route::controller(AdminMainController::class)->group(function() {
        Route::get('/dashboard', 'index')->name('admin.dashboard');
    });

    Route::controller(UserController::class)->group( function() {
        Route::get('/user',  'index')->name('admin.user.index');
        Route::get('/user/create',  'create')->name('admin.user.create');
        Route::post('/user',  'store')->name('admin.user.store');
        Route::get('/user/{id}/edit',  'edit')->name('admin.user.edit');
        Route::put('/user/{id}',  'update')->name('admin.user.update');
        Route::delete('/user/{id}',  'destroy')->name('admin.user.destroy');
    });


});

Route::prefix('guest')->group(function () {
    Route::controller(GuestMainController::class)->group(function() {
        Route::get('/home', 'index')->name('guest.home');
    });
});

Route::get('/user/dashboard', function () {
    return view('user.dashboard');
})->middleware(['auth', 'role:2']);

Route::get('/check-session', function () {
    return session()->all();
});
