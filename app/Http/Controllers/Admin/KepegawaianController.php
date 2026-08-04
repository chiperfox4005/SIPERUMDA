<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\SubBagian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class KepegawaianController extends Controller
{
    // ==========================================
    // 1. MANAJEMEN BAGIAN
    // ==========================================
    public function bagianIndex()
    {
        $bagians = Bagian::withCount('subBagians')->latest()->paginate(10);
        return view('admin.kepegawaian.bagian.index', compact('bagians'));
    }

    public function bagianCreate()
    {
        return view('admin.kepegawaian.bagian.create');
    }

    public function bagianStore(Request $request)
    {
        $validated = $request->validate([
            'nama_bagian' => 'required|string|max:255|unique:bagians,nama_bagian',
        ]);
        
        Bagian::create($validated);
        return redirect()->route('kepegawaian.bagian.index')->with('success', 'Bagian berhasil ditambahkan.');
    }

    public function bagianEdit(Bagian $bagian)
    {
        return view('admin.kepegawaian.bagian.edit', compact('bagian'));
    }

    public function bagianUpdate(Request $request, Bagian $bagian)
    {
        $validated = $request->validate([
            'nama_bagian' => 'required|string|max:255|unique:bagians,nama_bagian,' . $bagian->id,
        ]);
        
        $bagian->update($validated);
        return redirect()->route('kepegawaian.bagian.index')->with('success', 'Bagian berhasil diperbarui.');
    }

    public function bagianDestroy(Bagian $bagian)
    {
        $bagian->delete();
        return redirect()->route('kepegawaian.bagian.index')->with('success', 'Bagian berhasil dihapus.');
    }

    // ==========================================
    // 2. MANAJEMEN SUB BAGIAN
    // ==========================================
    public function subBagianIndex()
    {
        $subBagians = SubBagian::with('bagian')->latest()->paginate(10);
        $bagians = Bagian::all();
        return view('admin.kepegawaian.sub-bagian.index', compact('subBagians', 'bagians'));
    }

    public function subBagianCreate()
    {
        $bagians = Bagian::orderBy('nama_bagian')->get();
        return view('admin.kepegawaian.sub-bagian.create', compact('bagians'));
    }

    public function subBagianStore(Request $request)
    {
        $validated = $request->validate([
            'bagian_id' => 'required|exists:bagians,id',
            'nama_sub_bagian' => 'required|string|max:255',
        ]);
        SubBagian::create($validated);
        return redirect()->route('kepegawaian.sub-bagian.index')->with('success', 'Sub Bagian berhasil ditambahkan.');
    }

    public function subBagianEdit(SubBagian $subBagian)
    {
        $bagians = Bagian::orderBy('nama_bagian')->get();
        return view('admin.kepegawaian.sub-bagian.edit', compact('subBagian', 'bagians'));
    }

    public function subBagianUpdate(Request $request, SubBagian $subBagian)
    {
        $validated = $request->validate([
            'bagian_id' => 'required|exists:bagians,id',
            'nama_sub_bagian' => 'required|string|max:255',
        ]);
        $subBagian->update($validated);
        return redirect()->route('kepegawaian.sub-bagian.index')->with('success', 'Sub Bagian berhasil diperbarui.');
    }

    public function subBagianDestroy(SubBagian $subBagian)
    {
        $subBagian->delete();
        return redirect()->route('kepegawaian.sub-bagian.index')->with('success', 'Sub Bagian berhasil dihapus.');
    }

    // ==========================================
    // 3. MANAJEMEN JABATAN (Placeholder)
    // ==========================================
    public function jabatanIndex() { return view('admin.kepegawaian.jabatan.index'); }
    public function jabatanCreate() { return view('admin.kepegawaian.jabatan.create'); }
    public function jabatanStore(Request $request) { return redirect()->back()->with('success', 'Fitur jabatan akan segera tersedia.'); }
    public function jabatanEdit($id) { return view('admin.kepegawaian.jabatan.edit'); }
    public function jabatanUpdate(Request $request, $id) { return redirect()->back()->with('success', 'Fitur jabatan akan segera tersedia.'); }
    public function jabatanDestroy($id) { return redirect()->back()->with('success', 'Fitur jabatan akan segera tersedia.'); }

    // ==========================================
    // 4. MANAJEMEN PEGAWAI (Dengan Opsi Password)
    // ==========================================
    public function pegawaiIndex(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page') == '9999' ? 9999 : 10;
        
        $users = User::with(['bagian', 'subBagian', 'roles'])
            ->when($search, function ($query, $search) {
                $query->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage);

        return view('admin.kepegawaian.pegawai.index', compact('users', 'search', 'perPage'));
    }

    public function pegawaiCreate()
    {
        $bagians = Bagian::orderBy('nama_bagian')->get();
        $roles = Role::all();
        return view('admin.kepegawaian.pegawai.create', compact('bagians', 'roles'));
    }

    public function pegawaiStore(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required|string|unique:users,nip',
            'nama_lengkap' => 'required|string|max:255',
            'bagian_id' => 'required|exists:bagians,id',
            'sub_bagian_id' => 'required|exists:sub_bagians,id',
            'role' => 'required|string|exists:roles,name',
            'password' => 'nullable|string|min:6', // Password opsional
        ]);

        // Jika password diisi, gunakan itu. Jika kosong, gunakan NIP.
        $passwordToHash = !empty($validated['password']) ? $validated['password'] : $validated['nip'];

        $user = User::create([
            'nip' => $validated['nip'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'bagian_id' => $validated['bagian_id'],
            'sub_bagian_id' => $validated['sub_bagian_id'],
            'password' => Hash::make($passwordToHash),
            'status' => 'aktif',
        ]);

        $user->assignRole($validated['role']);

        $msg = !empty($validated['password']) 
            ? 'Pegawai berhasil ditambahkan dengan password kustom.' 
            : 'Pegawai berhasil ditambahkan. Password default adalah NIP.';

        return redirect()->route('kepegawaian.pegawai.index')->with('success', $msg);
    }

    public function pegawaiEdit(User $user)
    {
        $bagians = Bagian::orderBy('nama_bagian')->get();
        $subBagians = SubBagian::where('bagian_id', $user->bagian_id)->get();
        $roles = Role::all();
        
        return view('admin.kepegawaian.pegawai.edit', compact('user', 'bagians', 'subBagians', 'roles'));
    }

    public function pegawaiUpdate(Request $request, User $user)
    {
        $validated = $request->validate([
            'nip' => 'required|string|unique:users,nip,' . $user->id,
            'nama_lengkap' => 'required|string|max:255',
            'bagian_id' => 'required|exists:bagians,id',
            'sub_bagian_id' => 'required|exists:sub_bagians,id',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user->update([
            'nip' => $validated['nip'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'bagian_id' => $validated['bagian_id'],
            'sub_bagian_id' => $validated['sub_bagian_id'],
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('kepegawaian.pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function pegawaiDestroy(User $user)
    {
        $user->delete();
        return redirect()->route('kepegawaian.pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
    }

    public function pegawaiResetPassword(User $user)
    {
        $user->update(['password' => Hash::make($user->nip)]);
        return redirect()->route('kepegawaian.pegawai.index')->with('success', 'Password berhasil direset ke NIP: ' . $user->nip);
    }

    public function pegawaiUpdateStatus(User $user)
    {
        $newStatus = $user->status === 'aktif' ? 'nonaktif' : 'aktif';
        $user->update(['status' => $newStatus]);
        return redirect()->route('kepegawaian.pegawai.index')->with('success', "Status pegawai diubah menjadi {$newStatus}.");
    }
}