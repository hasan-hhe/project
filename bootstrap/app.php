<?php

use App\Http\Middleware\Active;
use App\Http\Middleware\Admin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: '/api',
        channels: __DIR__ . '/../routes/channels.php',
        then: function () {
            Route::post('/broadcasting/auth', function (Request $request) {
                return Broadcast::auth($request);
            })->middleware('auth:sanctum');
        },
    )

    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('admin', [
            Admin::class
        ]);

        $middleware->prependToGroup('admin', [
            Admin::class
        ]);

        $middleware->appendToGroup('active', [
            Active::class
        ]);

        $middleware->prependToGroup('active', [
            Active::class
        ]);

        // إضافة middleware للـ broadcasting authentication لاستخدام Sanctum
        $middleware->validateCsrfTokens(except: [
            'broadcasting/auth',
        ]);
    })
    ->withBroadcasting(
        __DIR__ . '/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['auth:sanctum']],
    )
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
