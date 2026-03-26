<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{Exceptions, Middleware};
use App\Http\Middleware\{EnsureProfileComplete, EnsureUserIsVerified};

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . "/../routes/web.php",
        commands: __DIR__ . "/../routes/console.php",
        health: "/up",
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([App\Http\Middleware\PreventBackHistory::class]);
        $middleware->alias([
            "ensureUserIsVerified" => EnsureUserIsVerified::class,
            "ensureProfileCompleted" => EnsureProfileComplete::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();