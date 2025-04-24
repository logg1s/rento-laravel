<?php

use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\ForceJsonEncoding;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Application;
use App\Http\Middleware\JsonResponseHeader;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'check.status' => CheckUserStatus::class,
        ]);
        $middleware->api()->append([JsonResponseHeader::class, ForceJsonEncoding::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
    })
    ->create();
