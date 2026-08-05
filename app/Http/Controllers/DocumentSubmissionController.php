<?php

namespace App\Http\Controllers;

use App\Models\DocumentSubmission;
use App\Models\DocumentTemplate;
use App\Models\Signatory;
use App\Models\Bagian;
use App\Models\Ruangan;
use App\Models\User;
use App\Notifications\DocumentApprovedNotification;
use App\Notifications\DocumentSubmittedNotification;
use App\Notifications\DocumentRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DocumentSubmissionController extends Controller
{
    public function index()
    {
        $userNip = (string) auth()->user()->nip;
        $submissions = DocumentSubmission::with(['template', 'signatory'])
            ->where('user_id', $userNip)
            ->latest()
            ->paginate(10);

        return view('submissions.index', compact('submissions'));
    }

    public function pilihTemplate()
    {
        $templates = DocumentTemplate::where('is_active', true)->orderBy('name')->get();
        return view('submissions.pilih-template', compact('templates'));
    }

    public function buatDenganTemplate(DocumentTemplate $template)
    {
        $options = [
            'bagians' => Bagian::with('subBagians')->orderBy('nama_bagian')->get(),
            'ruangans' => Ruangan::where('status', 'aktif')->orderBy('nama_ruangan')->get(),
            'users' => User::where('status', 'aktif')->orderBy('nama_lengkap')->get(),
        ];
        return view('submissions.buat', compact('template', 'options'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:document_templates,id',
        ]);
        
        $template = DocumentTemplate::findOrFail($request->template_id);
        $fields = $template->form_schema['fields'] ?? [];

        $rules = ['template_id' => 'required|exists:document_templates,id'];
        foreach ($fields as $field) {
            if (!empty($field['name'])) {
                $rule = (!empty($field['required']) ? 'required' : 'nullable') . '|string';
                $rules[$field['name']] = $rule;
            }
        }

        $validated = $request->validate($rules);
        $dataJson = $request->except(['_token', '_method', 'template_id', 'signatory_id']);

        $submission = DocumentSubmission::create([
            'user_id' => (string) auth()->user()->nip,
            'template_id' => $validated['template_id'],
            'data_json' => json_encode($dataJson),
            'status' => 'submitted', 
        ]);

        $secretaries = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Sekretariat', 'IT Administrator', 'Administrator']);
        })->get();

        foreach ($secretaries as $secretary) {
            $secretary->notify(new DocumentSubmittedNotification($submission));
        }

        return redirect()->route('surat.index')->with('success', '✅ Pengajuan surat BERHASIL dikirim!');
    }

    public function show(DocumentSubmission $submission)
    {
        $submission->load(['template', 'creator', 'signatory']);
        $userNip = (string) auth()->user()->nip;
        $isAdmin = auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']);
        abort_unless($submission->user_id === $userNip || $isAdmin, 403);
        
        $dataJson = is_string($submission->data_json) ? json_decode($submission->data_json, true) : ($submission->data_json ?? []);
        return view('submissions.show', compact('submission', 'dataJson'));
    }

    public function approval()
    {
        abort_unless(auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']), 403);
        $submissions = DocumentSubmission::with(['creator', 'template', 'signatory'])
            ->whereIn('status', ['submitted', 'approved', 'rejected'])
            ->latest()
            ->get();

        return view('submissions.approval', compact('submissions'));
    }

    public function approve(Request $request, DocumentSubmission $submission)
    {
        abort_unless(auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']), 403);

        $validated = $request->validate([
            'nomor_surat' => 'required|string|unique:document_submissions,nomor_surat,' . $submission->id,
            'signatory_id' => 'required|exists:signatories,id',
        ]);

        $submission->update([
            'status' => 'approved',
            'nomor_surat' => $validated['nomor_surat'],
            'signatory_id' => $validated['signatory_id'],
            'approved_at' => now(),
            'approved_by' => (string) auth()->user()->nip,
        ]);

        $signatory = Signatory::find($validated['signatory_id']);
        $dataJson = is_string($submission->data_json) ? json_decode($submission->data_json, true) : ($submission->data_json ?? []);
        
        // ✅ PERBAIKAN: Gunakan translatedFormat agar bulan Indonesia muncul dengan benar (misal: 4 Agustus 2026)
        $pdfData = [
            'submission' => $submission,
            'signatory' => $signatory,
            'data' => $dataJson,
            'tanggalCetak' => Carbon::now()->locale('id')->translatedFormat('d F Y'),
        ];

        $slug = $submission->template->slug ?? 'undangan-rapat';
        $viewPath = 'templates.surat.' . str_replace('-', '_', $slug);
        if (!view()->exists($viewPath)) {
            $viewPath = 'templates.surat.undangan_rapat';
        }

        $pdf = Pdf::loadView($viewPath, $pdfData);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);

        $fileName = str_replace('/', '-', $validated['nomor_surat']) . '.pdf';
        $pdfPath = 'surat_pdf/' . $fileName;
        
        if (!Storage::disk('public')->exists('surat_pdf')) {
            Storage::disk('public')->makeDirectory('surat_pdf');
        }
        
        Storage::disk('public')->put($pdfPath, $pdf->output());
        $submission->update(['pdf_path' => $pdfPath]);

        $pemohon = User::where('nip', $submission->user_id)->first();
        if ($pemohon) {
            $pemohon->notify(new DocumentApprovedNotification($submission));
        }

        return redirect()->route('surat.approval')->with('success', '✅ Surat BERHASIL diverifikasi & PDF digenerate!');
    }

    public function reject(Request $request, DocumentSubmission $submission)
    {
        abort_unless(auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']), 403);
        
        $validated = $request->validate(['rejection_reason' => 'required|string|max:500']);

        $submission->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_at' => now(),
            'approved_by' => (string) auth()->user()->nip,
        ]);

        $pemohon = User::where('nip', $submission->user_id)->first();
        if ($pemohon) {
            $pemohon->notify(new DocumentRejectedNotification($submission));
        }

        return redirect()->route('surat.approval')->with('success', 'Surat telah ditolak.');
    }

    public function generateNomorSurat()
    {
        $tahun = Carbon::now()->format('Y');
        $bulanRomawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][Carbon::now()->format('n') - 1];
        $count = DocumentSubmission::whereYear('created_at', $tahun)
            ->whereMonth('created_at', Carbon::now()->format('m'))
            ->whereNotNull('nomor_surat')
            ->count() + 1;
        
        return response()->json([
            'nomor_surat' => str_pad($count, 3, '0', STR_PAD_LEFT) . "/SIPERUMDA/{$bulanRomawi}/{$tahun}"
        ]);
    }

    public function download(DocumentSubmission $submission)
    {
        $userNip = (string) auth()->user()->nip;
        $isAdmin = auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']);
        
        abort_unless($submission->user_id === $userNip || $isAdmin, 403);
        abort_if($submission->status !== 'approved' || !$submission->pdf_path, 404, 'PDF belum tersedia.');
        
        return response()->download(storage_path('app/public/' . $submission->pdf_path));
    }
}