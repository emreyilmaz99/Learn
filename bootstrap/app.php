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
    ->withMiddleware(function (Middleware $middleware): void {
       //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Unauthenticated (token yok/geçersiz) - JSON response
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'statusCode' => 401,
                    'success' => false,
                    'message' => 'Geçersiz veya süresi dolmuş token',
                ], 401);
            }
        });
    })->create();
