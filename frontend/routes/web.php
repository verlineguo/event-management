<?php

use App\Http\Controllers\AdminMainController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CommitteeMainController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FinanceMainController;
use App\Http\Controllers\GuestMainController;
use App\Http\Controllers\MemberMainController;
use App\Http\Controllers\MemberRegistrationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/guest/home');

Route::controller(AuthController::class)->group(function () {
    // Show forms
    Route::get('/login', 'showLogin')->name('login');
    Route::get('/register', 'showRegister')->name('register');

    // Handle form submissions
    Route::post('/login', 'login');
    Route::post('/register', 'register');

    Route::post('/logout', 'logout')->name('logout');
});



Route::prefix('admin')
    ->middleware(['auth.jwt', 'role:admin'])
    ->group(function () {
        Route::controller(AdminMainController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('admin.dashboard');
            Route::get('/profile', 'profile')->name('admin.profile');
            Route::patch('/profile', 'update')->name('admin.profile.update');
            Route::put('/profile/password', 'updatePassword')->name('admin.profile.update-password');
            Route::get('/profile', 'profile')->name('admin.profile');
            Route::patch('/profile', 'update')->name('admin.profile.update');
            Route::put('/profile/password', 'updatePassword')->name('admin.profile.update-password');
        });

        Route::controller(UserController::class)->group(function () {
            Route::get('/user', 'index')->name('admin.user.index');
            Route::get('/user/create', 'create')->name('admin.user.create');
            Route::post('/user', 'store')->name('admin.user.store');
            Route::get('/user/{id}/edit', 'edit')->name('admin.user.edit');
            Route::put('/user/{id}', 'update')->name('admin.user.update');
            Route::delete('/user/{id}', 'destroy')->name('admin.user.destroy');
        });

        Route::controller(CategoryController::class)->group(function () {
            Route::get('/category', 'index')->name('admin.category.index');
            Route::get('/category/create', 'create')->name('admin.category.create');
            Route::post('/category', 'store')->name('admin.category.store');
            Route::get('/category/{id}/edit', 'edit')->name('admin.category.edit');
            Route::put('/category/{id}', 'update')->name('admin.category.update');
            Route::delete('/category/{id}', 'destroy')->name('admin.category.destroy');
        });

         Route::controller(RoleController::class)->group(function () {
            Route::get('/role', 'index')->name('admin.role.index');
            Route::get('/role/create', 'create')->name('admin.role.create');
            Route::post('/role', 'store')->name('admin.role.store');
            Route::get('/role/{id}/edit', 'edit')->name('admin.role.edit');
            Route::put('/role/{id}', 'update')->name('admin.role.update');
            Route::delete('/role/{id}', 'destroy')->name('admin.role.destroy');
        });
        
    });

Route::prefix('finance')
    ->middleware(['auth.jwt', 'role:finance'])
    ->group(function () {
        Route::controller(FinanceMainController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('finance.dashboard');

            Route::get('/profile', 'profile')->name('finance.profile');
            Route::patch('/profile', 'update')->name('finance.profile.update');
            Route::put('/profile/password', 'updatePassword')->name('finance.profile.update-password');


            Route::get('/profile', 'profile')->name('finance.profile');
            Route::patch('/profile', 'update')->name('finance.profile.update');
            Route::put('/profile/password', 'updatePassword')->name('finance.profile.update-password');

        });

        Route::controller(PaymentController::class)->group(function () {
            Route::get('/payment', 'index')->name('finance.payment.index');
            Route::get('/payment/{id}', 'show')->name('finance.payment.show');
            Route::put('/payment/{id}/status', 'updateStatus')->name('finance.payment.update-status');
            Route::post('/payment/bulk-approve', 'bulkApprove')->name('finance.payment.bulk-approve');
        });


    });

