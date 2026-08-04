<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Bagian;
use App\Models\SubBagian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Tampilkan daftar pengguna dengan fitur pencarian dan Show All.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        // Logika Show All: jika per_page = 9999, tampilkan semua
        $perPage = $request->input('per_page') == '9999' ? 9999 : 10;
        
        $users = User::with(['bagian', 'subBagian', 'roles'])
            ->when($search, function ($query, $search) {
                $query->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage);

        return view('admin.users.index', compact('users', 'search', 'perPage'));
    }

    /**
     * Tampilkan form tambah pengguna.
     */
    public function create()
    {
        $bagians = Bagian::orderBy('nama_bagian')->get();
        $roles = Role::all();
        return view('admin.users.create', compact('bagians', 'roles'));
    }

    /**
     * Simpan pengguna baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required|string|unique:users,nip',
            'nama_lengkap' => 'required|string|max:255',
            'bagian_id' => 'required|exists:bagians,id',
            'sub_bagian_id' => 'required|exists:sub_bagians,id',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            'nip' => $validated['nip'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'bagian_id' => $validated['bagian_id'],
            'sub_bagian_id' => $validated['sub_bagian_id'],
            'password' => Hash::make($validated['nip']), // Password default = NIP
            'status' => 'aktif',
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.users')->with('success', 'Pengguna berhasil ditambahkan. Password default adalah NIP.');
    }

    /**
     * Tampilkan form edit pengguna.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update data pengguna (HANYA MENGIZINKAN UPDATE NAMA DAN NIP).
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nip' => 'required|string|unique:users,nip,' . $user->id,
            'nama_lengkap' => 'required|string|max:255',
        ]);

        $user->update([
            'nip' => $validated['nip'],
            'nama_lengkap' => $validated['nama_lengkap'],
        ]);

        return redirect()->route('admin.users')->with('success', 'NIP dan Nama berhasil diperbarui.');
    }

    /**
     * Hapus pengguna.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Reset password pengguna ke NIP-nya.
     */
    public function resetPassword(User $user)
    {
        $user->update([
            'password' => Hash::make($user->nip)
        ]);

        return redirect()->route('admin.users')->with('success', 'Password berhasil direset ke NIP: ' . $user->nip);
    }

    /**
     * Toggle status aktif/nonaktif pengguna.
     */
    public function updateStatus(User $user)
    {
        $newStatus = $user->status === 'aktif' ? 'nonaktif' : 'aktif';
        $user->update(['status' => $newStatus]);

        return redirect()->route('admin.users')->with('success', "Status pengguna diubah menjadi {$newStatus}.");
    }
}