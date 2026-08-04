<?php

// app/Http/Middleware/RestrictRole.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Sesuaikan cara cek role berdasarkan sistem Anda:
        // 1. Jika role berupa kolom di tabel users (misal: $user->role):
        $hasRole = in_array($user->role, $roles);

        // 2. ATAU jika menggunakan method hasRole() bawaan package (seperti Spatie):
        // $hasRole = $user->hasAnyRole($roles);

        if (!$hasRole) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}