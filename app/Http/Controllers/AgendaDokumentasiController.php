<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\AgendaDokumentasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AgendaDokumentasiController extends Controller
{
    public function create(Agenda $agenda)
    {
        // Hanya pemohon atau sekretariat yang bisa upload dokumentasi
        abort_unless(
            auth()->user()->hasRole('Sekretariat') || 
            $agenda->created_by == (string) auth()->user()->nip,
            403
        );

        // Cek apakah sudah ada dokumentasi
        $dokumentasi = AgendaDokumentasi::where('agenda_id', $agenda->id)->first();

        return view('agenda.dokumentasi.create', compact('agenda', 'dokumentasi'));
    }

    public function store(Request $request, Agenda $agenda)
    {
        $validated = $request->validate([
            'risalah_rapat' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'daftar_hadir' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
            'foto_kegiatan' => 'nullable|array',
            'foto_kegiatan.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'lampiran_lainnya' => 'nullable|string',
        ]);

        $dokumentasi = AgendaDokumentasi::firstOrNew(['agenda_id' => $agenda->id]);
        $dokumentasi->uploaded_by = (string) auth()->user()->nip;
        $dokumentasi->uploaded_at = now();

        // Upload Risalah Rapat
        if ($request->hasFile('risalah_rapat')) {
            if ($dokumentasi->risalah_rapat) {
                Storage::disk('public')->delete($dokumentasi->risalah_rapat);
            }
            $dokumentasi->risalah_rapat = $request->file('risalah_rapat')->store('agenda/dokumentasi/risalah', 'public');
        }

        // Upload Daftar Hadir
        if ($request->hasFile('daftar_hadir')) {
            if ($dokumentasi->daftar_hadir) {
                Storage::disk('public')->delete($dokumentasi->daftar_hadir);
            }
            $dokumentasi->daftar_hadir = $request->file('daftar_hadir')->store('agenda/dokumentasi/daftar_hadir', 'public');
        }

        // Upload Foto Kegiatan
        if ($request->hasFile('foto_kegiatan')) {
            $fotoPaths = [];
            foreach ($request->file('foto_kegiatan') as $foto) {
                $fotoPaths[] = $foto->store('agenda/dokumentasi/foto', 'public');
            }
            $dokumentasi->foto_kegiatan = json_encode($fotoPaths);
        }

        $dokumentasi->lampiran_lainnya = $validated['lampiran_lainnya'] ?? null;
        $dokumentasi->save();

        return redirect()->route('agenda.show', $agenda)
            ->with('success', 'Dokumentasi berhasil diunggah.');
    }

    public function download(Agenda $agenda, $jenis)
    {
        $dokumentasi = AgendaDokumentasi::where('agenda_id', $agenda->id)->firstOrFail();

        $file = null;
        $filename = '';

        switch ($jenis) {
            case 'risalah':
                $file = $dokumentasi->risalah_rapat;
                $filename = 'Risalah_Rapat_' . $agenda->nomor_surat . '.pdf';
                break;
            case 'daftar_hadir':
                $file = $dokumentasi->daftar_hadir;
                $filename = 'Daftar_Hadir_' . $agenda->nomor_surat . '.pdf';
                break;
        }

        if (!$file || !Storage::disk('public')->exists($file)) {
            abort(404, 'File tidak ditemukan');
        }

        return Storage::disk('public')->download($file, $filename);
    }
}