<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// 1. IMPORT MIDDLEWARE DARI SPATIE
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

// 2. IMPORT MIDDLEWARE KUSTOM UNTUK CEK ADMINISTRATOR OTOMATIS
use App\Http\Middleware\IsAdministrator;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 3. DAFTARKAN ALIAS MIDDLEWARE DI SINI
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            
            // Middleware kustom untuk cek otomatis Bidang Litbang + Sub Bidang PTI
            'is_admin' => IsAdministrator::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();