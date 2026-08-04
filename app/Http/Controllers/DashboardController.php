<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\PeminjamanRuangan;
use App\Models\Pengumuman;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Bagian;
use App\Models\SubBagian;
use App\Models\Ruangan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private function isAdministrator($user)
    {
        if (!$user->bagian || !$user->subBagian) {
            return false;
        }
        $namaBagian = strtolower(trim($user->bagian->nama_bagian));
        $namaSubBagian = strtolower(trim($user->subBagian->nama_sub_bagian));
        return (str_contains($namaBagian, 'litbang') && str_contains($namaSubBagian, 'pengembangan teknologi informatika'));
    }

    private function isSekretariat($user)
    {
        $bagianSekretariat = ['bagian sekretariat', 'sub bagian umum & kepegawaian', 'sub bagian keuangan', 'sub bagian humas dan protokol'];
        $namaBagianUser = strtolower(trim($user->bagian->nama_bagian ?? ''));
        return $user->hasRole('Sekretariat') || in_array($namaBagianUser, $bagianSekretariat);
    }

    public function index()
    {
        $user = auth()->user();

        if ($this->isAdministrator($user) || $user->hasRole('IT Administrator')) {
            return $this->dashboardAdmin($user);
        }
        if ($user->hasRole('Kepegawaian')) {
            return $this->dashboardKepegawaian($user);
        }
        if ($this->isSekretariat($user)) { 
            return $this->dashboardSekretariat($user);
        } 
        
        return $this->dashboardPegawai($user);
    }

    // ==========================================
    // 1. DASHBOARD PEGAWAI
    // ==========================================
    private function dashboardPegawai($user)
    {
        $userNip = (string) $user->nip;
        $userBagianNama = $user->bagian->nama_bagian ?? '';
        $userSubBagianNama = $user->subBagian->nama_sub_bagian ?? '';
        $userBagianId = $user->bagian->id ?? null;
        $userSubBagianId = $user->subBagian->id ?? null;

        // ✅ PERBAIKAN: Query Agenda - Tampilkan jika user adalah pembuat ATAU bagiannya diundang
        $agendaQuery = Agenda::with(['creator', 'ruangan'])
            ->where(function ($q) use ($userNip, $userBagianNama, $userSubBagianNama) {
                $q->where('created_by', $userNip); // Selalu tampilkan agenda yang dibuat user
                if (!empty($userBagianNama)) {
                    $q->orWhereJsonContains('peserta', $userBagianNama);
                }
                if (!empty($userSubBagianNama)) {
                    $q->orWhereJsonContains('peserta', $userSubBagianNama);
                }
            });

        $agendaTerbaru = (clone $agendaQuery)->orderBy('tanggal_mulai', 'desc')->limit(5)->get();
        $agendaMendatang = (clone $agendaQuery)
            ->whereDate('tanggal_mulai', '>=', today())
            ->whereIn('status', ['submitted', 'approved', 'berlangsung'])
            ->orderBy('tanggal_mulai', 'asc')
            ->limit(3)
            ->get();

        $totalAgendaHariIni = (clone $agendaQuery)
            ->whereDate('tanggal_mulai', today())
            ->whereIn('status', ['submitted', 'approved', 'berlangsung'])
            ->count();

        // ✅ PERBAIKAN: Kalender Agenda - Ambil SEMUA agenda bulan ini yang relevan
        $kalenderAgendas = (clone $agendaQuery)
            ->whereMonth('tanggal_mulai', now()->month)
            ->whereYear('tanggal_mulai', now()->year)
            ->orderBy('tanggal_mulai', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        $kalenderKegiatan = $kalenderAgendas;

        // Pengumuman
        $pengumumanQuery = Pengumuman::where('status', 'publish')
            ->whereDate('tanggal_publish', '<=', now())
            ->where(function ($q) {
                $q->whereNull('tanggal_berakhir')
                  ->orWhereDate('tanggal_berakhir', '>=', now());
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

        $pengumumanTerbaru = (clone $pengumumanQuery)->orderBy('created_at', 'desc')->limit(5)->get();
        $kalenderPengumumans = (clone $pengumumanQuery)
            ->whereMonth('tanggal_publish', now()->month)
            ->whereYear('tanggal_publish', now()->year)
            ->get();

        // Peminjaman
        $peminjamanSaya = PeminjamanRuangan::with(['ruangan', 'agenda', 'pemohon'])
            ->where('user_id', $userNip)
            ->orderBy('tanggal_pemakaian', 'desc')
            ->limit(5)
            ->get();

        $permohonanSaya = PeminjamanRuangan::where('user_id', $userNip)
            ->whereIn('status_persetujuan', ['menunggu', 'disetujui'])->count();
            
        $permohonanBaru = PeminjamanRuangan::where('user_id', $userNip)
            ->whereDate('created_at', today())->count();

        $kalenderRuangan = PeminjamanRuangan::with(['ruangan', 'agenda', 'pemohon'])
            ->whereMonth('tanggal_pemakaian', now()->month)
            ->whereYear('tanggal_pemakaian', now()->year)
            ->orderBy('tanggal_pemakaian', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        $ruanganDigunakan = PeminjamanRuangan::whereDate('tanggal_pemakaian', today())
            ->where('status_persetujuan', 'disetujui')
            ->distinct('ruangan_id')
            ->count('ruangan_id');

        $notifikasiBaru = $user->unreadNotifications()->count();

        return view('dashboard.pegawai', compact(
            'totalAgendaHariIni', 'permohonanSaya', 'permohonanBaru', 'notifikasiBaru', 'ruanganDigunakan',
            'agendaTerbaru',        
            'pengumumanTerbaru',    
            'peminjamanSaya',        
            'agendaMendatang',      
            'kalenderAgendas', 'kalenderKegiatan', 'kalenderRuangan', 'kalenderPengumumans'
        ));
    }

    // ==========================================
    // 2. DASHBOARD SEKRETARIAT (Bisa lihat SEMUA)
    // ==========================================
    private function dashboardSekretariat($user)
    {
        $agendaHariIni = Agenda::with(['creator', 'ruangan'])
            ->whereDate('tanggal_mulai', today())
            ->whereIn('status', ['submitted', 'approved', 'berlangsung'])
            ->orderBy('tanggal_mulai', 'asc')
            ->get();
            
        $peminjamanMenungguList = PeminjamanRuangan::with(['pemohon', 'ruangan'])
            ->where('status_persetujuan', 'menunggu')
            ->orderBy('created_at', 'desc')
            ->get();

        $agendaAktif = Agenda::whereDate('tanggal_mulai', '>=', today())
            ->whereIn('status', ['submitted', 'approved', 'berlangsung'])
            ->count();
            
        $ruanganDigunakan = PeminjamanRuangan::whereDate('tanggal_pemakaian', today())
            ->where('status_persetujuan', 'disetujui')
            ->distinct('ruangan_id')
            ->count('ruangan_id');

        $pengumumanQuery = Pengumuman::where('status', 'publish')
            ->whereDate('tanggal_publish', '<=', now())
            ->where(function($q) {
                $q->whereNull('tanggal_berakhir')
                  ->orWhereDate('tanggal_berakhir', '>=', now());
            });

        $pengumumanTerbaru = (clone $pengumumanQuery)->orderBy('created_at', 'desc')->limit(5)->get();
        
        $kalenderPengumumans = (clone $pengumumanQuery)
            ->whereMonth('tanggal_publish', now()->month)
            ->whereYear('tanggal_publish', now()->year)
            ->get();

        $agendaSaya = Agenda::with(['creator', 'ruangan'])
            ->where('created_by', $user->nip)
            ->whereIn('status', ['submitted', 'approved', 'berlangsung'])
            ->orderBy('tanggal_mulai', 'desc')
            ->limit(5)
            ->get();

        $kalenderAgendas = Agenda::with(['creator', 'ruangan'])->whereMonth('tanggal_mulai', now()->month)->whereYear('tanggal_mulai', now()->year)->get();
        $kalenderRuangan = PeminjamanRuangan::with(['ruangan', 'pemohon'])->whereMonth('tanggal_pemakaian', now()->month)->whereYear('tanggal_pemakaian', now()->year)->get();

        $totalAgendaHariIni = $agendaHariIni->count();
        $permohonanBaru = $peminjamanMenungguList->count();

        return view('dashboard.sekretariat', compact(
            'agendaHariIni', 'totalAgendaHariIni', 'permohonanBaru',
            'peminjamanMenungguList', 'agendaAktif', 'ruanganDigunakan',
            'pengumumanTerbaru', 'agendaSaya', 'kalenderAgendas', 'kalenderRuangan', 'kalenderPengumumans'
        ));
    }

    // ==========================================
    // 3. DASHBOARD KEPEGAWAIAN
    // ==========================================
    private function dashboardKepegawaian($user)
    {
        $kalenderAgendas = Agenda::with(['creator', 'ruangan'])->whereMonth('tanggal_mulai', now()->month)->whereYear('tanggal_mulai', now()->year)->get();
        $kalenderRuangan = PeminjamanRuangan::with(['ruangan', 'pemohon'])->whereMonth('tanggal_pemakaian', now()->month)->whereYear('tanggal_pemakaian', now()->year)->get();
        
        $kalenderPengumumans = Pengumuman::where('status', 'publish')
            ->whereMonth('tanggal_publish', now()->month)
            ->whereYear('tanggal_publish', now()->year)
            ->get();

        $data = [
            'totalPegawai' => User::count(),
            'pegawaiAktif' => User::where('status', 'aktif')->count(),
            'pegawaiMenunggu' => User::whereIn('status', ['menunggu', 'pending'])->count(),
            'jumlahBagian' => Bagian::count(),
            'jumlahSubBagian' => SubBagian::count(),
            'aktivitasTerbaru' => AuditLog::with('user')->latest()->limit(5)->get(),
            'pegawaiTerbaru' => User::with(['bagian', 'subBagian'])->latest()->limit(5)->get(),
            'agendaTerbaru' => Agenda::with('creator')->latest('tanggal_mulai')->take(3)->get(),
            'pengumumanTerbaru' => Pengumuman::where('status', 'publish')->latest('tanggal_publish')->take(3)->get(),
            'kalenderAgendas' => $kalenderAgendas,
            'kalenderRuangan' => $kalenderRuangan,
            'kalenderPengumumans' => $kalenderPengumumans,
        ];
        return view('dashboard.kepegawaian', $data);
    }

    // ==========================================
    // 4. DASHBOARD ADMIN
    // ==========================================
    private function dashboardAdmin($user)
    {
        $kalenderAgendas = Agenda::with(['creator', 'ruangan'])->whereMonth('tanggal_mulai', now()->month)->whereYear('tanggal_mulai', now()->year)->get();
        $kalenderRuangan = PeminjamanRuangan::with(['ruangan', 'pemohon'])->whereMonth('tanggal_pemakaian', now()->month)->whereYear('tanggal_pemakaian', now()->year)->get();
        
        $kalenderPengumumans = Pengumuman::where('status', 'publish')
            ->whereMonth('tanggal_publish', now()->month)
            ->whereYear('tanggal_publish', now()->year)
            ->get();

        $data = [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('status', 'aktif')->count(),
            'totalAgendas' => Agenda::count(),
            'totalRuangan' => Ruangan::where('status', 'aktif')->count(),
            'systemStatus' => 'Online',
            'databaseStatus' => 'Connected',
            'recentActivities' => AuditLog::with('user')->latest()->limit(10)->get(),
            'kalenderAgendas' => $kalenderAgendas,
            'kalenderRuangan' => $kalenderRuangan,
            'kalenderPengumumans' => $kalenderPengumumans,
        ];
        return view('dashboard.admin', $data);
    }
}