@extends('layouts.app')

@section('title', 'Dashboard Kepegawaian')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    
    /* Hero Section */
    .hero-stats {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        border-radius: 16px;
        padding: 32px;
        color: white;
        margin-bottom: 24px;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    .hero-stats h1 { font-size: 1.75rem; font-weight: 700; margin-bottom: 4px; line-height: 1.2; }
    .hero-stats p { font-size: 1rem; opacity: 0.9; margin: 0; }
    
    .hero-badge {
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 16px 24px;
        text-align: center;
        min-width: 180px;
    }
    .hero-badge .label { font-size: 0.85rem; opacity: 0.9; margin-bottom: 4px; display: block; }
    .hero-badge .value { font-size: 2rem; font-weight: 700; }

    .hero-profile-img {
        width: 70px; height: 70px; object-fit: cover; flex-shrink: 0;
        transition: transform 0.3s ease;
    }
    .hero-profile-img:hover { transform: scale(1.05); }

    /* Metric Cards */
    .metric-card {
        background: white; border-radius: 12px; padding: 24px 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 2px solid #e2e8f0;
        transition: all 0.3s ease; text-decoration: none; color: inherit;
        height: 100%; display: flex; flex-direction: column;
        align-items: center; text-align: center; overflow: hidden;
    }
    .metric-card:hover {
        transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.12); border-color: #3b82f6;
    }
    .metric-icon-wrapper {
        width: 56px; height: 56px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 16px; flex-shrink: 0;
    }
    .metric-icon-wrapper i { font-size: 1.8rem; line-height: 1; }
    .metric-value { font-size: 2.25rem; font-weight: 700; color: #0f172a; line-height: 1; margin-bottom: 8px; }
    .metric-label { font-size: 0.9rem; color: #64748b; font-weight: 600; line-height: 1.3; word-wrap: break-word; }

    /* Quick Actions */
    .section-title {
        font-size: 1.25rem; font-weight: 700; color: #0f172a;
        margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
    }
    .quick-action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px; margin-bottom: 24px;
    }
    .quick-action-btn {
        background: white; border: 2px solid #e2e8f0; border-radius: 12px;
        padding: 20px 16px; text-align: center; text-decoration: none;
        color: #334155; transition: all 0.2s ease; cursor: pointer;
        display: flex; flex-direction: column; align-items: center; gap: 12px;
    }
    .quick-action-btn:hover {
        border-color: #3b82f6; background: #eff6ff; color: #1e3a8a; transform: translateY(-2px);
    }
    .quick-action-icon {
        width: 48px; height: 48px; background: #dbeafe; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; transition: all 0.2s; flex-shrink: 0;
    }
    .quick-action-btn:hover .quick-action-icon { background: #1e3a8a; color: white; }
    .quick-action-label { font-size: 0.9rem; font-weight: 600; }

    /* Calendar Container */
    .calendar-container {
        background: white; border-radius: 12px; border: 2px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05); overflow: hidden; height: 100%;
    }
    .calendar-header {
        background: #f8fafc; border-bottom: 2px solid #e2e8f0;
        padding: 16px 20px; display: flex; justify-content: space-between;
        align-items: center; flex-wrap: wrap; gap: 12px;
    }
    .calendar-title {
        font-size: 1.1rem; font-weight: 700; color: #1e3a8a;
        display: flex; align-items: center; gap: 8px;
    }
    .calendar-tabs { display: flex; gap: 8px; }
    .calendar-tab {
        padding: 8px 16px; border-radius: 8px; border: 2px solid #e2e8f0;
        background: white; color: #64748b; font-size: 0.85rem;
        font-weight: 600; cursor: pointer; transition: all 0.2s;
    }
    .calendar-tab.active { background: #1e3a8a; border-color: #1e3a8a; color: white; }
    .calendar-tab:hover:not(.active) { border-color: #3b82f6; color: #3b82f6; }
    .calendar-wrapper { padding: 20px; min-height: 400px; }

    /* Compact Table */
    .compact-table-wrapper {
        background: white; border-radius: 12px; border: 2px solid #e2e8f0;
        overflow: hidden; margin-bottom: 20px;
    }
    .compact-table-wrapper table { margin: 0; }
    .compact-table-wrapper thead { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
    .compact-table-wrapper th {
        font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
        color: #475569; padding: 12px 16px; letter-spacing: 0.5px; border: none;
    }
    .compact-table-wrapper td {
        padding: 14px 16px; border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem; vertical-align: middle;
    }
    .compact-table-wrapper tbody tr:hover { background: #f8fafc; }
    .compact-table-wrapper tbody tr:last-child td { border-bottom: none; }
    .employee-avatar {
        width: 32px; height: 32px; border-radius: 50%;
        object-fit: cover; border: 2px solid white;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    /* Status Badges */
    .status-badge {
        padding: 6px 12px; border-radius: 20px; font-size: 0.75rem;
        font-weight: 700; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;
    }
    .status-aktif { background: #dcfce7; color: #16a34a; }
    .status-menunggu { background: #fef3c7; color: #d97706; }

    /* Responsive */
    @media (max-width: 992px) {
        .hero-stats .row { flex-direction: column; text-align: center; }
        .hero-stats .d-flex { justify-content: center; text-align: left; }
        .hero-badge { margin-top: 20px; }
    }
    @media (max-width: 768px) {
        .calendar-header { flex-direction: column; align-items: flex-start; }
        .calendar-tabs { width: 100%; justify-content: center; }
    }
    @media (max-width: 576px) {
        .hero-stats { padding: 20px; }
        .metric-value { font-size: 1.75rem; }
    }

    /* FullCalendar Override */
    .fc-event { cursor: pointer; font-size: 0.85rem; }
    .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 600; color: #0f172a; }
    .fc .fc-button-primary { background-color: #1e3a8a !important; border-color: #1e3a8a !important; }
    .fc .fc-button-primary:hover { background-color: #3b82f6 !important; border-color: #3b82f6 !important; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    
    <!-- 1. HERO STATS -->
    <div class="hero-stats">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3 mb-3">
                    @php
                        $user = auth()->user();
                        $fotoUrl = $user->foto_profil 
                            ? asset('storage/' . $user->foto_profil) 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($user->nama_lengkap ?? $user->name ?? 'User') . '&background=000000&color=fff&size=120&bold=true';
                        $hour = date('H');
                        $sapaan = $hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : 'Selamat Sore');
                    @endphp
                    
                    <img src="{{ $fotoUrl }}" alt="Foto Profil" class="rounded-circle border border-3 border-white shadow hero-profile-img">
                    <div>
                        <div class="small opacity-75 mb-1">{{ $sapaan }},</div>
                        <h1 class="fw-bold mb-1">{{ $user->nama_lengkap ?? $user->name ?? 'Kepegawaian' }}</h1>
                        <div class="small opacity-75">
                            <i class="bi bi-building me-1"></i> {{ $user->bagian->nama_bagian ?? 'Bagian Kepegawaian' }}
                        </div>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                    <i class="bi bi-calendar3 me-2"></i>
                    {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="hero-badge d-inline-block">
                    <span class="label">Total Pegawai Aktif</span>
                    <div class="value">{{ $pegawaiAktif ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. METRIC CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card">
                <div class="metric-icon-wrapper" style="background: #dbeafe;"><i class="bi bi-people text-primary"></i></div>
                <div class="metric-value">{{ $totalPegawai ?? 0 }}</div>
                <div class="metric-label">Total Pegawai</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card">
                <div class="metric-icon-wrapper" style="background: #dcfce7;"><i class="bi bi-person-check text-success"></i></div>
                <div class="metric-value">{{ $pegawaiAktif ?? 0 }}</div>
                <div class="metric-label">Pegawai Aktif</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card">
                <div class="metric-icon-wrapper" style="background: #fef3c7;"><i class="bi bi-building text-warning"></i></div>
                <div class="metric-value">{{ $jumlahBagian ?? 0 }}</div>
                <div class="metric-label">Jumlah Bagian</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card">
                <div class="metric-icon-wrapper" style="background: #fee2e2;"><i class="bi bi-diagram-3 text-danger"></i></div>
                <div class="metric-value">{{ $jumlahSubBagian ?? 0 }}</div>
                <div class="metric-label">Jumlah Sub Bagian</div>
            </div>
        </div>
    </div>

    <!-- 3. QUICK ACTIONS -->
    <div class="section-title">Aksi Cepat</div>
    <div class="quick-action-grid mb-4">
        <a href="#" class="quick-action-btn" onclick="alert('Halaman Tambah Pegawai akan segera hadir!')">
            <div class="quick-action-icon"><i class="bi bi-person-plus"></i></div>
            <div class="quick-action-label">Tambah Pegawai</div>
        </a>
        <a href="{{ route('kepegawaian.bagian.index') }}" class="quick-action-btn">
            <div class="quick-action-icon"><i class="bi bi-building"></i></div>
            <div class="quick-action-label">Kelola Bagian</div>
        </a>
        <a href="{{ route('kepegawaian.sub-bagian.index') }}" class="quick-action-btn">
            <div class="quick-action-icon"><i class="bi bi-diagram-3"></i></div>
            <div class="quick-action-label">Kelola Sub Bagian</div>
        </a>
        <a href="{{ route('pengumuman.create') }}" class="quick-action-btn">
            <div class="quick-action-icon"><i class="bi bi-megaphone"></i></div>
            <div class="quick-action-label">Buat Pengumuman</div>
        </a>
    </div>

    <!-- 4. CALENDAR & RECENT ACTIVITIES -->
    <div class="row g-4">
        <!-- Left: Interactive Calendar -->
        <div class="col-lg-8">
            <div class="calendar-container">
                <div class="calendar-header">
                    <div class="calendar-title"><i class="bi bi-calendar-month"></i> Kalender Interaktif</div>
                    <div class="calendar-tabs">
                        <button class="calendar-tab active" onclick="switchCalendar('all', this)">Semua</button>
                        <button class="calendar-tab" onclick="switchCalendar('agenda', this)">Agenda</button>
                        <button class="calendar-tab" onclick="switchCalendar('pengumuman', this)">Pengumuman</button>
                    </div>
                </div>
                <div id="kalenderDisplay" class="calendar-wrapper"></div>
            </div>
        </div>

        <!-- Right: Informasi Terbaru (4 Bagian) -->
        <div class="col-lg-4">
            
            <!-- 1. AGENDA TERBARU -->
            <div class="section-title"><i class="bi bi-calendar-event text-primary"></i> Agenda Terbaru</div>
            <div class="compact-table-wrapper mb-4">
                <table class="table mb-0">
                    <thead><tr><th>Judul Agenda</th><th>Tanggal</th></tr></thead>
                    <tbody>
                        @forelse($agendaTerbaru ?? [] as $agenda)
                        <tr>
                            <td>
                                <div class="fw-semibold" style="font-size: 0.85rem;">{{ Str::limit($agenda->judul, 25) }}</div>
                                <small class="text-muted">{{ $agenda->creator->nama_lengkap ?? 'Unknown' }}</small>
                            </td>
                            <td class="text-end"><small class="text-muted">{{ \Carbon\Carbon::parse($agenda->tanggal_mulai)->format('d M Y') }}</small></td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-4"><i class="bi bi-calendar-x d-block mb-2" style="font-size: 2rem;"></i>Belum ada agenda</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 2. PENGUMUMAN TERBARU -->
            <div class="section-title"><i class="bi bi-megaphone text-danger"></i> Pengumuman Terbaru</div>
            <div class="compact-table-wrapper mb-4">
                <table class="table mb-0">
                    <thead><tr><th>Judul</th><th>Tanggal</th></tr></thead>
                    <tbody>
                        @forelse($pengumumanTerbaru ?? [] as $pengumuman)
                        <tr>
                            <td>
                                <div class="fw-semibold" style="font-size: 0.85rem;">{{ Str::limit($pengumuman->judul, 25) }}</div>
                                <span class="badge bg-light text-dark border" style="font-size: 0.7rem;">{{ $pengumuman->kategori ?? 'Umum' }}</span>
                            </td>
                            <td class="text-end"><small class="text-muted">{{ \Carbon\Carbon::parse($pengumuman->tanggal_publish)->format('d M Y') }}</small></td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-4"><i class="bi bi-megaphone d-block mb-2" style="font-size: 2rem;"></i>Belum ada pengumuman</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 3. AKTIVITAS TERBARU -->
            <div class="section-title"><i class="bi bi-activity text-success"></i> Aktivitas Terbaru</div>
            <div class="compact-table-wrapper mb-4">
                <table class="table mb-0">
                    <thead><tr><th>Aktivitas</th><th>Waktu</th></tr></thead>
                    <tbody>
                        @forelse($aktivitasTerbaru ?? [] as $aktivitas)
                        <tr>
                            <td>
                                <div class="fw-semibold" style="font-size: 0.85rem;">{{ Str::limit($aktivitas->description ?? 'Aktivitas sistem', 30) }}</div>
                                <small class="text-muted">{{ $aktivitas->user->nama_lengkap ?? $aktivitas->user->name ?? 'Sistem' }}</small>
                            </td>
                            <td class="text-end"><small class="text-muted">{{ \Carbon\Carbon::parse($aktivitas->created_at)->diffForHumans() }}</small></td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-4"><i class="bi bi-inbox d-block mb-2" style="font-size: 2rem;"></i>Belum ada aktivitas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 4. PEGAWAI TERBARU -->
            <div class="section-title"><i class="bi bi-person-lines-fill text-primary"></i> Pegawai Terbaru</div>
            <div class="compact-table-wrapper">
                <table class="table mb-0">
                    <thead><tr><th>Pegawai</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($pegawaiTerbaru ?? [] as $pegawai)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $pegawai->foto_profil ? asset('storage/'.$pegawai->foto_profil) : 'https://ui-avatars.com/api/?name='.urlencode($pegawai->nama_lengkap ?? $pegawai->name).'&background=1F3864&color=fff&size=36' }}" 
                                         class="employee-avatar" alt="Avatar">
                                    <div>
                                        <div class="fw-semibold" style="font-size: 0.85rem;">{{ Str::limit($pegawai->nama_lengkap ?? $pegawai->name, 15) }}</div>
                                        <small class="text-muted">{{ $pegawai->bagian->nama_bagian ?? '-' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                @if($pegawai->status == 'aktif')
                                    <span class="status-badge status-aktif">Aktif</span>
                                @elseif(in_array($pegawai->status, ['menunggu', 'pending']))
                                    <span class="status-badge status-menunggu">Menunggu</span>
                                @else
                                    <span class="status-badge" style="background: #f1f5f9; color: #475569;">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-4"><i class="bi bi-people d-block mb-2" style="font-size: 2rem;"></i>Belum ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
let mainCalendar;

function initCalendar() {
    mainCalendar = new FullCalendar.Calendar(document.getElementById('kalenderDisplay'), {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
        events: [],
        height: 'auto',
        firstDay: 1
    });
    mainCalendar.render();
    switchCalendar('all', document.querySelector('.calendar-tab'));
}

function switchCalendar(type, btn) {
    document.querySelectorAll('.calendar-tab').forEach(tab => tab.classList.remove('active'));
    if (btn) btn.classList.add('active');

    mainCalendar.removeAllEventSources();

    if (type === 'all') {
        mainCalendar.addEventSource('{{ route("api.kalender-all") }}');
    } else if (type === 'agenda') {
        mainCalendar.addEventSource('{{ route("api.kalender-agenda") }}');
    } else if (type === 'pengumuman') {
        mainCalendar.addEventSource('{{ route("api.kalender-pengumuman") }}');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initCalendar();
});
</script>
@endpush
@endsection