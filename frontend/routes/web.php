<?php

use App\Http\Controllers\AdminMainController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommitteeMainController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FinanceMainController;
use App\Http\Controllers\GuestMainController;
use App\Http\Controllers\MemberMainController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
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

    Route::get('/profile', function () {
        return view('admin.profile');
    })->name('profile');

    Route::middleware(['auth'])->group(function () {
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    });
});

Route::prefix('finance')->middleware(['auth.jwt', 'role:finance'])->group(function () {
    Route::controller(FinanceMainController::class)->group(function() {
        Route::get('/dashboard', 'index')->name('finance.dashboard');
    });

    Route::get('/profile', function () {
        return view('finance.profile');
    })->name('profile');

    Route::middleware(['auth'])->group(function () {
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    });
});


Route::prefix('committee')->middleware(['auth.jwt', 'role:committee'])->group(function () {
    Route::controller(CommitteeMainController::class)->group(function() {
        Route::get('/dashboard', 'index')->name('committee.dashboard');
    
    Route::controller(MemberRegistrationController::class)->group(function () {
        Route::get('/events/{id}/register', 'registerEvent')->name('member.events.register');
        Route::post('/events/{id}/register', 'storeRegistration')->name('member.events.store-registration');
        Route::get('{id}/payment', 'showPayment')->name('member.events.payment');
        Route::post('{id}/payment', 'processPayment')->name('member.events.process-payment');
        Route::get('{id}/success/{registration_id}', 'registrationSuccess')->name('member.events.registration-success');
        Route::get('/registrations', 'myRegistrations')->name('member.myRegistrations.index');
        Route::get('/registrations/{id}/qr-codes', 'showQRCodes')->name('member.myRegistrations.qr-codes');
        Route::patch('/registrations/{id}/cancel', 'cancelRegistration')->name('member.myRegistrations.cancel');
        Route::get('/registrations/{id}/payment-proof', 'downloadPaymentProof')->name('member.myRegistrations.payment-proof');
        Route::post('/registration-draft/{id}', 'saveDraftRegistration')->name('member.registration-draft.save');
        Route::get('/registration-draft/{id}', 'getDraft')->name('member.registration-draft.get');
        Route::delete('/registration-draft/{id}', 'deleteDraft')->name('member.registration-draft.delete');
        Route::get('/registrations/{id}', 'showRegistration')->name('member.myRegistrations.show');
    });

        Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'showMyProfile'])->name('profile.show.mine');
        Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.show');

        Route::get('/settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    });

    Route::get('/profile', function () {
        return view('committee.profile');
    })->name('profile');

    Route::middleware(['auth'])->group(function () {
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    });
});

    Route::controller(EventController::class)->group( function() {
        Route::get('/event',  'index')->name('committee.event.index');
        Route::get('/event/create',  'create')->name('committee.event.create');
        Route::post('/event',  'store')->name('committee.event.store');
        Route::get('/event/{id}/edit',  'edit')->name('committee.event.edit');
        Route::put('/event/{id}',  'update')->name('committee.event.update');
        Route::get('event/show/{id}',  'show')->name('committee.event.show');
        Route::delete('/event/{id}',  'destroy')->name('committee.event.destroy');
    });



});

Route::prefix('member')->middleware(['auth.jwt', 'role:member'])->group(function () {
    Route::controller(MemberMainController::class)->group(function() {
        Route::get('/home', action: 'index')->name('member.home');
    });

    Route::get('/profile', function () {
        return view('member.profile');
    })->name('profile');

    Route::middleware(['auth'])->group(function () {
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    });

});


Route::prefix('guest')->group(function () {
    Route::controller(GuestMainController::class)->group(function() {
        Route::get('/home', 'index')->name('guest.home');
    });
    Route::controller(GuestMainController::class)->group(function() {
        Route::get('/event', [GuestMainController::class, 'about'])->name('guest.events');
    });
});


Route::get('/check-session', function () {
    return session()->all();
});
