<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nip' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Coba login menggunakan 'nip' dan 'password'
        if (! Auth::attempt($request->only('nip', 'password'), $request->boolean('remember'))) {
            return back()->withErrors([
                'nip' => 'NIP atau Password yang Anda masukkan salah.',
            ])->withInput($request->only('nip'));
        }

        $request->session()->regenerate();

        // ✅ PERBAIKAN: Cek role Direksi untuk redirect otomatis ke Dashboard Eksekutif
        $user = Auth::user();
        if ($user->hasRole('Direksi')) {
            return redirect()->intended(route('direksi.dashboard', absolute: false));
        }

        // Redirect default untuk user biasa/admin/kepegawaian
        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}