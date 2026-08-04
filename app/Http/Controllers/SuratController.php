<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\Signatory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class SuratController extends Controller
{
    // Daftar jenis surat
    private $jenisSuratOptions = [
        'tugas' => 'Surat Tugas',
        'dinas' => 'Surat Perintah Dinas',
        'izin' => 'Surat Izin',
        'undangan' => 'Surat Undangan',
        'sk' => 'Surat Keputusan',
    ];

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Surat::with(['pembuat', 'penyetuju', 'penandatangan']);

        // Filter berdasarkan role
        if ($user->hasRole(['Sekretariat', 'IT Administrator', 'Administrator', 'Kepegawaian'])) {
            // Admin/Sekre/HR lihat semua
            if ($request->has('filter_status') && $request->filter_status !== 'semua') {
                $query->where('status', $request->filter_status);
            }
        } else {
            // Pegawai biasa hanya lihat surat buatannya sendiri
            $query->where('dibuat_oleh', (string) $user->nip);
        }

        $surats = $query->latest()->paginate(15);

        return view('surat.index', compact('surats'));
    }

    public function create()
    {
        $jenisOptions = $this->jenisSuratOptions;
        $penandatanganList = Signatory::where('is_active', true)->orderBy('name')->get();
        
        // Ambil daftar pegawai untuk penerima
        $pegawaiList = User::with(['bagian', 'subBagian'])
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        return view('surat.create', compact('jenisOptions', 'penandatanganList', 'pegawaiList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_surat' => 'required|in:tugas,dinas,izin,undangan,sk',
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:255',
            'isi_surat' => 'required|string',
            'tujuan' => 'nullable|string|max:255',
            'penerima_nama' => 'nullable|string|max:255',
            'penerima_nip' => 'nullable|string|max:50',
            'penerima_jabatan' => 'nullable|string|max:255',
            'penandatangan_id' => 'nullable|exists:signatories,id',
            'status' => 'required|in:draft,submitted',
        ]);

        $user = auth()->user();
        $validated['dibuat_oleh'] = (string) $user->nip;

        // Generate nomor surat otomatis jika status submitted
        if ($validated['status'] === 'submitted') {
            $validated['nomor_surat'] = $this->generateNomorSurat($validated['jenis_surat'], $validated['tanggal_surat']);
        }

        $surat = Surat::create($validated);

        return redirect()->route('surat.index')
            ->with('success', $validated['status'] === 'submitted' 
                ? 'Surat berhasil diajukan dan menunggu persetujuan!' 
                : 'Surat disimpan sebagai draft.');
    }

    public function show(Surat $surat)
    {
        $user = auth()->user();
        
        // Cek akses: pemilik atau admin/sekre/hr
        $canAccess = $surat->dibuat_oleh === (string) $user->nip 
            || $user->hasRole(['Sekretariat', 'IT Administrator', 'Administrator', 'Kepegawaian']);
        
        abort_unless($canAccess, 403);

        return view('surat.show', compact('surat'));
    }

    public function edit(Surat $surat)
    {
        abort_if($surat->dibuat_oleh !== (string) auth()->user()->nip, 403);
        abort_if($surat->status !== 'draft', 403, 'Surat yang sudah diajukan tidak dapat diedit.');

        $jenisOptions = $this->jenisSuratOptions;
        $penandatanganList = Signatory::where('is_active', true)->orderBy('name')->get();
        $pegawaiList = User::with(['bagian', 'subBagian'])
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        return view('surat.edit', compact('surat', 'jenisOptions', 'penandatanganList', 'pegawaiList'));
    }

    public function update(Request $request, Surat $surat)
    {
        abort_if($surat->dibuat_oleh !== (string) auth()->user()->nip, 403);
        abort_if($surat->status !== 'draft', 403);

        $validated = $request->validate([
            'jenis_surat' => 'required|in:tugas,dinas,izin,undangan,sk',
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:255',
            'isi_surat' => 'required|string',
            'tujuan' => 'nullable|string|max:255',
            'penerima_nama' => 'nullable|string|max:255',
            'penerima_nip' => 'nullable|string|max:50',
            'penerima_jabatan' => 'nullable|string|max:255',
            'penandatangan_id' => 'nullable|exists:signatories,id',
            'status' => 'required|in:draft,submitted',
        ]);

        // Generate nomor surat jika berubah ke submitted
        if ($validated['status'] === 'submitted' && $surat->status === 'draft') {
            $validated['nomor_surat'] = $this->generateNomorSurat($validated['jenis_surat'], $validated['tanggal_surat']);
        }

        $surat->update($validated);

        return redirect()->route('surat.index')
            ->with('success', 'Surat berhasil diperbarui.');
    }

    public function destroy(Surat $surat)
    {
        abort_if($surat->dibuat_oleh !== (string) auth()->user()->nip, 403);
        abort_if($surat->status !== 'draft', 403);

        // Hapus file PDF jika ada
        if ($surat->file_path) {
            Storage::disk('public')->delete($surat->file_path);
        }

        $surat->delete();

        return redirect()->route('surat.index')
            ->with('success', 'Surat berhasil dihapus.');
    }

    // =================================================================
    // APPROVAL (KHUSUS SEKRETARIAT/HR)
    // =================================================================

    public function approval()
    {
        abort_unless(
            auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator', 'Kepegawaian']), 
            403
        );

        $menunggu = Surat::with(['pembuat', 'penyetuju'])
            ->where('status', 'submitted')
            ->latest()
            ->get();

        $riwayat = Surat::with(['pembuat', 'penyetuju'])
            ->whereIn('status', ['approved', 'rejected'])
            ->latest()
            ->limit(20)
            ->get();

        return view('surat.approval', compact('menunggu', 'riwayat'));
    }

    public function approve(Surat $surat)
    {
        abort_unless(
            auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator', 'Kepegawaian']), 
            403
        );

        $surat->update([
            'status' => 'approved',
            'disetujui_oleh' => (string) auth()->user()->nip,
            'tanggal_disetujui' => now(),
        ]);

        return redirect()->back()->with('success', 'Surat telah disetujui.');
    }

    public function reject(Request $request, Surat $surat)
    {
        abort_unless(
            auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator', 'Kepegawaian']), 
            403
        );

        $validated = $request->validate([
            'catatan_penolakan' => 'required|string|max:500',
        ]);

        $surat->update([
            'status' => 'rejected',
            'catatan_penolakan' => $validated['catatan_penolakan'],
            'disetujui_oleh' => (string) auth()->user()->nip,
            'tanggal_disetujui' => now(),
        ]);

        return redirect()->back()->with('success', 'Surat telah ditolak.');
    }

    // =================================================================
    // GENERATE PDF
    // =================================================================

    public function downloadPdf(Surat $surat)
    {
        $user = auth()->user();
        $canAccess = $surat->dibuat_oleh === (string) $user->nip 
            || $user->hasRole(['Sekretariat', 'IT Administrator', 'Administrator', 'Kepegawaian']);
        
        abort_unless($canAccess, 403);
        abort_if($surat->status !== 'approved', 403, 'Hanya surat yang disetujui yang dapat diunduh.');

        $penandatangan = $surat->penandatangan;

        $data = [
            'surat' => $surat,
            'penandatangan' => $penandatangan,
            'tanggalCetak' => now()->locale('id')->isoFormat('D MMMM Y'),
        ];

        $pdf = Pdf::loadView('surat.pdf.template', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);

        $fileName = str_replace('/', '-', $surat->nomor_surat) . '.pdf';

        return $pdf->download($fileName);
    }

    // =================================================================
    // HELPER
    // =================================================================

    private function generateNomorSurat(string $jenis, $tanggal): string
    {
        $kodeJenis = match($jenis) {
            'tugas' => 'ST',
            'dinas' => 'SPD',
            'izin' => 'SI',
            'undangan' => 'SU',
            'sk' => 'SK',
            default => 'SR',
        };

        $tahun = \Carbon\Carbon::parse($tanggal)->format('Y');
        $bulan = \Carbon\Carbon::parse($tanggal)->format('m');

        // Hitung nomor urut surat jenis ini di bulan & tahun yang sama
        $nomorUrut = Surat::where('jenis_surat', $jenis)
            ->whereYear('tanggal_surat', $tahun)
            ->whereMonth('tanggal_surat', $bulan)
            ->count() + 1;

        $nomorUrutStr = str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);

        // Format: 001/ST/HRD/07/2026
        return "{$nomorUrutStr}/{$kodeJenis}/HRD/{$bulan}/{$tahun}";
    }
}