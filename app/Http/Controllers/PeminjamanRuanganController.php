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
use Carbon\Carbon;

class PeminjamanRuanganController extends Controller 
{
    /**
     * ✅ HELPER: Cek bentrok jadwal peminjaman ruangan
     */
    private function cekBentrokJadwal($ruanganId, $tanggal, $jamMulai, $jamSelesai, $excludeId = null)
    {
        if (!$ruanganId) return collect();

        $query = PeminjamanRuangan::where('ruangan_id', $ruanganId)
            ->whereDate('tanggal_pemakaian', Carbon::parse($tanggal)->toDateString())
            ->whereIn('status_persetujuan', ['menunggu', 'disetujui', 'dijadwalkan_ulang']) // Ditambahkan dijadwalkan_ulang agar lebih akurat
            ->where(function($q) use ($jamMulai, $jamSelesai) {
                $q->where('waktu_mulai', '<', $jamSelesai)
                  ->where('waktu_selesai', '>', $jamMulai);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->with(['pemohon', 'ruangan'])->get();
    }

    /**
     * Menampilkan daftar peminjaman.
     */
    public function index()
    {
        $user = auth()->user();
        // ✅ OPTIMALISASI: Fallback ke user->id jika nip kosong (untuk role Admin/IT)
        $userIdentifier = (string) ($user->nip ?? $user->id);
        
        if ($user->hasRole(['Sekretariat', 'IT Administrator', 'Administrator'])) {
            $peminjamans = PeminjamanRuangan::with(['ruangan', 'pemohon', 'agenda'])
                ->latest()
                ->paginate(10);
        } else {
            $peminjamans = PeminjamanRuangan::with(['ruangan', 'agenda', 'pemohon'])
                ->where('user_id', $userIdentifier)
                ->latest()
                ->paginate(10);
        }

        return view('peminjaman-ruangan.index', compact('peminjamans'));
    }

    /**
     * KHUSUS SEKRETARIAT/ADMIN: Menampilkan antrian permohonan dengan konteks lengkap
     */
    public function approval(Request $request)
    {
        abort_unless(auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']), 403);

        $query = PeminjamanRuangan::with(['pemohon', 'ruangan', 'pemohon.bagian', 'pemohon.subBagian', 'agenda']);

        if ($request->filled('status')) {
            $query->where('status_persetujuan', $request->status);
        }
        
        $peminjamanRuangans = $query
            ->orderByRaw("
                CASE 
                    WHEN status_persetujuan = 'menunggu' THEN 0
                    WHEN status_persetujuan = 'dijadwalkan_ulang' THEN 1
                    WHEN status_persetujuan = 'disetujui' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('created_at', 'desc')
            ->get();

        // ✅ CEK BENTROK OTOMATIS UNTUK SETIAP ITEM
        foreach ($peminjamanRuangans as $p) {
            $p->bentrokDengan = collect();
            if (in_array($p->status_persetujuan, ['menunggu', 'dijadwalkan_ulang'])) {
                $conflicts = PeminjamanRuangan::where('ruangan_id', $p->ruangan_id)
                    ->whereDate('tanggal_pemakaian', $p->tanggal_pemakaian)
                    ->where('id', '!=', $p->id)
                    ->whereIn('status_persetujuan', ['menunggu', 'disetujui', 'dijadwalkan_ulang'])
                    ->where(function($q) use ($p) {
                        $q->where('waktu_mulai', '<', $p->waktu_selesai)
                          ->where('waktu_selesai', '>', $p->waktu_mulai);
                    })
                    ->with('pemohon')
                    ->get();
                
                if ($conflicts->isNotEmpty()) {
                    $p->bentrokDengan = $conflicts;
                }
            }
        }

        return view('peminjaman-ruangan.approval', compact('peminjamanRuangans'));
    }

    /**
     * Reschedule permohonan (Sekretariat menawarkan jadwal alternatif)
     */
    public function reschedule(Request $request, PeminjamanRuangan $peminjamanRuangan)
    {
        abort_unless(auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']), 403);

        $validated = $request->validate([
            'tanggal_baru' => 'required|date',
            'waktu_mulai_baru' => 'required',
            'waktu_selesai_baru' => 'required|after:waktu_mulai_baru',
            'alasan_reschedule' => 'required|string',
        ]);

        $isAvailable = !PeminjamanRuangan::where('ruangan_id', $peminjamanRuangan->ruangan_id)
            ->whereDate('tanggal_pemakaian', $validated['tanggal_baru'])
            ->where('status_persetujuan', 'disetujui')
            ->where(function($q) use ($validated) {
                $q->where('waktu_mulai', '<', $validated['waktu_selesai_baru'])
                  ->where('waktu_selesai', '>', $validated['waktu_mulai_baru']);
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

        $bentrokList = $this->cekBentrokJadwal(
            $validated['ruangan_id'],
            $validated['tanggal_pemakaian'],
            $validated['waktu_mulai'],
            $validated['waktu_selesai']
        );

        if ($bentrokList->isNotEmpty()) {
            $ruangan = Ruangan::find($validated['ruangan_id']);
            $namaRuangan = $ruangan ? $ruangan->nama_ruangan : 'Ruangan';
            $tanggalFormat = Carbon::parse($validated['tanggal_pemakaian'])->locale('id')->isoFormat('dddd, D MMMM Y');

            $detailBentrok = [];
            foreach ($bentrokList as $b) {
                $namaPemohon = 'Unknown';
                if ($b->relationLoaded('pemohon') && $b->pemohon) {
                    $namaPemohon = $b->pemohon->nama_lengkap ?? $b->pemohon->name ?? 'NIP ' . $b->user_id;
                }
                $detailBentrok[] = "• <strong>{$b->waktu_mulai} - {$b->waktu_selesai}</strong> oleh <strong>{$namaPemohon}</strong> ({$b->keperluan})";
            }

            $pesan = "<strong>⚠️ Jadwal Bentrok!</strong><br>"
                . "{$namaRuangan} pada tanggal <strong>{$tanggalFormat}</strong> sudah dipinjam pada waktu berikut:<br><br>"
                . implode("<br>", $detailBentrok)
                . "<br><br>Silakan pilih <strong>waktu lain</strong> atau <strong>ruangan berbeda</strong>.";

            return back()->withErrors(['bentrok' => $pesan])->withInput();
        }

        $user = auth()->user();
        // ✅ OPTIMALISASI: Simpan nip, jika kosong simpan id user (agar Admin bisa submit)
        $userIdentifier = (string) ($user->nip ?? $user->id);

        if ($request->hasFile('lampiran')) {
            $validated['lampiran'] = $request->file('lampiran')->store('peminjaman_ruangan', 'public');
        }

        PeminjamanRuangan::create([
            'ruangan_id' => $validated['ruangan_id'],
            'agenda_id' => $validated['agenda_id'] ?? null,
            'user_id' => $userIdentifier, // Menggunakan identifier yang aman
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

        // ✅ NOTIFIKASI: Kirim ke Sekretariat & Admin lain
        $approvers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Sekretariat', 'Administrator', 'IT Administrator']);
        })->where('id', '!=', $user->id)->get();

        foreach ($approvers as $approver) {
            $approver->notify(new StatusPeminjamanRuanganNotification(
                PeminjamanRuangan::latest()->first(), // Ambil data terbaru yang baru saja dibuat
                'pengajuan',
                $user->nama_lengkap ?? $user->name,
                'Pengajuan peminjaman ruangan baru menunggu persetujuan Anda.'
            ));
        }

        return redirect()->route('peminjaman-ruangan.index')
            ->with('success', '✅ Permohonan peminjaman berhasil diajukan! Menunggu persetujuan.');
    }

    public function show(PeminjamanRuangan $peminjamanRuangan): View
    {
        $peminjamanRuangan->load(['ruangan', 'pemohon.bagian', 'agenda']);
        return view('peminjaman-ruangan.show', compact('peminjamanRuangan'));
    }

    public function edit(PeminjamanRuangan $peminjamanRuangan): View
    {
        $user = auth()->user();
        // ✅ OPTIMALISASI: Cek kepemilikan berdasarkan NIP atau ID
        $isOwner = ($peminjamanRuangan->user_id == $user->nip) || ((string)$peminjamanRuangan->user_id == (string)$user->id);
        
        abort_if(!$isOwner, 403, 'Anda tidak memiliki izin untuk mengubah data ini.');
        abort_if($peminjamanRuangan->status_persetujuan !== 'menunggu', 403, 'Pengajuan yang sudah diproses tidak dapat diubah.');
        
        $ruangans = Ruangan::where('status', 'aktif')->get();
        return view('peminjaman-ruangan.edit', compact('peminjamanRuangan', 'ruangans'));
    }

    public function update(Request $request, PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        $user = auth()->user();
        $isOwner = ($peminjamanRuangan->user_id == $user->nip) || ((string)$peminjamanRuangan->user_id == (string)$user->id);
        
        abort_if(!$isOwner, 403, 'Anda tidak memiliki izin untuk mengubah data ini.');
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
        return redirect()->route('peminjaman-ruangan.index')->with('success', '✅ Data peminjaman berhasil diperbarui.');
    }

    public function destroy(PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        $user = auth()->user();
        $isOwner = ($peminjamanRuangan->user_id == $user->nip) || ((string)$peminjamanRuangan->user_id == (string)$user->id);
        
        abort_if(!$isOwner, 403, 'Anda tidak memiliki izin untuk menghapus data ini.');
        abort_if($peminjamanRuangan->status_persetujuan !== 'menunggu', 403, 'Pengajuan yang sudah diproses tidak dapat dibatalkan.');
        
        if ($peminjamanRuangan->lampiran && Storage::disk('public')->exists($peminjamanRuangan->lampiran)) {
            Storage::disk('public')->delete($peminjamanRuangan->lampiran);
        }

        $peminjamanRuangan->delete();
        return redirect()->route('peminjaman-ruangan.index')->with('success', '✅ Pengajuan peminjaman berhasil dibatalkan.');
    }

    // =================================================================
    // METHOD KHUSUS SEKRETARIAT / ADMIN (VERIFIKASI)
    // =================================================================

    public function approve(PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']), 403);

        $user = auth()->user();
        $approverId = (string) ($user->nip ?? $user->id);

        $peminjamanRuangan->update([
            'status_persetujuan' => 'disetujui',
            'status_peminjaman' => 'disetujui',
            'disetujui_oleh' => $approverId,
            'tanggal_disetujui' => now(),
        ]);

        // ✅ OPTIMALISASI NOTIFIKASI: Cari pemohon berdasarkan user_id (bisa berupa NIP atau ID)
        $pemohon = User::where('nip', $peminjamanRuangan->user_id)
                       ->orWhere('id', $peminjamanRuangan->user_id)
                       ->first();
                       
        if ($pemohon) {
            $pemohon->notify(new StatusPeminjamanRuanganNotification(
                $peminjamanRuangan, 
                'disetujui', 
                $user->nama_lengkap ?? 'Sekretariat',
                'Permohonan peminjaman ruangan Anda telah disetujui.'
            ));
        }

        return redirect()->back()->with('success', '✅ Peminjaman ruangan telah disetujui.');
    }

    public function reject(Request $request, PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']), 403);

        $request->validate(['catatan_penolakan' => 'required|string|max:500']);
        $user = auth()->user();
        $rejectorId = (string) ($user->nip ?? $user->id);

        $peminjamanRuangan->update([
            'status_persetujuan' => 'ditolak',
            'status_peminjaman' => 'ditolak',
            'catatan_penolakan' => $request->catatan_penolakan,
            'ditolak_oleh' => $rejectorId,
            'tanggal_ditolak' => now(),
        ]);

        $pemohon = User::where('nip', $peminjamanRuangan->user_id)
                       ->orWhere('id', $peminjamanRuangan->user_id)
                       ->first();
                       
        if ($pemohon) {
            $pemohon->notify(new StatusPeminjamanRuanganNotification(
                $peminjamanRuangan, 
                'ditolak', 
                $user->nama_lengkap ?? 'Sekretariat',
                'Permohonan Anda ditolak. Alasan: ' . $request->catatan_penolakan
            ));
        }

        return redirect()->back()->with('success', 'Peminjaman ruangan telah ditolak.');
    }

    public function cancel(PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        $user = auth()->user();
        $isOwner = ($peminjamanRuangan->user_id == $user->nip) || ((string)$peminjamanRuangan->user_id == (string)$user->id);
        
        abort_if(!$isOwner, 403, 'Anda tidak memiliki izin.');
        abort_if($peminjamanRuangan->status_persetujuan !== 'menunggu', 403);

        $peminjamanRuangan->update(['status_persetujuan' => 'dibatalkan']);
        return redirect()->back()->with('success', '✅ Pengajuan peminjaman dibatalkan.');
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'ruangan_id' => 'required|exists:ruangans,id',
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
        ]);

        $bentrokList = $this->cekBentrokJadwal(
            $request->ruangan_id,
            $request->tanggal,
            $request->waktu_mulai,
            $request->waktu_selesai
        );

        $isAvailable = $bentrokList->isEmpty();
        $message = 'Ruangan tersedia pada waktu tersebut.';
        
        if (!$isAvailable) {
            $detail = [];
            foreach ($bentrokList as $b) {
                $namaPemohon = $b->pemohon ? ($b->pemohon->nama_lengkap ?? $b->pemohon->name ?? 'NIP ' . $b->user_id) : 'NIP ' . $b->user_id;
                $detail[] = "{$b->waktu_mulai}-{$b->waktu_selesai} oleh {$namaPemohon}";
            }
            $message = 'Ruangan sudah dipesan: ' . implode(', ', $detail);
        }

        return response()->json([
            'available' => $isAvailable,
            'message' => $message,
            'conflicts' => $bentrokList->map(fn($b) => [
                'waktu_mulai' => $b->waktu_mulai,
                'waktu_selesai' => $b->waktu_selesai,
                'keperluan' => $b->keperluan,
                'pemohon' => $b->pemohon ? ($b->pemohon->nama_lengkap ?? $b->pemohon->name ?? null) : null,
            ])->toArray(),
        ]);
    }

    public function revoke(Request $request, PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']), 403);
        abort_if($peminjamanRuangan->status_persetujuan !== 'disetujui', 403, 'Hanya peminjaman yang sudah disetujui yang dapat dibatalkan.');

        $request->validate(['catatan_pembatalan' => 'required|string|max:500']);
        $user = auth()->user();
        $revokerId = (string) ($user->nip ?? $user->id);

        $peminjamanRuangan->update([
            'status_persetujuan' => 'dibatalkan',
            'status_peminjaman' => 'dibatalkan',
            'catatan_pembatalan' => $request->catatan_pembatalan,
            'ditolak_oleh' => $revokerId,
            'tanggal_ditolak' => now(),
        ]);

        $pemohon = User::where('nip', $peminjamanRuangan->user_id)
                       ->orWhere('id', $peminjamanRuangan->user_id)
                       ->first();
                       
        if ($pemohon) {
            $pemohon->notify(new StatusPeminjamanRuanganNotification(
                $peminjamanRuangan, 
                'dibatalkan', 
                $user->nama_lengkap ?? 'Sekretariat',
                'Peminjaman yang sudah disetujui dibatalkan. Alasan: ' . $request->catatan_pembatalan
            ));
        }

        return redirect()->back()->with('success', '✅ Peminjaman berhasil DIBATALKAN.');
    }

    public function confirmReschedule(PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        $user = auth()->user();
        $isOwner = ($peminjamanRuangan->user_id == $user->nip) || ((string)$peminjamanRuangan->user_id == (string)$user->id);
        
        abort_if(!$isOwner, 403, 'Anda tidak memiliki izin.');
        abort_if($peminjamanRuangan->status_persetujuan !== 'menunggu_konfirmasi', 403);

        $peminjamanRuangan->update([
            'status_persetujuan' => 'disetujui',
            'status_peminjaman' => 'disetujui',
            'catatan_penolakan' => $peminjamanRuangan->catatan_penolakan . "\n[Dikonfirmasi oleh pemohon pada " . now()->format('d M Y, H:i') . "]",
        ]);

        return redirect()->route('peminjaman-ruangan.show', $peminjamanRuangan)
            ->with('success', '✅ Jadwal baru berhasil dikonfirmasi. Peminjaman Anda Disetujui!');
    }

    public function rejectReschedule(PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        $user = auth()->user();
        $isOwner = ($peminjamanRuangan->user_id == $user->nip) || ((string)$peminjamanRuangan->user_id == (string)$user->id);
        
        abort_if(!$isOwner, 403, 'Anda tidak memiliki izin.');
        abort_if($peminjamanRuangan->status_persetujuan !== 'menunggu_konfirmasi', 403);

        $peminjamanRuangan->update([
            'status_persetujuan' => 'menunggu',
            'status_peminjaman' => 'menunggu',
        ]);

        return redirect()->route('peminjaman-ruangan.edit', $peminjamanRuangan)
            ->with('info', 'Jadwal usulan Sekretariat ditolak. Silakan edit dan ajukan kembali.');
    }

    public function rescheduleDenganCatatan(Request $request, PeminjamanRuangan $peminjamanRuangan): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']), 403);
        abort_if($peminjamanRuangan->status_persetujuan !== 'disetujui', 403, 'Hanya peminjaman yang sudah disetujui yang dapat dijadwalkan ulang.');

        $validated = $request->validate([
            'catatan_reschedule' => 'required|string|max:500',
        ]);

        $peminjamanRuangan->loadMissing('pemohon');
        $user = auth()->user();
        $revokerId = (string) ($user->nip ?? $user->id);

        $peminjamanRuangan->update([
            'status_persetujuan' => 'dijadwalkan_ulang',
            'status_peminjaman' => 'dijadwalkan_ulang',
            'catatan_penolakan' => '[Reschedule] ' . $validated['catatan_reschedule'],
            'ditolak_oleh' => $revokerId,
            'tanggal_ditolak' => now(),
        ]);

        $pemohon = User::where('nip', $peminjamanRuangan->user_id)
                       ->orWhere('id', $peminjamanRuangan->user_id)
                       ->first();

        if ($pemohon) {
            $pemohon->notify(new StatusPeminjamanRuanganNotification(
                $peminjamanRuangan, 
                'dijadwalkan_ulang', 
                $user->nama_lengkap ?? 'Sekretariat',
                'Peminjaman ruangan Anda dijadwalkan ulang/dibatalkan. Catatan: ' . $validated['catatan_reschedule']
            ));
        }

        return redirect()->back()->with('success', '✅ Peminjaman berhasil dijadwalkan ulang. Pemohon telah diberi tahu.');
    }
}