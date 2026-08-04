<?php

namespace App\Http\Controllers;

use App\Models\PeminjamanRuangan;
use App\Models\Ruangan;
use App\Models\User;
use App\Notifications\StatusPeminjamanRuanganNotification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class PeminjamanRuanganController extends Controller 
{
    /**
     * Menampilkan daftar peminjaman.
     */
    public function index()
    {
        $user = auth()->user();
        
        if ($user->hasRole(['Sekretariat', 'IT Administrator', 'Administrator'])) {
            $peminjamans = PeminjamanRuangan::with(['ruangan', 'pemohon', 'agenda'])
                ->latest()
                ->paginate(10);
        } else {
            $peminjamans = PeminjamanRuangan::with(['ruangan', 'agenda'])
                ->where('user_id', (string) $user->nip)
                ->latest()
                ->paginate(10);
        }

        return view('peminjaman-ruangan.index', compact('peminjamans'));
    }

    /**
     * KHUSUS SEKRETARIAT: Menampilkan antrian permohonan dengan konteks lengkap
     */
    public function approval(): View
    {
        abort_unless(
            auth()->user()->hasRole('Sekretariat') || auth()->user()->hasRole('IT Administrator'), 
            403, 
            'Akses ditolak.'
        );

        $permohonanMenunggu = PeminjamanRuangan::with(['ruangan', 'pemohon.bagian', 'pemohon.subBagian', 'agenda'])
            ->where('status_persetujuan', 'menunggu')
            ->orderBy('created_at', 'asc')
            ->get();

        $permohonanMenunggu->transform(function($peminjaman) {
            $konflik = PeminjamanRuangan::where('ruangan_id', $peminjaman->ruangan_id)
                ->where('tanggal_pemakaian', $peminjaman->tanggal_pemakaian)
                ->where('id', '!=', $peminjaman->id)
                ->whereIn('status_persetujuan', ['menunggu', 'disetujui']) 
                ->where(function($q) use ($peminjaman) {
                    $q->where('waktu_mulai', '<', $peminjaman->waktu_selesai)
                      ->where('waktu_selesai', '>', $peminjaman->waktu_mulai);
                })
                ->with(['pemohon.bagian'])
                ->get();

            $jadwalSekitar = PeminjamanRuangan::where('ruangan_id', $peminjaman->ruangan_id)
                ->where('tanggal_pemakaian', $peminjaman->tanggal_pemakaian)
                ->where('id', '!=', $peminjaman->id)
                ->whereIn('status_persetujuan', ['menunggu', 'disetujui'])
                ->orderBy('waktu_mulai', 'asc')
                ->with(['pemohon.bagian'])
                ->get();

            $peminjaman->konflik = $konflik;
            $peminjaman->jadwal_sekitar = $jadwalSekitar;

            return $peminjaman;
        });

        $disetujuiHariIni = PeminjamanRuangan::whereDate('updated_at', today())
            ->where('status_persetujuan', 'disetujui')
            ->count();

        return view('peminjaman-ruangan.approval', compact('permohonanMenunggu', 'disetujuiHariIni'));
    }

    /**
     * Reschedule permohonan (Sekretariat menawarkan jadwal alternatif) - LOGIKA LAMA DIPERTAHANKAN
     */
    public function reschedule(Request $request, PeminjamanRuangan $peminjamanRuangan)
    {
        abort_unless(
            auth()->user()->hasRole('Sekretariat') || auth()->user()->hasRole('IT Administrator'), 
            403
        );

        $validated = $request->validate([
            'tanggal_baru' => 'required|date',
            'waktu_mulai_baru' => 'required',
            'waktu_selesai_baru' => 'required|after:waktu_mulai_baru',
            'alasan_reschedule' => 'required|string',
        ]);

        $isAvailable = !PeminjamanRuangan::where('ruangan_id', $peminjamanRuangan->ruangan_id)
            ->where('tanggal_pemakaian', $validated['tanggal_baru'])
            ->where('status_persetujuan', 'disetujui')
            ->where(function($q) use ($validated) {
                $q->whereBetween('waktu_mulai', [$validated['waktu_mulai_baru'], $validated['waktu_selesai_baru']])
                  ->orWhereBetween('waktu_selesai', [$validated['waktu_mulai_baru'], $validated['waktu_selesai_baru']]);
            })
            ->exists();

        if (!$isAvailable) {
            return back()->with('error', 'Ruangan sudah dipesan pada jadwal yang diusulkan.');
        }

        $peminjamanRuangan->update([
            'tanggal_pemakaian' => $validated['tanggal_baru'],
            'waktu_mulai' => $validated['waktu_mulai_baru'],
            'waktu_selesai' => $validated['waktu_selesai_baru'],
            'catatan_penolakan' => $validated['alasan_reschedule'] . "\n[Jadwal diusulkan ulang oleh Sekretariat]",
            'status_persetujuan' => 'menunggu_konfirmasi',
        ]);

        return back()->with('success', 'Jadwal alternatif telah diusulkan ke pemohon.');
    }

    /**
     * Menampilkan kalender jadwal peminjaman ruangan.
     */
    public function kalender(): View
    {
        $kalenderRuangan = PeminjamanRuangan::with(['ruangan', 'agenda', 'pemohon'])
            ->whereMonth('tanggal_pemakaian', now()->month)
            ->whereYear('tanggal_pemakaian', now()->year)
            ->orderBy('tanggal_pemakaian', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->get()
            ->groupBy(function($item) {
                return \Carbon\Carbon::parse($item->tanggal_pemakaian)->format('Y-m-d');
            });

        return view('peminjaman-ruangan.kalender', compact('kalenderRuangan'));
    }

    public function create() 
    {
        $ruangans = Ruangan::where('status', 'aktif')->orderBy('nama_ruangan')->get();
        return view('peminjaman-ruangan.create', compact('ruangans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ruangan_id' => 'required|exists:ruangans,id',
            'agenda_id' => 'nullable|exists:agendas,id',
            'tanggal_pemakaian' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required|after:waktu_mulai',
            'keperluan' => 'required|string',
            'jumlah_peserta' => 'required|integer|min:1',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:2048',
        ]);

        $user = auth()->user();

        if ($request->hasFile('lampiran')) {
            $validated['lampiran'] = $request->file('lampiran')->store('peminjaman_ruangan', 'public');
        }

        PeminjamanRuangan::create([
            'ruangan_id' => $validated['ruangan_id'],
            'agenda_id' => $validated['agenda_id'] ?? null,
            'user_id' => (string) $user->nip,
            'tanggal_pemakaian' => $validated['tanggal_pemakaian'],
            'waktu_mulai' => $validated['waktu_mulai'],
            'waktu_selesai' => $validated['waktu_selesai'],
            'keperluan' => $validated['keperluan'],
            'jumlah_peserta' => $validated['jumlah_peserta'],
            'lampiran' => $validated['lampiran'] ?? null,
            'status_persetujuan' => 'menunggu',
            'status_peminjaman' => 'menunggu',
            'disetujui_oleh' => null,
            'catatan_penolakan' => null,
        ]);

        return redirect()->route('peminjaman-ruangan.index')
            ->with('success', 'Permohonan peminjaman berhasil diajukan! Menunggu persetujuan Sekretariat.');
    }

    public function show(PeminjamanRuangan $peminjamanRuangan): View
    {
        $peminjamanRuangan->load(['ruangan', 'pemohon.bagian', 'agenda']);
        return view('peminjaman-ruangan.show', compact('peminjamanRuangan'));
    }

    public function edit(PeminjamanRuangan $peminjamanRuangan): View
    {
        abort_if((string) auth()->user()->nip !== $peminjamanRuangan->user_id, 403);
        abort_if($peminjamanRuangan->status_persetujuan !== 'menunggu', 403, 'Pengajuan yang sudah diproses tidak dapat diubah.');
        
        $ruangans = Ruangan::where('status', 'aktif')->get();
        return view('peminjaman-ruangan.edit', compact('peminjamanRuangan', 'ruangans'));
    }

    public function update(Request $request, PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        abort_if((string) auth()->user()->nip !== $peminjamanRuangan->user_id, 403);
        abort_if($peminjamanRuangan->status_persetujuan !== 'menunggu', 403);
        
        $validated = $request->validate([
            'keperluan' => 'required|string|max:500',
            'jumlah_peserta' => 'required|integer|min:1',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('lampiran')) {
            if ($peminjamanRuangan->lampiran && Storage::disk('public')->exists($peminjamanRuangan->lampiran)) {
                Storage::disk('public')->delete($peminjamanRuangan->lampiran);
            }
            $validated['lampiran'] = $request->file('lampiran')->store('peminjaman_ruangan', 'public');
        }

        $peminjamanRuangan->update($validated);
        return redirect()->route('peminjaman-ruangan.index')->with('success', 'Data peminjaman berhasil diperbarui.');
    }

    public function destroy(PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        abort_if((string) auth()->user()->nip !== $peminjamanRuangan->user_id, 403);
        abort_if($peminjamanRuangan->status_persetujuan !== 'menunggu', 403, 'Pengajuan yang sudah diproses tidak dapat dibatalkan.');
        
        if ($peminjamanRuangan->lampiran && Storage::disk('public')->exists($peminjamanRuangan->lampiran)) {
            Storage::disk('public')->delete($peminjamanRuangan->lampiran);
        }

        $peminjamanRuangan->delete();
        return redirect()->route('peminjaman-ruangan.index')->with('success', 'Pengajuan peminjaman berhasil dibatalkan.');
    }

    // =================================================================
    // METHOD KHUSUS SEKRETARIAT / ADMIN (VERIFIKASI)
    // =================================================================

    public function approve(PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('Sekretariat') || auth()->user()->hasRole('IT Administrator'), 403);

        $peminjamanRuangan->update([
            'status_persetujuan' => 'disetujui',
            'status_peminjaman' => 'disetujui',
            'disetujui_oleh' => (string) auth()->user()->nip,
            'tanggal_disetujui' => now(),
        ]);

        if ($peminjamanRuangan->pemohon) {
            $peminjamanRuangan->pemohon->notify(new StatusPeminjamanRuanganNotification(
                $peminjamanRuangan, 
                'disetujui', 
                'Permohonan peminjaman ruangan Anda telah disetujui oleh Sekretariat.'
            ));
        }

        return redirect()->back()->with('success', 'Peminjaman ruangan telah disetujui.');
    }

    public function reject(Request $request, PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('Sekretariat') || auth()->user()->hasRole('IT Administrator'), 403);

        $request->validate(['catatan_penolakan' => 'required|string|max:500']);

        $peminjamanRuangan->update([
            'status_persetujuan' => 'ditolak',
            'status_peminjaman' => 'ditolak',
            'catatan_penolakan' => $request->catatan_penolakan,
            'ditolak_oleh' => (string) auth()->user()->nip,
            'tanggal_ditolak' => now(),
        ]);

        if ($peminjamanRuangan->pemohon) {
            $peminjamanRuangan->pemohon->notify(new StatusPeminjamanRuanganNotification(
                $peminjamanRuangan, 
                'ditolak', 
                'Permohonan Anda ditolak. Alasan: ' . $request->catatan_penolakan
            ));
        }

        return redirect()->back()->with('success', 'Peminjaman ruangan telah ditolak.');
    }

    public function cancel(PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        abort_if((string) auth()->user()->nip !== $peminjamanRuangan->user_id, 403);
        abort_if($peminjamanRuangan->status_persetujuan !== 'menunggu', 403);

        $peminjamanRuangan->update(['status_persetujuan' => 'dibatalkan']);
        return redirect()->back()->with('success', 'Pengajuan peminjaman dibatalkan.');
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'ruangan_id' => 'required|exists:ruangans,id',
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
        ]);

        $isAvailable = !PeminjamanRuangan::where('ruangan_id', $request->ruangan_id)
            ->where('tanggal_pemakaian', $request->tanggal)
            ->where('status_persetujuan', 'disetujui')
            ->where(function($q) use ($request) {
                $q->whereBetween('waktu_mulai', [$request->waktu_mulai, $request->waktu_selesai])
                  ->orWhereBetween('waktu_selesai', [$request->waktu_mulai, $request->waktu_selesai])
                  ->orWhere(function($q2) use ($request) {
                      $q2->where('waktu_mulai', '<=', $request->waktu_mulai)
                         ->where('waktu_selesai', '>=', $request->waktu_selesai);
                  });
            })
            ->exists();

        return response()->json([
            'available' => $isAvailable,
            'message' => $isAvailable ? 'Ruangan tersedia pada waktu tersebut.' : 'Ruangan sudah dipesan pada waktu tersebut.'
        ]);
    }

    public function revoke(Request $request, PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(['Sekretariat', 'IT Administrator']), 403);
        abort_if($peminjamanRuangan->status_persetujuan !== 'disetujui', 403, 'Hanya peminjaman yang sudah disetujui yang dapat dibatalkan.');

        $request->validate(['catatan_pembatalan' => 'required|string|max:500']);

        $peminjamanRuangan->update([
            'status_persetujuan' => 'dibatalkan',
            'status_peminjaman' => 'dibatalkan',
            'catatan_pembatalan' => $request->catatan_pembatalan,
            'ditolak_oleh' => (string) auth()->user()->nip,
            'tanggal_ditolak' => now(),
        ]);

        if ($peminjamanRuangan->pemohon) {
            $peminjamanRuangan->pemohon->notify(new StatusPeminjamanRuanganNotification(
                $peminjamanRuangan, 
                'dibatalkan', 
                'Peminjaman yang sudah disetujui dibatalkan. Alasan: ' . $request->catatan_pembatalan
            ));
        }

        return redirect()->back()->with('success', 'Peminjaman berhasil DIBATALKAN.');
    }

    public function confirmReschedule(PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        abort_if((string) auth()->user()->nip !== $peminjamanRuangan->user_id, 403);
        abort_if($peminjamanRuangan->status_persetujuan !== 'menunggu_konfirmasi', 403);

        $peminjamanRuangan->update([
            'status_persetujuan' => 'disetujui',
            'status_peminjaman' => 'disetujui',
            'catatan_penolakan' => $peminjamanRuangan->catatan_penolakan . "\n[Dikonfirmasi oleh pemohon pada " . now()->format('d M Y, H:i') . "]",
        ]);

        return redirect()->route('peminjaman-ruangan.show', $peminjamanRuangan)
            ->with('success', 'Jadwal baru berhasil dikonfirmasi. Peminjaman Anda Disetujui!');
    }

    public function rejectReschedule(PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        abort_if((string) auth()->user()->nip !== $peminjamanRuangan->user_id, 403);
        abort_if($peminjamanRuangan->status_persetujuan !== 'menunggu_konfirmasi', 403);

        $peminjamanRuangan->update([
            'status_persetujuan' => 'menunggu',
            'status_peminjaman' => 'menunggu',
        ]);

        return redirect()->route('peminjaman-ruangan.edit', $peminjamanRuangan)
            ->with('info', 'Jadwal usulan Sekretariat ditolak. Silakan edit dan ajukan kembali.');
    }

    // =================================================================
    // ✅ FITUR BARU: RESCHEDULE LANGSUNG DENGAN CATATAN (BADGE MERAH)
    // =================================================================
    /**
     * Reschedule Langsung dengan Catatan (Mengubah status menjadi Dijadwalkan Ulang / Merah)
     * Tanpa mengubah tanggal, hanya memberi catatan dan mengubah status jadi merah.
     */
    public function rescheduleDenganCatatan(Request $request, PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']), 403);
        abort_if($peminjamanRuangan->status_persetujuan !== 'disetujui', 403, 'Hanya peminjaman yang sudah disetujui yang dapat dijadwalkan ulang.');

        $validated = $request->validate([
            'catatan_reschedule' => 'required|string|max:500',
        ]);

        // ✅ PENTING: Load relasi pemohon agar data pengaju tersedia untuk notifikasi
        $peminjamanRuangan->loadMissing('pemohon');

        $peminjamanRuangan->update([
            'status_persetujuan' => 'dijadwalkan_ulang',
            'status_peminjaman' => 'dijadwalkan_ulang',
            'catatan_penolakan' => '[Reschedule] ' . $validated['catatan_reschedule'],
            'ditolak_oleh' => (string) auth()->user()->nip,
            'tanggal_ditolak' => now(),
        ]);

        if ($peminjamanRuangan->pemohon) {
            $peminjamanRuangan->pemohon->notify(new StatusPeminjamanRuanganNotification(
                $peminjamanRuangan, 
                'dijadwalkan_ulang', 
                'Peminjaman ruangan Anda dijadwalkan ulang/dibatalkan oleh Sekretariat. Catatan: ' . $validated['catatan_reschedule']
            ));
        }

        return redirect()->back()->with('success', 'Peminjaman berhasil dijadwalkan ulang. Pemohon telah diberi tahu.');
    }
}