Route::prefix('committee')
    ->middleware(['auth.jwt', 'role:committee'])
    ->group(function () {
        Route::controller(CommitteeMainController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('committee.dashboard');
            Route::get('/profile', 'profile')->name('committee.profile');
            Route::patch('/profile', 'update')->name('committee.profile.update');
            Route::put('/profile/password', 'updatePassword')->name('committee.profile.update-password');
        
            Route::get('/profile', 'profile')->name('committee.profile');
            Route::patch('/profile', 'update')->name('committee.profile.update');
            Route::put('/profile/password', 'updatePassword')->name('committee.profile.update-password');
        
        });

        Route::controller(EventController::class)->group(function () {
            Route::get('/event', 'index')->name('committee.event.index');
            Route::get('/event/create', 'create')->name('committee.event.create');
            Route::post('/event', 'store')->name('committee.event.store');
            Route::get('/event/{id}/edit', 'edit')->name('committee.event.edit');
            Route::put('/event/{id}', 'update')->name('committee.event.update');
            Route::get('event/show/{id}', 'show')->name('committee.event.show');
            Route::delete('/event/{id}', 'destroy')->name('committee.event.destroy');
            Route::get('/event/search', 'search')->name('committee.event.search');
            Route::patch('/event/{id}/status', 'updateStatus')->name('committee.event.status.update');
        });

        Route::controller(AttendanceController::class)->group(function () {
            Route::get('/event/{id}/participants', 'participants')->name('committee.event.participants');
            Route::get('/event/{id}/scan-qr', 'scanQR')->name('committee.event.scan-qr');
            Route::post('/event/scan-qr', 'processScan')->name('committee.event.process-scan');
            Route::post('/event/manual', 'manualCheckIn')->name('committee.event.process-manual');
            Route::get('/attendance/session/{id}', 'sessionAttendance')->name('committee.attendance.session');
            Route::get('/participants/{id}/details', 'participantDetails')->name('committee.participant.details');
        });
        Route::controller(CertificateController::class)->group(function () {
            Route::post('/attendance/session/{id}/upload-certificate', 'uploadCertificate')->name('committee.attendance.upload-certificate');
            Route::post('/attendance/session/{id}/bulk-upload-certificates', 'bulkUploadCertificates')->name('committee.attendance.bulk-upload-certificates');
            Route::get('certificate/download/{id}', 'downloadCertificate')->name('committee.certificate.download');
            Route::delete('certificate/revoke/{id}', 'revokeCertificate')->name('committee.certificate.revoke');

            Route::get('/attendance/{sessionId}/export', 'exportAttendance')->name('committee.attendance.export');
        });

        

        
    });

Route::prefix('member')
    ->middleware(['auth.jwt', 'role:member'])
    ->group(function () {
        Route::controller(MemberMainController::class)->group(function () {
            Route::get('/home', action: 'index')->name('member.home');
            Route::get('/events', 'events')->name('member.events.index');
            Route::get('/events/{id}', 'showEvent')->name('member.events.show');
            Route::get('/events/search', 'search')->name('member.event.search');
            Route::get('/profile', 'profile')->name('member.profile');
            Route::patch('/profile', 'update')->name('member.profile.update');
            Route::put('/profile/password', 'updatePassword')->name('member.profile.update-password');
            Route::get('/profile/current-user', 'getCurrentUser')->name('current-user');

            Route::get('/profile', 'profile')->name('member.profile');
            Route::patch('/profile', 'update')->name('member.profile.update');
            Route::put('/profile/password', 'updatePassword')->name('member.profile.update-password');
            Route::get('/profile/current-user', 'getCurrentUser')->name('current-user');

        });

        Route::controller(MemberRegistrationController::class)->group(function () {
            Route::get('/events/{id}/register', 'registerEvent')->name('member.events.register');
            Route::post('/events/{id}/register', 'storeRegistration')->name('member.events.store-registration');
            Route::get('{id}/payment', 'showPayment')->name('member.events.payment');
            Route::post('{id}/payment', 'processPayment')->name('member.events.process-payment');
            Route::get('{id}/success/{registration_id}', 'registrationSuccess')->name('member.events.registration-success');
            Route::get('/registrations', 'myRegistrations')->name('member.myRegistrations.index');
            Route::get('/registrations/{id}/qr-codes', 'showQRCodes')->name('member.myRegistrations.qr-codes');
            Route::patch('/registrations/{id}/cancel', 'cancelRegistration')->name('member.myRegistrations.cancel');
            Route::post('/registrations/{id}/reupload-payment',  'reuploadPayment')->name('member.myRegistrations.reupload-payment');
            Route::get('/registrations/{id}/payment-proof', 'downloadPaymentProof')->name('member.myRegistrations.payment-proof');
            Route::post('/registration-draft/{id}', 'saveDraftRegistration')->name('member.registration-draft.save');
            Route::get('/registration-draft/{id}', 'getDraft')->name('member.registration-draft.get');
            Route::delete('/registration-draft/{id}', 'deleteDraft')->name('member.registration-draft.delete');
            Route::get('/registrations/{id}', 'showRegistration')->name('member.myRegistrations.show');
        });

        Route::controller(CertificateController::class)->group(function () {
            Route::get('certificate/download/{sessionId}/{userId}', 'downloadCertificateMember')->name('member.certificate.download');
        });

      

      
    });

Route::prefix('guest')->group(function () {
    Route::controller(GuestMainController::class)->group(function () {
        Route::get('/home', 'index')->name('guest.home');
        Route::get('/event', 'events')->name('guest.events');
        Route::get('/events/{id}', 'showEvent')->name('guest.events.show');
    });
    
});

Route::get('/check-session', function () {
    return session()->all();
});
