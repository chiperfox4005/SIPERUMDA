@extends('layouts.app')

@section('title', 'Dashboard Administrator')

@push('styles')
<style>
    :root {
        --admin-primary: #0078D4;
        --admin-bg: #f3f2f1;
        --admin-card-bg: #ffffff;
        --admin-text: #323130;
        --admin-text-secondary: #605e5c;
    }
    
    body {
        background-color: var(--admin-bg);
        font-family: 'Segoe UI', 'Inter', sans-serif;
        color: var(--admin-text);
    }

    .admin-card {
        background: var(--admin-card-bg);
        border-radius: 8px;
        border: 1px solid #edebe9;
        box-shadow: 0 1.6px 3.6px 0 rgba(0,0,0,0.05), 0 0.3px 0.9px 0 rgba(0,0,0,0.05);
        transition: box-shadow 0.2s ease;
        margin-bottom: 24px;
    }
    .admin-card:hover {
        box-shadow: 0 3.2px 7.2px 0 rgba(0,0,0,0.08), 0 0.6px 1.8px 0 rgba(0,0,0,0.08);
    }

    .stat-card-admin {
        padding: 24px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }
    .stat-icon-admin {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        background-color: #eff6fc;
        color: var(--admin-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    .stat-value-admin {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--admin-text);
        margin: 0;
        line-height: 1.2;
    }
    .stat-label-admin {
        font-size: 0.875rem;
        color: var(--admin-text-secondary);
        margin: 4px 0 0 0;
    }

    .admin-section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--admin-text);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 20px 24px 0 24px;
    }

    .activity-item-admin {
        padding: 12px 24px;
        border-bottom: 1px solid #edebe9;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .activity-item-admin:last-child { border-bottom: none; }
    .activity-user { font-weight: 600; font-size: 0.9rem; color: var(--admin-text); }
    .activity-action { font-size: 0.85rem; color: var(--admin-text-secondary); }
    .activity-time { font-size: 0.75rem; color: #a19f9d; white-space: nowrap; }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .status-online { background-color: #dff6dd; color: #107c10; }
    
    .quick-action-admin {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 6px;
        background: white;
        border: 1px solid #edebe9;
        text-decoration: none;
        color: var(--admin-primary);
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.2s;
        margin-bottom: 8px;
    }
    .quick-action-admin:hover {
        background-color: #eff6fc;
        border-color: var(--admin-primary);
    }
    .quick-action-admin i { font-size: 1.1rem; }
</style>
@endpush

@section('content')
<div class="p-4 p-lg-5 max-w-[1600px] mx-auto">
    
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.75rem; color: var(--admin-text);">Pusat Administrasi Sistem</h1>
            <p class="text-muted mb-0">Monitoring, konfigurasi, dan pemeliharaan sistem SIPERUMDA.</p>
        </div>
        <div>
            <span class="status-badge status-online">
                <i class="bi bi-check-circle-fill"></i> Sistem Online
            </span>
        </div>
    </div>

    <!-- 1. SYSTEM METRICS -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="admin-card stat-card-admin">
                <div class="stat-icon-admin"><i class="bi bi-people"></i></div>
                <div>
                    <h3 class="stat-value-admin">{{ $totalUsers ?? 0 }}</h3>
                    <p class="stat-label-admin">Total Pengguna</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="admin-card stat-card-admin">
                <div class="stat-icon-admin"><i class="bi bi-person-check"></i></div>
                <div>
                    <h3 class="stat-value-admin">{{ $activeUsers ?? 0 }}</h3>
                    <p class="stat-label-admin">Pengguna Aktif</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="admin-card stat-card-admin">
                <div class="stat-icon-admin"><i class="bi bi-calendar-event"></i></div>
                <div>
                    <h3 class="stat-value-admin">{{ $agendaThisMonth ?? 0 }}</h3>
                    <p class="stat-label-admin">Agenda Bulan Ini</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="admin-card stat-card-admin">
                <div class="stat-icon-admin"><i class="bi bi-door-open"></i></div>
                <div>
                    <h3 class="stat-value-admin">{{ $roomReservations ?? 0 }}</h3>
                    <p class="stat-label-admin">Peminjaman Ruangan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. MAIN DASHBOARD GRID -->
    <div class="row g-4">
        <!-- LEFT COLUMN (Activities & Quick Actions) -->
        <div class="col-lg-8">
            
            <!-- Recent Activities -->
            <div class="admin-card">
                <div class="admin-section-title">
                    <i class="bi bi-journal-text text-primary"></i> Audit Log Aktivitas Terbaru
                </div>
                <div class="pb-3">
                    @forelse($recentActivities ?? [] as $activity)
                    <div class="activity-item-admin">
                        <div>
                            <div class="activity-user">{{ $activity->user->nama_lengkap ?? 'Sistem' }}</div>
                            <div class="activity-action">{{ $activity->description ?? 'Melakukan aktivitas sistem' }}</div>
                        </div>
                        <div class="activity-time">{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</div>
                    </div>
                    @empty
                    <div class="activity-item-admin">
                        <div class="activity-action text-muted">Belum ada aktivitas tercatat.</div>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- System Health -->
            <div class="admin-card p-4">
                <div class="admin-section-title mb-3" style="padding: 0;">
                    <i class="bi bi-activity text-primary"></i> Kesehatan Sistem
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                            <span class="fw-medium">Status Database</span>
                            <span class="status-badge status-online"><i class="bi bi-check-circle-fill"></i> {{ $databaseStatus ?? 'Connected' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                            <span class="fw-medium">Versi Aplikasi</span>
                            <span class="badge bg-secondary">v1.0.4</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN (Quick Actions) -->
        <div class="col-lg-4">
            <div class="admin-card p-4">
                <div class="admin-section-title mb-3" style="padding: 0;">
                    <i class="bi bi-lightning-charge text-primary"></i> Tindakan Cepat
                </div>
                
                <a href="#" class="quick-action-admin">
                    <i class="bi bi-person-plus"></i> Tambah Pengguna Baru
                </a>
                <a href="#" class="quick-action-admin">
                    <i class="bi bi-shield-lock"></i> Kelola Hak Akses
                </a>
                <a href="#" class="quick-action-admin">
                    <i class="bi bi-database-gear"></i> Master Data
                </a>
                <a href="#" class="quick-action-admin">
                    <i class="bi bi-hdd-network"></i> Backup Database
                </a>
                <a href="#" class="quick-action-admin">
                    <i class="bi bi-gear-wide-connected"></i> Pengaturan Sistem
                </a>
            </div>

            <!-- Admin Profile Snippet -->
            <div class="admin-card p-4 text-center">
                <img src="{{ auth()->user()->foto_profil ? asset('storage/'.auth()->user()->foto_profil) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->nama_lengkap).'&background=0078D4&color=fff&size=64' }}" 
                     class="rounded-circle mb-3" width="64" height="64" alt="Profile">
                <h5 class="fw-bold mb-1">{{ auth()->user()->nama_lengkap }}</h5>
                <p class="text-muted small mb-2">{{ auth()->user()->nip }}</p>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">Administrator Sistem</span>
                <div class="mt-3 text-start small text-muted">
                    <div><i class="bi bi-building me-2"></i> Litbang</div>
                    <div><i class="bi bi-diagram-3 me-2"></i> Pengembangan Teknologi Informatika (PTI)</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection