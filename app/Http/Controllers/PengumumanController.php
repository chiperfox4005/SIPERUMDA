<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\Bagian;
use App\Models\User;
use App\Notifications\PengumumanNotification;
use App\Services\ConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengumumanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $userBagianId = $user->bagian->id ?? null;
        $userSubBagianId = $user->subBagian->id ?? null;
        
        $filter = $request->query('filter', 'semua');

        $query = Pengumuman::where('status', 'publish')
            ->whereDate('tanggal_publish', '<=', now())
            ->where(function ($q) {
                $q->whereNull('tanggal_selesai')
                  ->orWhereDate('tanggal_selesai', '>=', now());
            })
            ->where(function ($q) use ($userBagianId, $userSubBagianId) {
                $q->where('target_audience', 'semua_pegawai')
                  ->orWhere(function ($subQ) use ($userBagianId, $userSubBagianId) {
                      $subQ->where('target_audience', 'bagian_tertentu');
                      if ($userBagianId) {
                          $subQ->orWhereJsonContains('target_ids->bagians', (string)$userBagianId);
                      }
                      if ($userSubBagianId) {
                          $subQ->orWhereJsonContains('target_ids->sub_bagians', (string)$userSubBagianId);
                      }
                  });
            });

        if ($filter !== 'semua') {
            $query->where('prioritas', $filter); 
        }

        $pengumumans = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('pengumuman.index', compact('pengumumans', 'filter'));
    }

    public function create()
    {
        $bagians = Bagian::withCount('users')->with('subBagians')->get();
        $jenisOptions = ConfigService::get('pengumuman', 'jenis');
        
        $prioritasOptions = [
            ['id' => 'umum', 'nama' => 'Umum', 'warna' => 'secondary'],
            ['id' => 'penting', 'nama' => 'Penting', 'warna' => 'warning'],
            ['id' => 'mendesak', 'nama' => 'Mendesak', 'warna' => 'danger']
        ];
        
        $statusOptions = [
            ['id' => 'draft', 'nama' => 'Draft'],
            ['id' => 'publish', 'nama' => 'Publish']
        ];

        return view('pengumuman.create', compact('bagians', 'jenisOptions', 'prioritasOptions', 'statusOptions'));
    }

    public function store(Request $request)
    {
        $allowedJenis = implode(',', collect(ConfigService::get('pengumuman', 'jenis'))->pluck('id')->toArray());
        $allowedPrioritas = 'umum,penting,mendesak';
        $allowedStatus = 'draft,publish';

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'jenis' => 'required|in:' . $allowedJenis,
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'target_audience' => 'required|in:semua_pegawai,bagian_tertentu',
            'target_ids' => 'nullable|array',
            'isi' => 'required|string',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:2048',
            'prioritas' => 'required|in:' . $allowedPrioritas,
            'status' => 'required|in:' . $allowedStatus,
        ]);

        if ($request->hasFile('lampiran')) {
            $validated['lampiran'] = $request->file('lampiran')->store('pengumuman', 'public');
        }

        $validated['dibuat_oleh'] = (int) auth()->user()->id;
        $validated['tanggal_publish'] = $request->input('tanggal_publish') ?? $validated['tanggal_mulai'] ?? now();
        $validated['tanggal_berakhir'] = $request->input('tanggal_berakhir') ?? ($validated['tanggal_selesai'] ?? null);

        $bagianIds = [];
        $subBagianIds = [];

        if ($request->has('target_ids') && is_array($request->target_ids)) {
            foreach ($request->target_ids as $targetId) {
                if (str_starts_with($targetId, 'bagian_')) {
                    $bagianIds[] = (int) str_replace('bagian_', '', $targetId);
                } elseif (str_starts_with($targetId, 'sub_')) {
                    $subBagianIds[] = (int) str_replace('sub_', '', $targetId);
                }
            }
            $validated['target_ids'] = json_encode(['bagians' => $bagianIds, 'sub_bagians' => $subBagianIds]);
        } else {
            $validated['target_ids'] = null;
        }

        $pengumuman = Pengumuman::create($validated);

        // KIRIM NOTIFIKASI HANYA JIKA STATUS PUBLISH
        if ($pengumuman->status === 'publish') {
            if ($pengumuman->target_audience === 'semua_pegawai') {
                $usersToNotify = User::where('id', '!=', auth()->user()->id)->get();
            } else {
                $usersToNotify = User::where(function($q) use ($bagianIds, $subBagianIds) {
                    if (!empty($bagianIds)) {
                        $q->orWhereIn('bagian_id', $bagianIds);
                    }
                    if (!empty($subBagianIds)) {
                        $q->orWhereIn('sub_bagian_id', $subBagianIds);
                    }
                })->where('id', '!=', auth()->user()->id)->get();
            }

            foreach ($usersToNotify as $userItem) {
                $userItem->notify(new PengumumanNotification(
                    $pengumuman, 
                    'Pengumuman baru telah dipublikasikan: ' . $pengumuman->judul
                ));
            }
        }

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function show(Pengumuman $pengumuman)
    {
        return view('pengumuman.show', compact('pengumuman'));
    }

    public function edit(Pengumuman $pengumuman)
    {
        abort_unless(
            auth()->user()->id == $pengumuman->dibuat_oleh || 
            auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']), 
            403
        );
        
        $bagians = Bagian::withCount('users')->with('subBagians')->get();
        $jenisOptions = ConfigService::get('pengumuman', 'jenis');
        $prioritasOptions = [
            ['id' => 'umum', 'nama' => 'Umum', 'warna' => 'secondary'],
            ['id' => 'penting', 'nama' => 'Penting', 'warna' => 'warning'],
            ['id' => 'mendesak', 'nama' => 'Mendesak', 'warna' => 'danger']
        ];
        $statusOptions = [
            ['id' => 'draft', 'nama' => 'Draft'],
            ['id' => 'publish', 'nama' => 'Publish']
        ];

        return view('pengumuman.edit', compact('pengumuman', 'bagians', 'jenisOptions', 'prioritasOptions', 'statusOptions'));
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        abort_unless(
            auth()->user()->id == $pengumuman->dibuat_oleh || 
            auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']), 
            403
        );
        
        $allowedJenis = implode(',', collect(ConfigService::get('pengumuman', 'jenis'))->pluck('id')->toArray());
        $allowedPrioritas = 'umum,penting,mendesak';
        $allowedStatus = 'draft,publish';

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'jenis' => 'required|in:' . $allowedJenis,
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'target_audience' => 'required|in:semua_pegawai,bagian_tertentu',
            'target_ids' => 'nullable|array',
            'isi' => 'required|string',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:2048',
            'prioritas' => 'required|in:' . $allowedPrioritas,
            'status' => 'required|in:' . $allowedStatus,
        ]);

        if ($request->hasFile('lampiran')) {
            if ($pengumuman->lampiran) {
                Storage::disk('public')->delete($pengumuman->lampiran);
            }
            $validated['lampiran'] = $request->file('lampiran')->store('pengumuman', 'public');
        }

        $validated['tanggal_publish'] = $request->input('tanggal_publish') ?? $validated['tanggal_mulai'] ?? $pengumuman->tanggal_publish ?? now();
        $validated['tanggal_berakhir'] = $request->input('tanggal_berakhir') ?? ($validated['tanggal_selesai'] ?? $pengumuman->tanggal_berakhir ?? null);

        if ($request->has('target_ids') && is_array($request->target_ids)) {
            $bagianIds = [];
            $subBagianIds = [];
            foreach ($request->target_ids as $targetId) {
                if (str_starts_with($targetId, 'bagian_')) {
                    $bagianIds[] = (int) str_replace('bagian_', '', $targetId);
                } elseif (str_starts_with($targetId, 'sub_')) {
                    $subBagianIds[] = (int) str_replace('sub_', '', $targetId);
                }
            }
            $validated['target_ids'] = json_encode(['bagians' => $bagianIds, 'sub_bagians' => $subBagianIds]);
        } else {
            $validated['target_ids'] = null;
        }

        $pengumuman->update($validated);
        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        abort_unless(
            auth()->user()->id == $pengumuman->dibuat_oleh || 
            auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']), 
            403
        );
        
        if ($pengumuman->lampiran) {
            Storage::disk('public')->delete($pengumuman->lampiran);
        }
        $pengumuman->delete();
        
        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}