<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use Illuminate\Http\Request;

class DocumentTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:Administrator|IT Administrator']);
    }

    public function index()
    {
        $templates = DocumentTemplate::latest()->paginate(10);
        return view('admin.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:document_templates,code',
            'blade_view_path' => 'required|string',
            'form_schema' => 'required|json',
        ]);

        DocumentTemplate::create($validated);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template berhasil dibuat');
    }

    public function edit(DocumentTemplate $template)
    {
        return view('admin.templates.edit', compact('template'));
    }

    public function update(Request $request, DocumentTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:document_templates,code,' . $template->id,
            'blade_view_path' => 'required|string',
            'form_schema' => 'required|json',
            'is_active' => 'boolean',
        ]);

        $template->update($validated);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template berhasil diperbarui');
    }

    public function destroy(DocumentTemplate $template)
    {
        $template->delete();
        return redirect()->route('admin.templates.index')
            ->with('success', 'Template dihapus');
    }
}