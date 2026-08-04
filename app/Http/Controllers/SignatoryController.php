<?php

namespace App\Http\Controllers;

use App\Models\Signatory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SignatoryController extends Controller
{
    // ✅ __construct DIHAPUS karena middleware sudah diatur di routes/web.php

    public function index()
    {
        $signatories = Signatory::orderBy('sort_order')->orderBy('name')->get();
        return view('signatories.index', compact('signatories'));
    }

    public function create()
    {
        return view('signatories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:signatories,nip',
            'signature_image' => 'required|image|mimes:png,jpg,jpeg|max:2048',
            'is_active' => 'nullable|boolean',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('signature_image')) {
            $file = $request->file('signature_image');
            $filename = 'ttd_' . time() . '_' . str_replace(' ', '_', $validated['name']) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('signatures', $filename, 'public');
            $validated['signature_image'] = $path;
        }

        $validated['is_active'] = $request->has('is_active');

        Signatory::create($validated);

        return redirect()->route('signatories.index')
            ->with('success', 'Pejabat penandatangan berhasil ditambahkan.');
    }

    public function show(Signatory $signatory)
    {
        return view('signatories.show', compact('signatory'));
    }

    public function edit(Signatory $signatory)
    {
        return view('signatories.edit', compact('signatory'));
    }

    public function update(Request $request, Signatory $signatory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:signatories,nip,' . $signatory->id,
            'signature_image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'is_active' => 'nullable|boolean',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('signature_image')) {
            if ($signatory->signature_image && Storage::disk('public')->exists($signatory->signature_image)) {
                Storage::disk('public')->delete($signatory->signature_image);
            }

            $file = $request->file('signature_image');
            $filename = 'ttd_' . time() . '_' . str_replace(' ', '_', $validated['name']) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('signatures', $filename, 'public');
            $validated['signature_image'] = $path;
        }

        $validated['is_active'] = $request->has('is_active');

        $signatory->update($validated);

        return redirect()->route('signatories.index')
            ->with('success', 'Data pejabat berhasil diperbarui.');
    }

    public function destroy(Signatory $signatory)
    {
        if ($signatory->signature_image && Storage::disk('public')->exists($signatory->signature_image)) {
            Storage::disk('public')->delete($signatory->signature_image);
        }

        $signatory->delete();

        return redirect()->route('signatories.index')
            ->with('success', 'Pejabat penandatangan berhasil dihapus.');
    }

    public function toggleStatus(Signatory $signatory)
    {
        $signatory->update(['is_active' => !$signatory->is_active]);
        return back()->with('success', 'Status pejabat berhasil diubah.');
    }
}