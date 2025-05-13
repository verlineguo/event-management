<?php

use App\Http\Middleware\CheckJWTToken;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append([
    ]);
    
    // Middleware groups
    $middleware->group('web', [
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
        // Tambahkan middleware lain yang diperlukan
    ]);
    
    // Middleware alias
    $middleware->alias([
        'role' => RoleMiddleware::class,
        'auth.jwt' => CheckJWTToken::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
