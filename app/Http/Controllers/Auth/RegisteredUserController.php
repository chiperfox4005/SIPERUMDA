<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Bagian;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $bagians = Bagian::all();
        return view('auth.register', compact('bagians'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'nip' => ['required', 'string', 'max:50', 'unique:'.User::class],
            'bagian_id' => ['required', 'exists:bagians,id'],
            'sub_bagian_id' => ['required', 'exists:sub_bagians,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 1. Buat User Baru
        $user = User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'nip' => $request->nip,
            'bagian_id' => $request->bagian_id,
            'sub_bagian_id' => $request->sub_bagian_id,
            'password' => Hash::make($request->password),
        ]);

        // 2. LOGIKA OTOMATISASI ROLE BERDASARKAN BAGIAN
        $bagian = Bagian::find($request->bagian_id);
        
        // Cek apakah nama bagian mengandung kata "sekretariat" (case-insensitive)
        if ($bagian && str_contains(strtolower($bagian->nama_bagian), 'sekretariat')) {
            // Otomatis berikan role Sekretariat
            $user->assignRole('Sekretariat');
        } else {
            // Berikan role default untuk pegawai biasa (pastikan role 'Pegawai' sudah ada di database)
            // Jika belum ada, Anda bisa membuat migration seeder untuk role 'Pegawai', atau biarkan kosong jika tidak pakai role default.
            $user->assignRole('Pegawai'); 
        }

        // 3. Login user dan redirect
        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}