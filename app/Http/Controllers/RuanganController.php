<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

// PENTING: Harus extends Controller agar method middleware() dan fitur lainnya dikenali
class RuanganController extends Controller 
{
    public function index(): View
    {
        $ruangans = Ruangan::latest()->paginate(10);
        return view('ruangan.index', compact('ruangans'));
    }

    public function create(): View
    {
        return view('ruangan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_ruangan' => 'required|string|max:100',
            'kapasitas' => 'required|integer|min:1',
            'fasilitas' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        Ruangan::create($request->all());

        return redirect()->route('ruangan.index')->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function show(Ruangan $ruangan): View
    {
        return view('ruangan.show', compact('ruangan'));
    }

    public function edit(Ruangan $ruangan): View
    {
        return view('ruangan.edit', compact('ruangan'));
    }

    public function update(Request $request, Ruangan $ruangan): RedirectResponse
    {
        $request->validate([
            'nama_ruangan' => 'required|string|max:100',
            'kapasitas' => 'required|integer|min:1',
            'fasilitas' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $ruangan->update($request->all());

        return redirect()->route('ruangan.index')->with('success', 'Data ruangan berhasil diperbarui.');
    }

    public function destroy(Ruangan $ruangan): RedirectResponse
    {
        $ruangan->delete();
        return redirect()->route('ruangan.index')->with('success', 'Ruangan berhasil dihapus.');
    }
}