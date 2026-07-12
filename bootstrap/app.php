<?php

use App\Http\Middleware\checkIsAdmin;
use App\Http\Middleware\EnsureProfileComplete;
use App\Http\Middleware\EnsureUserIsVerified;
use App\Http\Middleware\PreventBackHistory;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([PreventBackHistory::class]);
        $middleware->alias([
            'ensureUserIsVerified' => EnsureUserIsVerified::class,
            'ensureProfileCompleted' => EnsureProfileComplete::class,
            'checkIsAdmin' => checkIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
