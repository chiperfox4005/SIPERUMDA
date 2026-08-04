<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $bagians = \App\Models\Bagian::all();
        
        return view('profile.edit', compact('user', 'bagians'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password_lama' => 'nullable|required_with:password_baru',
            'password_baru' => 'nullable|min:8|confirmed',
        ]);

        // 1. Update Nama
        $user->nama_lengkap = $request->nama_lengkap;

        // 2. Update Foto Profil
        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil) {
                Storage::disk('public')->delete($user->foto_profil);
            }
            $user->foto_profil = $request->file('foto_profil')->store('profil', 'public');
        }

        // 3. Update Password
        if ($request->filled('password_lama')) {
            if (!Hash::check($request->password_lama, $user->password)) {
                return Redirect::route('profile.edit')->withErrors(['password_lama' => 'Password lama tidak sesuai.'])->withInput();
            }
            $user->password = Hash::make($request->password_baru);
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}