<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Ruangan;
use App\Models\PeminjamanRuangan;
use App\Models\User;
use App\Notifications\AgendaNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AgendaController extends Controller
{
    /**
     * Helper: Bangun query filter agenda yang relevan untuk user
     * Digunakan oleh index() dan kalender() agar konsisten
     */
    private function filterAgendaRelevan($query, $user)
    {
        $userNip = (string) $user->nip;
        $userBagian = $user->bagian->nama_bagian ?? '';
        $userSubBagian = $user->subBagian->nama_sub_bagian ?? '';
        $userName = $user->nama_lengkap ?? $user->name ?? '';

        $query->where(function ($q) use ($userNip, $userBagian, $userSubBagian, $userName) {
            // 1. Agenda yang dibuat oleh user ini
            $q->where('created_by', $userNip);
            
            // 2. Agenda di mana user disebut sebagai pimpinan rapat (PJ)
            if (!empty($userName)) {
                $q->orWhere('pimpinan_rapat', 'LIKE', '%' . $userName . '%');
            }
            
            // 3. Agenda di mana user disebut sebagai notulen (PIC)
            if (!empty($userName)) {
                $q->orWhere('notulen', 'LIKE', '%' . $userName . '%');
            }
            
            // 4. Agenda di mana bagian user disebut sebagai inisiator
            if (!empty($userBagian)) {
                $q->orWhere('inisiator', 'LIKE', '%' . $userBagian . '%');
            }
            
            // 5. Agenda di mana bagian user diundang sebagai peserta (exact JSON match)
            if (!empty($userBagian)) {
                $q->orWhereJsonContains('peserta', $userBagian);
            }
            
            // 6. Agenda di mana sub bagian user diundang sebagai peserta (exact JSON match)
            if (!empty($userSubBagian)) {
                $q->orWhereJsonContains('peserta', $userSubBagian);
            }
            
            // 7. Peserta manual yang menyebutkan nama user ("Bagian - Nama User")
            if (!empty($userName)) {
                $q->orWhere('peserta', 'LIKE', '% - ' . $userName . '"%');
            }
        });

        return $query;
    }

    /**
     * Helper: Cek apakah user berhak melihat detail agenda ini
     */
    private function userBolehLihatAgenda($agenda, $user): bool
    {
        $userNip = (string) $user->nip;
        $userBagian = $user->bagian->nama_bagian ?? '';
        $userSubBagian = $user->subBagian->nama_sub_bagian ?? '';
        $userName = $user->nama_lengkap ?? $user->name ?? '';

        // Admin & IT Administrator selalu bisa lihat semua
        if ($user->hasRole(['IT Administrator', 'Administrator'])) {
            return true;
        }

        // Pembuat agenda selalu bisa lihat
        if ($agenda->created_by === $userNip) {
            return true;
        }

        // User adalah PJ (pimpinan rapat)
        if (!empty($agenda->pimpinan_rapat) && !empty($userName) && 
            str_contains($agenda->pimpinan_rapat, $userName)) {
            return true;
        }

        // User adalah Notulen/PIC
        if (!empty($agenda->notulen) && !empty($userName) && 
            str_contains($agenda->notulen, $userName)) {
            return true;
        }

        // Bagian user adalah inisiator
        if (!empty($agenda->inisiator) && !empty($userBagian) && 
            str_contains($agenda->inisiator, $userBagian)) {
            return true;
        }

        // Cek keanggotaan sebagai peserta
        $pesertaArray = is_array($agenda->peserta) ? $agenda->peserta : [];
        foreach ($pesertaArray as $peserta) {
            $pesertaTrimmed = trim($peserta);
            if (!empty($userBagian) && $pesertaTrimmed === $userBagian) return true;
            if (!empty($userSubBagian) && $pesertaTrimmed === $userSubBagian) return true;
            if (!empty($userName) && str_contains($peserta, ' - ' . $userName)) return true;
            if (!empty($userBagian) && str_starts_with($peserta, $userBagian . ' - ')) return true;
        }

        return false;
    }

    /**
     * Helper: Cek apakah user boleh mengedit/menghapus agenda ini
     * HANYA PEMBUAT (creator) yang boleh
     */
    private function userBolehEditHapus($agenda, $user): bool
    {
        return $agenda->created_by === (string) $user->nip;
    }

    public function index()
    {
        $user = auth()->user();
        $query = Agenda::with(['ruangan', 'creator']);
        
        if ($user->hasRole(['Administrator', 'IT Administrator'])) {
            // HANYA Admin & IT Administrator yang bisa lihat SEMUA agenda
            $agendas = $query->latest('created_at')->paginate(15);
        } else {
            // ✅ SEMUA role lain (Sekretariat, Pegawai, Direksi, dll) 
            // HANYA lihat agenda yang tertuju kepadanya
            $this->filterAgendaRelevan($query, $user);
            $agendas = $query->latest('created_at')->paginate(15);
        }

        return view('agenda.index', compact('agendas'));
    }

    public function create()
    {
        $bagians = \App\Models\Bagian::with('subBagians')->orderBy('nama_bagian')->get();
        return view('agenda.create', compact('bagians'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'acara' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'jenis_lokasi' => 'required|in:ruangan,manual',
            'ruangan_id' => 'nullable|required_if:jenis_lokasi,ruangan|exists:ruangans,id',
            'tempat_manual' => 'nullable|string|max:255',
            'pimpinan_rapat' => 'nullable|string|max:255',
            'inisiator' => 'nullable|string|max:255',
            'notulen' => 'nullable|string|max:255',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg|max:5120',
            'peserta' => 'nullable|array',
            'peserta.*' => 'string',
            'peserta_manual_bagian' => 'nullable|array',
            'peserta_manual_nama' => 'nullable|array',
        ]);

        if ($validated['jenis_lokasi'] === 'manual' && empty($validated['tempat_manual'])) {
            return back()->withErrors(['tempat_manual' => 'Tempat manual wajib diisi jika memilih lokasi manual.'])->withInput();
        }

        $user = auth()->user();
        $userNip = (string) $user->nip;

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('lampiran/agenda', 'public');
        }

        $daftarPesertaFinal = [];
        if (!empty($validated['peserta']) && is_array($validated['peserta'])) {
            $daftarPesertaFinal = array_merge($daftarPesertaFinal, $validated['peserta']);
        }

        if (!empty($validated['peserta_manual_bagian']) && !empty($validated['peserta_manual_nama'])) {
            foreach ($validated['peserta_manual_bagian'] as $index => $namaBagian) {
                $namaOrang = trim($validated['peserta_manual_nama'][$index] ?? '');
                if (!empty($namaBagian) && !empty($namaOrang)) {
                    $daftarPesertaFinal[] = $namaBagian . ' - ' . $namaOrang;
                }
            }
        }

        $ruanganId = null;
        $namaTempat = '';
        if ($validated['jenis_lokasi'] === 'ruangan') {
            $ruanganId = $validated['ruangan_id'];
            $ruangan = \App\Models\Ruangan::find($ruanganId);
            $namaTempat = $ruangan ? $ruangan->nama_ruangan : 'Ruangan Tidak Diketahui';
        } else {
            $namaTempat = $validated['tempat_manual'] ?? 'Tempat Tidak Diketahui';
        }

        $agenda = Agenda::create([
            'judul' => $validated['judul'],
            'acara' => $validated['acara'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'ruangan_id' => $ruanganId,
            'tempat' => $namaTempat,
            'pimpinan_rapat' => $validated['pimpinan_rapat'],
            'inisiator' => $validated['inisiator'] ?? ($user->bagian->nama_bagian ?? 'Inisiator'),
            'notulen' => $validated['notulen'],
            'peserta' => $daftarPesertaFinal, 
            'lampiran' => $lampiranPath,
            'created_by' => $userNip,
            'status' => 'submitted',
            'membutuhkan_ruangan' => $ruanganId ? 1 : 0,
        ]);

        if ($ruanganId) {
            PeminjamanRuangan::create([
                'agenda_id' => $agenda->id,
                'ruangan_id' => $ruanganId,
                'user_id' => $userNip,
                'tanggal_pemakaian' => $validated['tanggal_mulai'],
                'waktu_mulai' => $validated['jam_mulai'],
                'waktu_selesai' => $validated['jam_selesai'],
                'keperluan' => $validated['acara'],
                'jumlah_peserta' => count($daftarPesertaFinal),
                'status_persetujuan' => 'menunggu',
                'status_peminjaman' => 'menunggu',
            ]);
        }

        // Notifikasi ke peserta berdasarkan bagian/sub bagian
        if (!empty($daftarPesertaFinal)) {
            $usersToNotify = User::where(function($q) use ($daftarPesertaFinal) {
                foreach ($daftarPesertaFinal as $peserta) {
                    $q->orWhereHas('bagian', function($bq) use ($peserta) {
                        $bq->whereRaw("LOCATE(nama_bagian, ?) > 0", [$peserta]);
                    })->orWhereHas('subBagian', function($sbq) use ($peserta) {
                        $sbq->whereRaw("LOCATE(nama_sub_bagian, ?) > 0", [$peserta]);
                    });
                }
            })->where('id', '!=', auth()->id())->get();

            foreach ($usersToNotify as $userItem) {
                $userItem->notify(new AgendaNotification(
                    $agenda, 'baru', 'Anda diundang ke agenda: ' . $agenda->judul
                ));
            }
        }

        // Notifikasi ke PJ (Hanya berdasarkan nama_lengkap)
        if (!empty($validated['pimpinan_rapat'])) {
            $pjUser = User::where('nama_lengkap', 'LIKE', '%' . $validated['pimpinan_rapat'] . '%')->first();
            if ($pjUser && $pjUser->id !== auth()->id()) {
                $pjUser->notify(new AgendaNotification(
                    $agenda, 'pj', 'Anda ditunjuk sebagai Penanggung Jawab: ' . $agenda->judul
                ));
            }
        }
        
        // Notifikasi ke Notulen (Hanya berdasarkan nama_lengkap)
        if (!empty($validated['notulen'])) {
            $notulenUser = User::where('nama_lengkap', 'LIKE', '%' . $validated['notulen'] . '%')->first();
            if ($notulenUser && $notulenUser->id !== auth()->id()) {
                $notulenUser->notify(new AgendaNotification(
                    $agenda, 'notulen', 'Anda ditunjuk sebagai Notulen/PIC: ' . $agenda->judul
                ));
            }
        }

        return redirect()->route('agenda.index')
            ->with('success', 'Agenda berhasil dibuat dan diajukan!');
    }

    public function show(Agenda $agenda)
    {
        $user = auth()->user();

        // ✅ Gunakan helper untuk cek hak akses (konsisten dengan index)
        abort_unless(
            $this->userBolehLihatAgenda($agenda, $user), 
            403, 
            'Anda tidak memiliki izin untuk melihat detail agenda ini.'
        );

        $pesertaList = is_array($agenda->peserta) ? $agenda->peserta : [];
        $catatanList = is_array($agenda->catatan) ? $agenda->catatan : [];

        return view('agenda.show', compact('agenda', 'pesertaList', 'catatanList'));
    }

    /**
     * ✅ METHOD BARU: Form edit agenda
     * HANYA PEMBUAT yang boleh edit
     */
    public function edit(Agenda $agenda)
    {
        $user = auth()->user();

        // KUNCI: Hanya pembuat yang bisa edit
        abort_unless(
            $this->userBolehEditHapus($agenda, $user),
            403,
            'Hanya pembuat agenda yang dapat mengedit.'
        );
        abort_if($agenda->status !== 'submitted', 403, 'Agenda yang sudah diproses tidak dapat diedit.');

        $bagians = \App\Models\Bagian::with('subBagians')->orderBy('nama_bagian')->get();
        return view('agenda.edit', compact('agenda', 'bagians'));
    }

    /**
     * ✅ METHOD BARU: Proses update agenda
     * HANYA PEMBUAT yang boleh update
     */
    public function update(Request $request, Agenda $agenda)
    {
        $user = auth()->user();
        $userNip = (string) $user->nip;

        // KUNCI: Hanya pembuat yang bisa update
        abort_unless(
            $this->userBolehEditHapus($agenda, $user),
            403,
            'Hanya pembuat agenda yang dapat memperbarui.'
        );
        abort_if($agenda->status !== 'submitted', 403, 'Agenda yang sudah diproses tidak dapat diedit.');

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'acara' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'jenis_lokasi' => 'required|in:ruangan,manual',
            'ruangan_id' => 'nullable|required_if:jenis_lokasi,ruangan|exists:ruangans,id',
            'tempat_manual' => 'nullable|string|max:255',
            'pimpinan_rapat' => 'nullable|string|max:255',
            'inisiator' => 'nullable|string|max:255',
            'notulen' => 'nullable|string|max:255',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg|max:5120',
            'peserta' => 'nullable|array',
            'peserta.*' => 'string',
            'peserta_manual_bagian' => 'nullable|array',
            'peserta_manual_nama' => 'nullable|array',
        ]);

        if ($validated['jenis_lokasi'] === 'manual' && empty($validated['tempat_manual'])) {
            return back()->withErrors(['tempat_manual' => 'Tempat manual wajib diisi.'])->withInput();
        }

        // Proses peserta
        $daftarPesertaFinal = [];
        if (!empty($validated['peserta']) && is_array($validated['peserta'])) {
            $daftarPesertaFinal = array_merge($daftarPesertaFinal, $validated['peserta']);
        }
        if (!empty($validated['peserta_manual_bagian']) && !empty($validated['peserta_manual_nama'])) {
            foreach ($validated['peserta_manual_bagian'] as $index => $namaBagian) {
                $namaOrang = trim($validated['peserta_manual_nama'][$index] ?? '');
                if (!empty($namaBagian) && !empty($namaOrang)) {
                    $daftarPesertaFinal[] = $namaBagian . ' - ' . $namaOrang;
                }
            }
        }

        // Proses ruangan/tempat
        $ruanganId = null;
        $namaTempat = '';
        if ($validated['jenis_lokasi'] === 'ruangan') {
            $ruanganId = $validated['ruangan_id'];
            $ruangan = \App\Models\Ruangan::find($ruanganId);
            $namaTempat = $ruangan ? $ruangan->nama_ruangan : 'Ruangan Tidak Diketahui';
        } else {
            $namaTempat = $validated['tempat_manual'] ?? 'Tempat Tidak Diketahui';
        }

        // Proses lampiran
        $lampiranPath = $agenda->lampiran;
        if ($request->hasFile('lampiran')) {
            if ($agenda->lampiran && Storage::disk('public')->exists($agenda->lampiran)) {
                Storage::disk('public')->delete($agenda->lampiran);
            }
            $lampiranPath = $request->file('lampiran')->store('lampiran/agenda', 'public');
        }

        // Update agenda
        $agenda->update([
            'judul' => $validated['judul'],
            'acara' => $validated['acara'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'ruangan_id' => $ruanganId,
            'tempat' => $namaTempat,
            'pimpinan_rapat' => $validated['pimpinan_rapat'],
            'inisiator' => $validated['inisiator'] ?? ($user->bagian->nama_bagian ?? 'Inisiator'),
            'notulen' => $validated['notulen'],
            'peserta' => $daftarPesertaFinal,
            'lampiran' => $lampiranPath,
            'membutuhkan_ruangan' => $ruanganId ? 1 : 0,
        ]);

        // Sinkronisasi peminjaman ruangan terkait
        $peminjaman = PeminjamanRuangan::where('agenda_id', $agenda->id)->first();
        if ($peminjaman && $ruanganId) {
            $peminjaman->update([
                'ruangan_id' => $ruanganId,
                'tanggal_pemakaian' => $validated['tanggal_mulai'],
                'waktu_mulai' => $validated['jam_mulai'],
                'waktu_selesai' => $validated['jam_selesai'],
                'keperluan' => $validated['acara'],
                'jumlah_peserta' => count($daftarPesertaFinal),
                'status_persetujuan' => 'menunggu',
                'status_peminjaman' => 'menunggu',
            ]);
        } elseif ($peminjaman && !$ruanganId) {
            $peminjaman->delete();
        } elseif (!$peminjaman && $ruanganId) {
            PeminjamanRuangan::create([
                'agenda_id' => $agenda->id,
                'ruangan_id' => $ruanganId,
                'user_id' => $userNip,
                'tanggal_pemakaian' => $validated['tanggal_mulai'],
                'waktu_mulai' => $validated['jam_mulai'],
                'waktu_selesai' => $validated['jam_selesai'],
                'keperluan' => $validated['acara'],
                'jumlah_peserta' => count($daftarPesertaFinal),
                'status_persetujuan' => 'menunggu',
                'status_peminjaman' => 'menunggu',
            ]);
        }

        return redirect()->route('agenda.show', $agenda)
            ->with('success', 'Agenda berhasil diperbarui!');
    }

    public function approve(Agenda $agenda)
    {
        abort_unless(auth()->user()->hasRole('Sekretariat'), 403);
        $agenda->update(['status' => 'approved', 'approved_by' => (string) auth()->user()->nip, 'approved_at' => now()]);

        $peminjaman = PeminjamanRuangan::where('agenda_id', $agenda->id)->first();
        if ($peminjaman) {
            $peminjaman->update(['status_persetujuan' => 'disetujui', 'status_peminjaman' => 'disetujui', 'disetujui_oleh' => (string) auth()->user()->nip, 'tanggal_disetujui' => now()]);
        }

        $creator = User::where('nip', $agenda->created_by)->first();
        if ($creator) {
            $creator->notify(new AgendaNotification($agenda, 'disetujui', 'Agenda Anda telah disetujui oleh Sekretariat.'));
        }

        return redirect()->back()->with('success', 'Agenda dan peminjaman ruangan telah disetujui.');
    }

    public function reject(Request $request, Agenda $agenda)
    {
        abort_unless(auth()->user()->hasRole('Sekretariat'), 403);
        $validated = $request->validate(['rejection_reason' => 'required|string']);

        $agenda->update(['status' => 'rejected', 'rejection_reason' => $validated['rejection_reason']]);

        $peminjaman = PeminjamanRuangan::where('agenda_id', $agenda->id)->first();
        if ($peminjaman) {
            $peminjaman->update(['status_persetujuan' => 'ditolak', 'status_peminjaman' => 'ditolak', 'catatan_penolakan' => $validated['rejection_reason']]);
        }
        return redirect()->back()->with('success', 'Agenda telah ditolak.');
    }

    /**
     * Hapus agenda - HANYA PEMBUAT yang boleh hapus
     */
    public function destroy(Agenda $agenda)
    {
        $user = auth()->user();

        // KUNCI: Hanya pembuat yang bisa hapus
        abort_unless(
            $this->userBolehEditHapus($agenda, $user),
            403,
            'Hanya pembuat agenda yang dapat menghapus.'
        );
        abort_if($agenda->status !== 'submitted', 403, 'Agenda yang sudah diproses tidak dapat dihapus.');

        if ($agenda->lampiran && Storage::disk('public')->exists($agenda->lampiran)) {
            Storage::disk('public')->delete($agenda->lampiran);
        }
        PeminjamanRuangan::where('agenda_id', $agenda->id)->delete();
        $agenda->delete();
        
        return redirect()->route('agenda.index')->with('success', 'Agenda berhasil dihapus.');
    }

    /**
     * ✅ METHOD BARU: Kalender agenda
     * HANYA menampilkan agenda yang relevan untuk user (sama seperti index)
     * Agar agenda orang lain tidak muncul di kalender pegawai
     */
    public function kalender(): View
    {
        $user = auth()->user();
        $query = Agenda::with(['ruangan', 'creator']);

        if ($user->hasRole(['Administrator', 'IT Administrator'])) {
            // Admin & IT lihat semua agenda di kalender
            $agendas = $query->orderBy('tanggal_mulai', 'asc')->get();
        } else {
            // Pegawai & Sekretariat: hanya agenda yang tertuju kepadanya
            $this->filterAgendaRelevan($query, $user);
            $agendas = $query->orderBy('tanggal_mulai', 'asc')->get();
        }

        // Group by tanggal untuk kalender
        $kalenderAgenda = $agendas->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->tanggal_mulai)->format('Y-m-d');
        });

        return view('agenda.kalender', compact('kalenderAgenda'));
    }
}