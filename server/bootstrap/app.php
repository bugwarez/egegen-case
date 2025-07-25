<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Tüm API isteklerini logla
        $middleware->api(prepend: [
            \App\Http\Middleware\RequestLoggerMiddleware::class,
        ]);

        // Bearer token middleware'ını kaydet
        $middleware->alias([
            'bearer.token' => \App\Http\Middleware\BearerTokenMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
