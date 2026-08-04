<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\PeminjamanRuangan;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function agenda(Request $request)
    {
        $this->authorize('viewAny', Agenda::class);

        $query = Agenda::with(['creator', 'peserta', 'disetujuiOleh']);

        // Filter
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('tanggal_mulai', [
                $request->tanggal_mulai,
                $request->tanggal_selesai
            ]);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('bagian_id')) {
            $query->whereHas('creator', function ($q) use ($request) {
                $q->where('bagian_id', $request->bagian_id);
            });
        }

        $agendas = $query->get();

        if ($request->filled('export') && $request->export === 'pdf') {
            return $this->exportAgendaPdf($agendas, $request);
        }

        $bagians = \App\Models\Bagian::all();
        $kategoriOptions = [
            'rapat_internal' => 'Rapat Internal',
            'rapat_eksternal' => 'Rapat Eksternal',
            'kunjungan_dinas' => 'Kunjungan Dinas',
            'seremoni' => 'Seremoni',
            'lainnya' => 'Lainnya'
        ];
        $statusOptions = [
            'diajukan' => 'Diajukan',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'berlangsung' => 'Berlangsung',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan'
        ];

        return view('laporan.agenda', compact('agendas', 'bagians', 'kategoriOptions', 'statusOptions'));
    }

    public function peminjamanRuangan(Request $request)
    {
        $this->authorize('viewAny', PeminjamanRuangan::class);

        $query = PeminjamanRuangan::with(['ruangan', 'agenda', 'pemohon', 'disetujuiOleh']);

        // Filter
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('tanggal_pemakaian', [
                $request->tanggal_mulai,
                $request->tanggal_selesai
            ]);
        }

        if ($request->filled('status_persetujuan')) {
            $query->where('status_persetujuan', $request->status_persetujuan);
        }

        if ($request->filled('ruangan_id')) {
            $query->where('ruangan_id', $request->ruangan_id);
        }

        $peminjamans = $query->get();

        if ($request->filled('export') && $request->export === 'pdf') {
            return $this->exportPeminjamanPdf($peminjamans, $request);
        }

        $ruangans = Ruangan::where('status', 'aktif')->get();
        $statusOptions = [
            'menunggu' => 'Menunggu',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'dibatalkan' => 'Dibatalkan'
        ];

        return view('laporan.peminjaman-ruangan', compact('peminjamans', 'ruangans', 'statusOptions'));
    }

    public function okupansi(Request $request)
    {
        $this->authorize('viewAny', Ruangan::class);

        $query = Ruangan::withCount(['peminjamanRuangans' => function ($q) use ($request) {
            $q->where('status_persetujuan', 'disetujui');
            
            if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
                $q->whereBetween('tanggal_pemakaian', [
                    $request->tanggal_mulai,
                    $request->tanggal_selesai
                ]);
            }
        }]);

        $ruangans = $query->get();

        if ($request->filled('export') && $request->export === 'pdf') {
            return $this->exportOkupansiPdf($ruangans, $request);
        }

        return view('laporan.okupansi', compact('ruangans'));
    }

    private function exportAgendaPdf($agendas, Request $request)
    {
        $data = [
            'agendas' => $agendas,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'generated_at' => now(),
        ];

        $pdf = Pdf::loadView('laporan.pdf.agenda', $data);
        $filename = 'laporan-agenda-' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    private function exportPeminjamanPdf($peminjamans, Request $request)
    {
        $data = [
            'peminjamans' => $peminjamans,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'generated_at' => now(),
        ];

        $pdf = Pdf::loadView('laporan.pdf.peminjaman-ruangan', $data);
        $filename = 'laporan-peminjaman-ruangan-' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    private function exportOkupansiPdf($ruangans, Request $request)
    {
        $data = [
            'ruangans' => $ruangans,
            'generated_at' => now(),
        ];

        $pdf = Pdf::loadView('laporan.pdf.okupansi', $data);
        $filename = 'laporan-okupansi-ruangan-' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
}