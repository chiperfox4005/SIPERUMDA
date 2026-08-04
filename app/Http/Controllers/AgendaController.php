<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Ruangan;
use App\Models\PeminjamanRuangan;
use App\Models\User;
use App\Notifications\AgendaNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AgendaController extends Controller
{
    public function index()
{
    $user = auth()->user();
    $query = Agenda::with(['ruangan', 'creator']);
    
    if ($user->hasRole(['Administrator', 'IT Administrator', 'Sekretariat'])) {
        // Sekretariat dan Admin bisa lihat semua agenda
        $agendas = $query->latest('created_at')->paginate(15);
    } else {
        // Pegawai biasa hanya lihat agenda yang dibuat atau di mana bagiannya diundang
        $userNip = (string) $user->nip;
        $userBagian = $user->bagian->nama_bagian ?? '';
        $userSubBagian = $user->subBagian->nama_sub_bagian ?? '';

        $query->where(function ($q) use ($userNip, $userBagian, $userSubBagian) {
            // 1. Agenda yang dibuat oleh user ini
            $q->where('created_by', $userNip);
            
            // 2. ATAU agenda di mana bagian user diundang
            if (!empty($userBagian)) {
                $q->orWhereJsonContains('peserta', $userBagian);
            }
            
            // 3. ATAU agenda di mana sub bagian user diundang
            if (!empty($userSubBagian)) {
                $q->orWhereJsonContains('peserta', $userSubBagian);
            }
        });
        
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
            
            // ✅ PERBAIKAN: tempat_manual sekarang nullable, dan hanya divalidasi jika jenis_lokasi = manual
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

        // ✅ VALIDASI MANUAL: Jika jenis_lokasi = manual, tempat_manual WAJIB diisi
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
                $userItem->notify(new \App\Notifications\AgendaNotification(
                    $agenda, 'baru', 'Anda diundang ke agenda: ' . $agenda->judul
                ));
            }
        }

        return redirect()->route('agenda.index')
            ->with('success', 'Agenda berhasil dibuat dan diajukan!');
    }
    public function show(Agenda $agenda)
    {
        $user = auth()->user();
        $userNip = (string) $user->nip;
        $userBagian = $user->bagian->nama_bagian ?? '';
        $userSubBagian = $user->subBagian->nama_sub_bagian ?? '';

        $pesertaString = is_array($agenda->peserta) ? json_encode($agenda->peserta) : ($agenda->peserta ?? '');
        
        $canSee = $agenda->created_by === $userNip || 
                  $user->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']) ||
                  (!empty($userBagian) && str_contains($pesertaString, $userBagian)) ||
                  (!empty($userSubBagian) && str_contains($pesertaString, $userSubBagian));
                  
        abort_unless($canSee, 403, 'Anda tidak memiliki izin untuk melihat detail agenda ini.');

        $pesertaList = is_array($agenda->peserta) ? $agenda->peserta : [];
        $catatanList = is_array($agenda->catatan) ? $agenda->catatan : [];

        return view('agenda.show', compact('agenda', 'pesertaList', 'catatanList'));
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

    public function destroy(Agenda $agenda)
    {
        abort_if($agenda->created_by !== (string) auth()->user()->nip, 403);
        abort_if($agenda->status !== 'submitted', 403, 'Agenda yang sudah diproses tidak dapat dibatalkan.');

        if ($agenda->lampiran && Storage::disk('public')->exists($agenda->lampiran)) {
            Storage::disk('public')->delete($agenda->lampiran);
        }
        PeminjamanRuangan::where('agenda_id', $agenda->id)->delete();
        $agenda->delete();
        
        return redirect()->route('agenda.index')->with('success', 'Agenda berhasil dibatalkan.');
    }
}