@extends('layouts.app')

@section('title', 'Dashboard Eksekutif')

@section('content')
<style>
    .exec-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        transition: transform 0.2s ease;
    }
    .exec-card:hover { transform: translateY(-3px); }
    .exec-header {
        background: linear-gradient(135deg, #1F3864 0%, #2c4a85 100%);
        color: white;
        border-radius: 12px 12px 0 0;
        padding: 20px 24px;
    }
    .timeline-item {
        border-left: 3px solid #1F3864;
        padding-left: 20px;
        margin-bottom: 20px;
        position: relative;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 5px;
        width: 13px;
        height: 13px;
        border-radius: 50%;
        background: #1F3864;
        border: 2px solid white;
    }
    .date-badge {
        background: #f1f5f9;
        color: #1F3864;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 6px;
    }
</style>

<div class="container-fluid py-4">
    <!-- Header Elegan -->
    <div class="mb-5">
        <h2 class="fw-bold mb-1" style="color: #1F3864;">Selamat Datang, Bapak/Ibu Direksi</h2>
        <p class="text-muted mb-0">
            <i class="bi bi-calendar-event me-2"></i>
            {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
        </p>
    </div>

    <div class="row g-4">
        <!-- Kolom Kiri: Agenda Mendatang -->
        <div class="col-lg-7">
            <div class="card exec-card h-100">
                <div class="exec-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-check me-2"></i>Agenda Mendatang</h5>
                    <a href="{{ route('agenda.index') }}" class="btn btn-sm btn-light text-primary fw-semibold">Lihat Semua</a>
                </div>
                <div class="card-body p-4">
                    @forelse($upcomingAgendas as $agenda)
                        <div class="timeline-item">
                            <span class="date-badge">
                                <i class="bi bi-clock me-1"></i>
                                {{ \Carbon\Carbon::parse($agenda->tanggal_mulai)->locale('id')->isoFormat('D MMM Y') }} 
                                | {{ $agenda->jam_mulai ?? '-' }} WIB
                            </span>
                            <h6 class="fw-bold mb-1 text-dark">{{ $agenda->judul }}</h6>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-geo-alt me-1"></i> {{ $agenda->tempat ?? ($agenda->ruangan->nama_ruangan ?? 'Lokasi belum ditentukan') }}
                            </p>
                            <p class="small text-secondary mb-0">{{ Str::limit($agenda->acara ?? $agenda->keterangan, 100) }}</p>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-3 opacity-50"></i>
                            <p class="mb-0">Tidak ada agenda mendatang dalam waktu dekat.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Pengumuman Terbaru -->
        <div class="col-lg-5">
            <div class="card exec-card h-100">
                <div class="exec-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-megaphone me-2"></i>Pengumuman Terbaru</h5>
                    <a href="{{ route('pengumuman.index') }}" class="btn btn-sm btn-light text-success fw-semibold">Lihat Semua</a>
                </div>
                <div class="card-body p-4">
                    @forelse($latestAnnouncements as $pengumuman)
                        <div class="mb-4 pb-3 border-bottom border-light">
                            <span class="badge bg-success bg-opacity-10 text-success mb-2">
                                {{ $pengumuman->kategori ?? 'Umum' }}
                            </span>
                            <h6 class="fw-bold mb-2 text-dark">
                                <a href="{{ route('pengumuman.show', $pengumuman) }}" class="text-decoration-none text-dark stretched-link">
                                    {{ $pengumuman->judul }}
                                </a>
                            </h6>
                            <p class="small text-muted mb-2">{{ Str::limit($pengumuman->isi, 80) }}</p>
                            <small class="text-secondary" style="font-size: 0.75rem;">
                                <i class="bi bi-calendar3 me-1"></i> {{ \Carbon\Carbon::parse($pengumuman->tanggal_publish)->locale('id')->isoFormat('D MMM Y') }}
                            </small>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-megaphone fs-1 d-block mb-3 opacity-50"></i>
                            <p class="mb-0">Belum ada pengumuman yang diterbitkan.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection