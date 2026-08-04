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
    // 1. RIWAYAT PEMOHON
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
            'signatories' => Signatory::where('is_active', true)->orderBy('name')->get(),
        ];
        return view('submissions.buat', compact('template', 'options'));
    }

    // 2. SIMPAN PENGAJUAN (PEMOHON)
    public function store(Request $request)
    {
        // Validasi dasar (signatory_id DIHAPUS dari sini karena dipilih oleh Sekretariat nanti)
        $request->validate([
            'template_id' => 'required|exists:document_templates,id',
        ]);
        
        $template = DocumentTemplate::findOrFail($request->template_id);
        $fields = $template->form_schema['fields'] ?? [];

        // Bangun validasi dinamis berdasarkan schema
        $rules = [
            'template_id' => 'required|exists:document_templates,id',
        ];

        foreach ($fields as $field) {
            if (!empty($field['name'])) {
                $rule = (!empty($field['required']) ? 'required' : 'nullable') . '|string';
                $rules[$field['name']] = $rule;
            }
        }

        // Jika validasi gagal, Laravel otomatis redirect back dengan error
        $validated = $request->validate($rules);

        // Ambil semua data kecuali field kontrol Laravel
        $dataJson = $request->except(['_token', '_method', 'template_id', 'signatory_id']);

        // SIMPAN DENGAN STATUS 'submitted' 
        // signatory_id akan bernilai NULL, nanti diisi oleh Sekretariat saat Approval
        $submission = DocumentSubmission::create([
            'user_id' => (string) auth()->user()->nip,
            'template_id' => $validated['template_id'],
            'data_json' => json_encode($dataJson),
            'status' => 'submitted', 
        ]);

        // ✅ KIRIM NOTIFIKASI KE SEMUA SEKRETARIAT / ADMIN
        $secretaries = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Sekretariat', 'IT Administrator', 'Administrator']);
        })->get();

        foreach ($secretaries as $secretary) {
            $secretary->notify(new DocumentSubmittedNotification($submission));
        }

        // ✅ Redirect dengan pesan sukses yang JELAS
        return redirect()->route('surat.index')
            ->with('success', '✅ Pengajuan surat BERHASIL dikirim! Notifikasi telah dikirim ke Sekretariat.');
    }

    public function show(DocumentSubmission $submission)
    {
        // ✅ Muat relasi agar tidak error saat memanggil $submission->template atau $submission->creator
        $submission->load(['template', 'creator', 'signatory']);
        
        $userNip = (string) auth()->user()->nip;
        $isAdmin = auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']);
        abort_unless($submission->user_id === $userNip || $isAdmin, 403);
        
        $dataJson = is_string($submission->data_json) ? json_decode($submission->data_json, true) : ($submission->data_json ?? []);
        
        // ✅ Pastikan nama variabel yang dikirim adalah 'submission', BUKAN 'surat'
        return view('submissions.show', compact('submission', 'dataJson'));
    }

    // 3. HALAMAN VERIFIKASI (SEKRETARIAT)
    public function approval()
    {
        abort_unless(auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']), 403);
        
        // ✅ PERBAIKAN: Ambil SEMUA status (submitted, approved, rejected) 
        // agar sekretaris bisa melihat hasil verifikasi secara lengkap
        $submissions = DocumentSubmission::with(['creator', 'template', 'signatory'])
            ->whereIn('status', ['submitted', 'approved', 'rejected'])
            ->latest()
            ->get();

        return view('submissions.approval', compact('submissions'));
    }

    // 4. PROSES APPROVE & GENERATE PDF (SEKRETARIAT)
    // ✅ Di sinilah Sekretariat WAJIB memilih signatory_id
    public function approve(Request $request, DocumentSubmission $submission)
    {
        abort_unless(auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']), 403);

        $validated = $request->validate([
            'nomor_surat' => 'required|string|unique:document_submissions,nomor_surat,' . $submission->id,
            'signatory_id' => 'required|exists:signatories,id', // WAJIB di sini
        ]);

        // ✅ PASTIKAN UPDATE INI BERJALAN
        $submission->update([
            'status' => 'approved',
            'nomor_surat' => $validated['nomor_surat'],
            'signatory_id' => $validated['signatory_id'], // Disimpan di sini
            'approved_at' => now(),
            'approved_by' => (string) auth()->user()->nip,
        ]);

        $signatory = Signatory::find($validated['signatory_id']);
        $dataJson = is_string($submission->data_json) ? json_decode($submission->data_json, true) : ($submission->data_json ?? []);
        
        $data = [
            'submission' => $submission,
            'signatory' => $signatory,
            'data' => $dataJson,
            'tanggalCetak' => Carbon::now()->locale('id')->isoFormat('D MMMM Y'),
        ];

        $slug = $submission->template->slug ?? 'undangan-rapat';
        $viewPath = 'templates.surat.' . str_replace('-', '_', $slug);
        if (!view()->exists($viewPath)) {
            $viewPath = 'templates.surat.undangan-rapat';
        }

        $pdf = Pdf::loadView($viewPath, $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);

        $fileName = str_replace('/', '-', $validated['nomor_surat']) . '.pdf';
        $pdfPath = 'surat_pdf/' . $fileName;
        Storage::disk('public')->put($pdfPath, $pdf->output());

        $submission->update(['pdf_path' => $pdfPath]);

        // ✅ KIRIM NOTIFIKASI KE PEMOHON
        $pemohon = User::where('nip', $submission->user_id)->first();
        if ($pemohon) {
            $pemohon->notify(new DocumentApprovedNotification($submission));
        }

        return redirect()->route('surat.approval')->with('success', '✅ Surat BERHASIL diverifikasi, PDF digenerate, dan notifikasi dikirim ke pemohon!');
    }

    public function reject(Request $request, DocumentSubmission $submission)
    {
        abort_unless(auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']), 403);
        
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $submission->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_at' => now(),
            'approved_by' => (string) auth()->user()->nip,
        ]);

        // ✅ KIRIM NOTIFIKASI KE PEMOHON
        $pemohon = User::where('nip', $submission->user_id)->first();
        if ($pemohon) {
            $pemohon->notify(new DocumentRejectedNotification($submission));
        }

        return redirect()->route('surat.approval')->with('success', 'Surat telah ditolak dan notifikasi dikirim ke pemohon.');
    }

    public function generateNomorSurat()
    {
        $tahun = Carbon::now()->format('Y');
        $bulanRomawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][Carbon::now()->format('n') - 1];
        $count = DocumentSubmission::whereYear('created_at', $tahun)->whereMonth('created_at', Carbon::now()->format('m'))->count() + 1;
        
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