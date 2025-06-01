<?php

use App\Http\Controllers\AdminMainController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommitteeMainController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FinanceMainController;
use App\Http\Controllers\GuestMainController;
use App\Http\Controllers\MemberMainController;
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

    Route::controller(CategoryController::class)->group( function() {
        Route::get('/category',  'index')->name('admin.category.index');
        Route::get('/category/create',  'create')->name('admin.category.create');
        Route::post('/category',  'store')->name('admin.category.store');
        Route::get('/category/{id}/edit',  'edit')->name('admin.category.edit');
        Route::put('/category/{id}',  'update')->name('admin.category.update');
        Route::delete('/category/{id}',  'destroy')->name('admin.category.destroy');
        
    });



});

Route::prefix('finance')->middleware(['auth.jwt', 'role:finance'])->group(function () {
    Route::controller(FinanceMainController::class)->group(function() {
        Route::get('/dashboard', 'index')->name('finance.dashboard');
    });



});


Route::prefix('committee')->middleware(['auth.jwt', 'role:committee'])->group(function () {
    Route::controller(CommitteeMainController::class)->group(function() {
        Route::get('/dashboard', 'index')->name('committee.dashboard');
    });

    Route::controller(EventController::class)->group( function() {
        Route::get('/event',  'index')->name('committee.event.index');
        Route::get('/event/create',  'create')->name('committee.event.create');
        Route::post('/event',  'store')->name('committee.event.store');
        Route::get('/event/{id}/edit',  'edit')->name('committee.event.edit');
        Route::put('/event/{id}',  'update')->name('committee.event.update');
        Route::get('event/show/{id}',  'show')->name('committee.event.show');
        Route::delete('/event/{id}',  'destroy')->name('committee.event.destroy');
        Route::get('/event/search',  'search')->name('committee.event.search');

    });



});

Route::prefix('member')->middleware(['auth.jwt', 'role:member'])->group(function () {
    Route::controller(MemberMainController::class)->group(function() {
        Route::get('/home', action: 'index')->name('member.home');
    });

});


Route::prefix('guest')->group(function () {
    Route::controller(GuestMainController::class)->group(function() {
        Route::get('/home', 'index')->name('guest.home');
    });
    Route::controller(GuestMainController::class)->group(function() {
        Route::get('/event', 'about')->name('guest.events');
    });
});


Route::get('/check-session', function () {
    return session()->all();
});
