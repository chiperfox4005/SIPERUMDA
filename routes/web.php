<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AgendaDokumentasiController;
use App\Http\Controllers\PeminjamanRuanganController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\KalenderController;
use App\Http\Controllers\KalenderLiburController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\DocumentSubmissionController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\SignatoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\KepegawaianController;
use App\Http\Controllers\Admin\KonfigurasiController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; 
use App\Models\SubBagian;    

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// ==========================================
// ROUTE PUBLIK (TANPA AUTH/LOGIN)
// ==========================================
Route::get('/track-surat', [SuratController::class, 'showTrackingForm'])->name('surat.track');
Route::post('/track-surat', [SuratController::class, 'trackSurat'])->name('surat.track.post');

// Document Template Engine: Public Tracking
Route::get('/surat/track/{id}', [DocumentSubmissionController::class, 'tracking'])->name('surat.tracking');

Route::get('/api/v1/sub-bagians', function (Request $request) {
    $request->validate(['bagian_id' => 'required|exists:bagians,id']);
    $subBagians = SubBagian::where('bagian_id', $request->bagian_id)
        ->select('id', 'nama_sub_bagian')
        ->orderBy('nama_sub_bagian', 'asc')
        ->get();
    return response()->json($subBagians);
});

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {

    // ==========================================
    // ROUTE KHUSUS DIREKSI (VIEW ONLY)
    // ==========================================
    Route::middleware(['role:Direksi'])->group(function () {
        Route::get('/direksi', [\App\Http\Controllers\DireksiController::class, 'index'])->name('direksi.dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('pengumuman', PengumumanController::class);

    // ==========================================
    // DOCUMENT TEMPLATE ENGINE (SURAT)
    // ==========================================
    Route::get('/surat', [DocumentSubmissionController::class, 'index'])->name('surat.index');
    Route::get('/surat/pilih-template', [DocumentSubmissionController::class, 'pilihTemplate'])->name('surat.pilih-template');
    Route::get('/surat/buat/{template}', [DocumentSubmissionController::class, 'buatDenganTemplate'])->name('surat.buat-template');
    Route::post('/surat', [DocumentSubmissionController::class, 'store'])->name('surat.store');

    // Sekretariat: Approval & Master Tanda Tangan
    Route::middleware(['role:Sekretariat'])->group(function () {
        Route::get('/surat/approval', [DocumentSubmissionController::class, 'approval'])->name('surat.approval');
        Route::post('/surat/{submission}/approve', [DocumentSubmissionController::class, 'approve'])->name('surat.approve');
        Route::post('/surat/{submission}/reject', [DocumentSubmissionController::class, 'reject'])->name('surat.reject');
        Route::get('/surat/generate-nomor', [DocumentSubmissionController::class, 'generateNomorSurat'])->name('surat.generate-nomor');

        Route::resource('signatories', SignatoryController::class);
        Route::post('/signatories/{signatory}/toggle', [SignatoryController::class, 'toggleStatus'])
            ->name('signatories.toggle');
    });

    Route::get('/surat/{submission}', [DocumentSubmissionController::class, 'show'])->name('surat.show');
    Route::get('/surat/{submission}/download', [DocumentSubmissionController::class, 'download'])->name('surat.download');
    Route::get('/surat/{surat}/export-pdf', [SuratController::class, 'exportPdf'])->name('surat.export');

    Route::resource('surat', SuratController::class)->except(['index', 'create', 'store', 'show']);

    // ==========================================
    // ROUTE AGENDA & DOKUMENTASI
    // ==========================================
    Route::get('/agenda/template/{template}', [AgendaController::class, 'selectTemplate'])->name('agenda.template.select');
    Route::resource('agenda', AgendaController::class);
    Route::post('/agenda/{agenda}/approve', [AgendaController::class, 'approve'])->name('agenda.approve');
    Route::post('/agenda/{agenda}/reject', [AgendaController::class, 'reject'])->name('agenda.reject');
    Route::post('/agenda/{agenda}/cancel', [AgendaController::class, 'cancel'])->name('agenda.cancel');
    Route::post('/agenda/{agenda}/rsvp', [AgendaController::class, 'rsvp'])->name('agenda.rsvp');
    Route::get('/agenda/export-pdf', [AgendaController::class, 'exportPdf'])->name('agenda.export');
    Route::post('/agenda/{agenda}/duplicate', [AgendaController::class, 'duplicate'])->name('agenda.duplicate');
    Route::post('/agenda/{agenda}/reschedule', [AgendaController::class, 'reschedule'])->name('agenda.reschedule');

    Route::get('/agenda/{agenda}/dokumentasi', [AgendaDokumentasiController::class, 'create'])->name('agenda.dokumentasi.create');
    Route::post('/agenda/{agenda}/dokumentasi', [AgendaDokumentasiController::class, 'store'])->name('agenda.dokumentasi.store');
    Route::get('/agenda/{agenda}/dokumentasi/download/{jenis}', [AgendaDokumentasiController::class, 'download'])->name('agenda.dokumentasi.download');

    // ==========================================
    // ROUTE PEMINJAMAN RUANGAN
    // ==========================================
    Route::get('/peminjaman-ruangan/approval', [PeminjamanRuanganController::class, 'approval'])->name('peminjaman-ruangan.approval');
    Route::post('/peminjaman-ruangan/{peminjamanRuangan}/reschedule', [PeminjamanRuanganController::class, 'reschedule'])->name('peminjaman-ruangan.reschedule');
    Route::get('/peminjaman-ruangan/kalender', [PeminjamanRuanganController::class, 'kalender'])->name('peminjaman-ruangan.kalender');
    Route::resource('peminjaman-ruangan', PeminjamanRuanganController::class);
    Route::post('/peminjaman-ruangan/{peminjamanRuangan}/approve', [PeminjamanRuanganController::class, 'approve'])->name('peminjaman-ruangan.approve');
    Route::post('/peminjaman-ruangan/{peminjamanRuangan}/reject', [PeminjamanRuanganController::class, 'reject'])->name('peminjaman-ruangan.reject');
    Route::post('/peminjaman-ruangan/{peminjamanRuangan}/cancel', [PeminjamanRuanganController::class, 'cancel'])->name('peminjaman-ruangan.cancel');
    Route::get('/peminjaman-ruangan/check-availability', [PeminjamanRuanganController::class, 'checkAvailability'])->name('peminjaman-ruangan.availability');

    Route::post('/peminjaman-ruangan/{peminjamanRuangan}/confirm-reschedule', [PeminjamanRuanganController::class, 'confirmReschedule'])->name('peminjaman-ruangan.confirm-reschedule');
    Route::post('/peminjaman-ruangan/{peminjamanRuangan}/reject-reschedule', [PeminjamanRuanganController::class, 'rejectReschedule'])->name('peminjaman-ruangan.reject-reschedule');
    Route::post('/peminjaman-ruangan/{peminjamanRuangan}/revoke', [PeminjamanRuanganController::class, 'revoke'])->name('peminjaman-ruangan.revoke');
    
    // ✅ Route untuk Reschedule dengan Catatan (Badge Merah)
    Route::post('/peminjaman-ruangan/{peminjamanRuangan}/reschedule-dengan-catatan', [PeminjamanRuanganController::class, 'rescheduleDenganCatatan'])->name('peminjaman-ruangan.reschedule-dengan-catatan');

    Route::resource('ruangan', RuanganController::class);

    Route::get('/kalender', [KalenderController::class, 'index'])->name('kalender');
    Route::get('/kalender/events', [KalenderController::class, 'events'])->name('kalender.events');

    // ==========================================
    // ROUTE NOTIFIKASI
    // ==========================================
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/{id}/read', [NotifikasiController::class, 'markAsRead'])->name('notifikasi.mark-as-read');
    Route::post('/notifikasi/read-all', [NotifikasiController::class, 'markAllAsRead'])->name('notifikasi.mark-all-read');
    Route::get('/notifikasi/unread-count', [NotifikasiController::class, 'unreadCount'])->name('notifikasi.unread-count');

    // ==========================================
    // ROUTE LAPORAN
    // ==========================================
    Route::get('/laporan/agenda', [LaporanController::class, 'agenda'])->name('laporan.agenda');
    Route::get('/laporan/peminjaman-ruangan', [LaporanController::class, 'peminjamanRuangan'])->name('laporan.peminjaman');
    Route::get('/laporan/okupansi', [LaporanController::class, 'okupansi'])->name('laporan.okupansi');

    // ==========================================
    // API ROUTES UNTUK FULLCALENDAR
    // ==========================================
    Route::get('/api/kalender-all', function () {
        $user = auth()->user();
        $events = [];

        $agendaQuery = \App\Models\Agenda::with(['creator', 'ruangan'])
            ->whereMonth('tanggal_mulai', now()->month)
            ->whereYear('tanggal_mulai', now()->year);
            
        if (!$user->hasRole(['Sekretariat', 'IT Administrator', 'Administrator'])) {
            $userBagianNama = $user->bagian->nama_bagian ?? '';
            $userSubBagianNama = $user->subBagian->nama_sub_bagian ?? '';
            $agendaQuery->where(function ($q) use ($user, $userBagianNama, $userSubBagianNama) {
                $q->where('created_by', (string) $user->nip);
                if (!empty($userBagianNama)) $q->orWhereJsonContains('peserta', $userBagianNama);
                if (!empty($userSubBagianNama)) $q->orWhereJsonContains('peserta', $userSubBagianNama);
            });
        }
        
        foreach ($agendaQuery->get() as $agenda) {
            $color = $agenda->status == 'approved' ? '#10b981' : ($agenda->status == 'rejected' ? '#ef4444' : '#f59e0b');
            $endDate = $agenda->tanggal_selesai ? $agenda->tanggal_selesai->format('Y-m-d') : $agenda->tanggal_mulai->format('Y-m-d');
            $events[] = [
                'title' => $agenda->judul . ' (' . ($agenda->creator->nama_lengkap ?? 'Unknown') . ')',
                'start' => $agenda->tanggal_mulai->format('Y-m-d') . 'T' . ($agenda->jam_mulai ?? '00:00'),
                'end' => $endDate . 'T' . ($agenda->jam_selesai ?? '23:59'),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'url' => route('agenda.show', $agenda),
                'extendedProps' => [ 'type' => 'agenda' ]
            ];
        }

        $pengumumanQuery = \App\Models\Pengumuman::where('status', 'publish')
            ->whereDate('tanggal_publish', '<=', now())
            ->where(function ($q) { 
                $q->whereNull('tanggal_berakhir')
                  ->orWhereDate('tanggal_berakhir', '>=', now());
            });

        if (!$user->hasRole(['Sekretariat', 'IT Administrator', 'Administrator'])) {
            $userBagianId = $user->bagian->id ?? null;
            $userSubBagianId = $user->subBagian->id ?? null;
            $pengumumanQuery->where(function ($q) use ($userBagianId, $userSubBagianId) {
                $q->where('target_audience', 'semua_pegawai')
                  ->orWhere(function ($subQ) use ($userBagianId, $userSubBagianId) {
                      $subQ->where('target_audience', 'bagian_tertentu');
                      if ($userBagianId) $subQ->orWhereJsonContains('target_ids->bagians', (string)$userBagianId);
                      if ($userSubBagianId) $subQ->orWhereJsonContains('target_ids->sub_bagians', (string)$userSubBagianId);
                  });
            });
        }

        foreach ($pengumumanQuery->get() as $pengumuman) {
            $startDate = $pengumuman->tanggal_publish ? \Carbon\Carbon::parse($pengumuman->tanggal_publish)->format('Y-m-d') : \Carbon\Carbon::parse($pengumuman->created_at)->format('Y-m-d');
            $endDate = $pengumuman->tanggal_berakhir ? \Carbon\Carbon::parse($pengumuman->tanggal_berakhir)->addDay()->format('Y-m-d') : $startDate;
            
            $events[] = [
                'title' => '📢 ' . $pengumuman->judul,
                'start' => $startDate,
                'end' => $endDate,
                'allDay' => true,
                'backgroundColor' => '#10b981',
                'borderColor' => '#10b981',
                'url' => route('pengumuman.show', $pengumuman),
                'extendedProps' => [ 'type' => 'pengumuman' ]
            ];
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->withoutVerifying()->get("https://libur.deno.dev/api?year=" . now()->year);
            if ($response->successful()) {
                foreach ($response->json() as $libur) {
                    if ($libur['is_national_holiday'] ?? false) {
                        $events[] = [
                            'title' => '🔴 ' . $libur['name'],
                            'start' => $libur['date'],
                            'allDay' => true,
                            'backgroundColor' => '#dc2626',
                            'borderColor' => '#dc2626',
                            'textColor' => '#ffffff',
                            'extendedProps' => [ 'type' => 'libur' ]
                        ];
                    }
                }
            }
        } catch (\Exception $e) {}

        return response()->json($events);
    })->name('api.kalender-all');

    Route::get('/api/kalender-agenda', function () {
        $user = auth()->user();
        $events = [];
        
        $agendaQuery = \App\Models\Agenda::with(['creator', 'ruangan'])
            ->whereYear('tanggal_mulai', now()->year)
            ->whereMonth('tanggal_mulai', now()->month);
            
        if (!$user->hasRole(['Sekretariat', 'IT Administrator', 'Administrator'])) {
            $userNip = (string) $user->nip;
            $userBagianNama = trim($user->bagian->nama_bagian ?? '');
            $userSubBagianNama = trim($user->subBagian->nama_sub_bagian ?? '');
            
            $agendaQuery->where(function ($q) use ($userNip, $userBagianNama, $userSubBagianNama) {
                $q->where('created_by', $userNip);
                
                if (!empty($userBagianNama)) {
                    $q->orWhere('peserta', 'LIKE', '%' . $userBagianNama . '%');
                }
                
                if (!empty($userSubBagianNama)) {
                    $q->orWhere('peserta', 'LIKE', '%' . $userSubBagianNama . '%');
                }
            });
        }
        
        $agendas = $agendaQuery->get();
        
        foreach ($agendas as $agenda) {
            $color = $agenda->status == 'approved' ? '#10b981' : ($agenda->status == 'rejected' ? '#ef4444' : '#f59e0b');
            $endDate = $agenda->tanggal_selesai ? $agenda->tanggal_selesai->format('Y-m-d') : $agenda->tanggal_mulai->format('Y-m-d');
            
            $events[] = [
                'title' => $agenda->judul,
                'start' => $agenda->tanggal_mulai->format('Y-m-d') . 'T' . ($agenda->jam_mulai ?? '00:00'),
                'end' => $endDate . 'T' . ($agenda->jam_selesai ?? '23:59'),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'url' => route('agenda.show', $agenda),
                'extendedProps' => ['status' => 'Agenda: ' . ucfirst($agenda->status)]
            ];
        }
        
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(3)->withoutVerifying()->get("https://libur.deno.dev/api?year=" . now()->year);
            if ($response->successful()) {
                foreach ($response->json() as $libur) {
                    if ($libur['is_national_holiday'] ?? false) {
                        $events[] = [
                            'title' => '🔴 ' . $libur['name'],
                            'start' => $libur['date'],
                            'allDay' => true,
                            'backgroundColor' => '#dc2626',
                            'borderColor' => '#dc2626',
                            'textColor' => '#ffffff',
                            'extendedProps' => ['status' => 'Libur Nasional']
                        ];
                    }
                }
            }
        } catch (\Exception $e) {}
        
        return response()->json($events);
    })->name('api.kalender-agenda');

    Route::get('/api/kalender-pengumuman', function () {
        $user = auth()->user();
        $events = [];
        $userBagianId = $user->bagian->id ?? null;
        $userSubBagianId = $user->subBagian->id ?? null;
        
        $pengumumans = \App\Models\Pengumuman::where('status', 'publish')
            ->whereDate('tanggal_publish', '<=', now())
            ->where(function ($q) {
                $q->whereNull('tanggal_berakhir')
                  ->orWhereDate('tanggal_berakhir', '>=', now());
            })
            ->where(function ($q) use ($userBagianId, $userSubBagianId) {
                $q->where('target_audience', 'semua_pegawai')
                  ->orWhere(function ($subQ) use ($userBagianId, $userSubBagianId) {
                      $subQ->where('target_audience', 'bagian_tertentu');
                      if ($userBagianId) $subQ->orWhereJsonContains('target_ids->bagians', (string)$userBagianId);
                      if ($userSubBagianId) $subQ->orWhereJsonContains('target_ids->sub_bagians', (string)$userSubBagianId);
                  });
            })
            ->get();

        foreach ($pengumumans as $pengumuman) {
            $startDate = $pengumuman->tanggal_publish ? \Carbon\Carbon::parse($pengumuman->tanggal_publish)->format('Y-m-d') : \Carbon\Carbon::parse($pengumuman->created_at)->format('Y-m-d');
            $endDate = $pengumuman->tanggal_berakhir ? \Carbon\Carbon::parse($pengumuman->tanggal_berakhir)->addDay()->format('Y-m-d') : $startDate;
            
            $events[] = [
                'title' => '📢 ' . $pengumuman->judul,
                'start' => $startDate,
                'end' => $endDate,
                'allDay' => true,
                'backgroundColor' => '#10b981',
                'borderColor' => '#10b981',
                'url' => route('pengumuman.show', $pengumuman),
                'extendedProps' => [
                    'kategori' => $pengumuman->kategori ?? $pengumuman->jenis ?? 'Umum',
                    'status' => 'Pengumuman'
                ]
            ];
        }
        
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->withoutVerifying()
                ->get("https://libur.deno.dev/api?year=" . now()->year);
            if ($response->successful()) {
                foreach ($response->json() as $libur) {
                    if ($libur['is_national_holiday'] ?? false) {
                        $events[] = [
                            'title' => '🔴 ' . $libur['name'],
                            'start' => $libur['date'],
                            'allDay' => true,
                            'backgroundColor' => '#dc2626',
                            'borderColor' => '#dc2626',
                            'textColor' => '#ffffff',
                            'extendedProps' => ['status' => 'Libur Nasional']
                        ];
                    }
                }
            }
        } catch (\Exception $e) {}
        
        return response()->json($events);
    })->name('api.kalender-pengumuman');

    Route::get('/api/kalender-ruangan', function () {
        $peminjamans = \App\Models\PeminjamanRuangan::with(['ruangan', 'agenda', 'pemohon'])
            ->whereMonth('tanggal_pemakaian', now()->month)
            ->whereYear('tanggal_pemakaian', now()->year)
            ->get();
        
        $events = [];
        foreach ($peminjamans as $peminjaman) {
            $color = '#f59e0b'; 
            $statusText = 'Menunggu';
            
            if ($peminjaman->status_persetujuan == 'disetujui') { 
                $color = '#10b981'; 
                $statusText = 'Terpakai'; 
            } elseif ($peminjaman->status_persetujuan == 'dibatalkan') {
                $color = '#ef4444'; 
                $statusText = 'Dibatalkan';
            } elseif ($peminjaman->status_persetujuan == 'ditolak') { 
                $color = '#ef4444'; 
                $statusText = 'Ditolak'; 
            } elseif ($peminjaman->status_persetujuan == 'dijadwalkan_ulang') {
                $color = '#ef4444'; 
                $statusText = 'Dijadwalkan Ulang';
            }
            
            $events[] = [
                'title' => ($peminjaman->ruangan->nama_ruangan ?? 'Ruangan') . ' - ' . ($peminjaman->agenda->judul ?? $peminjaman->keperluan),
                'start' => $peminjaman->tanggal_pemakaian->format('Y-m-d') . 'T' . $peminjaman->waktu_mulai,
                'end' => $peminjaman->tanggal_pemakaian->format('Y-m-d') . 'T' . $peminjaman->waktu_selesai,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'url' => route('peminjaman-ruangan.show', $peminjaman),
                'extendedProps' => [
                    'status' => $statusText,
                    'status_persetujuan' => $peminjaman->status_persetujuan
                ]
            ];
        }
        return response()->json($events);
    })->name('api.kalender-ruangan');

    Route::get('/api/kalender-libur', [KalenderLiburController::class, 'events'])->name('api.kalender-libur');

    // ==========================================
    // ROLE-SPECIFIC ROUTES
    // ==========================================
    Route::middleware(['role:Kepegawaian'])->prefix('kepegawaian')->name('kepegawaian.')->group(function () {
        // Bagian
        Route::get('/bagian', [KepegawaianController::class, 'bagianIndex'])->name('bagian.index');
        Route::get('/bagian/create', [KepegawaianController::class, 'bagianCreate'])->name('bagian.create');
        Route::post('/bagian', [KepegawaianController::class, 'bagianStore'])->name('bagian.store');
        Route::get('/bagian/{bagian}/edit', [KepegawaianController::class, 'bagianEdit'])->name('bagian.edit');
        Route::put('/bagian/{bagian}', [KepegawaianController::class, 'bagianUpdate'])->name('bagian.update');
        Route::delete('/bagian/{bagian}', [KepegawaianController::class, 'bagianDestroy'])->name('bagian.destroy');
        
        // Sub Bagian
        Route::get('/sub-bagian', [KepegawaianController::class, 'subBagianIndex'])->name('sub-bagian.index');
        Route::get('/sub-bagian/create', [KepegawaianController::class, 'subBagianCreate'])->name('sub-bagian.create');
        Route::post('/sub-bagian', [KepegawaianController::class, 'subBagianStore'])->name('sub-bagian.store');
        Route::get('/sub-bagian/{subBagian}/edit', [KepegawaianController::class, 'subBagianEdit'])->name('sub-bagian.edit');
        Route::put('/sub-bagian/{subBagian}', [KepegawaianController::class, 'subBagianUpdate'])->name('sub-bagian.update');
        Route::delete('/sub-bagian/{subBagian}', [KepegawaianController::class, 'subBagianDestroy'])->name('sub-bagian.destroy');
        
        // Jabatan
        Route::get('/jabatan', [KepegawaianController::class, 'jabatanIndex'])->name('jabatan.index');
        Route::get('/jabatan/create', [KepegawaianController::class, 'jabatanCreate'])->name('jabatan.create');
        Route::post('/jabatan', [KepegawaianController::class, 'jabatanStore'])->name('jabatan.store');
        Route::get('/jabatan/{jabatan}/edit', [KepegawaianController::class, 'jabatanEdit'])->name('jabatan.edit');
        Route::put('/jabatan/{jabatan}', [KepegawaianController::class, 'jabatanUpdate'])->name('jabatan.update');
        Route::delete('/jabatan/{jabatan}', [KepegawaianController::class, 'jabatanDestroy'])->name('jabatan.destroy');

        // Pegawai
        Route::get('/pegawai', [KepegawaianController::class, 'pegawaiIndex'])->name('pegawai.index');
        Route::get('/pegawai/create', [KepegawaianController::class, 'pegawaiCreate'])->name('pegawai.create');
        Route::post('/pegawai', [KepegawaianController::class, 'pegawaiStore'])->name('pegawai.store');
        Route::get('/pegawai/{user}/edit', [KepegawaianController::class, 'pegawaiEdit'])->name('pegawai.edit');
        Route::put('/pegawai/{user}', [KepegawaianController::class, 'pegawaiUpdate'])->name('pegawai.update');
        Route::delete('/pegawai/{user}', [KepegawaianController::class, 'pegawaiDestroy'])->name('pegawai.destroy');
        Route::post('/pegawai/{user}/reset-password', [KepegawaianController::class, 'pegawaiResetPassword'])->name('pegawai.reset');
        Route::put('/pegawai/{user}/status', [KepegawaianController::class, 'pegawaiUpdateStatus'])->name('pegawai.status');
    });

    Route::middleware(['is_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
        Route::put('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset');
        
        Route::get('/audit-logs', [KonfigurasiController::class, 'auditLogs'])->name('audit-logs');
        Route::get('/system/backup', function () { return view('admin.backup'); })->name('backup.page');
        Route::post('/system/backup', [KonfigurasiController::class, 'backup'])->name('backup.action');
    });
});