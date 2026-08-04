@extends('layouts.app')

@section('title', 'Pengumuman')

@push('styles')
<style>
    :root {
        --primary-color: #1F3864;
        --card-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    
    body {
        overflow-x: hidden;
    }
    
    .announcement-card {
        background: white;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
        margin-bottom: 20px;
        overflow: hidden;
        word-wrap: break-word;
    }
    
    .announcement-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    
    .announcement-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .announcement-body {
        padding: 20px;
        word-break: break-word;
    }
    
    .badge-prioritas {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }
    
    .badge-mendesak { background-color: #fee2e2; color: #991b1b; }
    .badge-penting { background-color: #fef3c7; color: #92400e; }
    .badge-normal { background-color: #dbeafe; color: #1e40af; }
    .badge-informasi { background-color: #dbeafe; color: #1e40af; }
    .badge-imbauan { background-color: #e0e7ff; color: #4f46e5; }
    .badge-maintenance { background-color: #f3f4f6; color: #6b7280; }
    
    .filter-btn {
        padding: 6px 14px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        background: white;
        color: #495057;
        font-weight: 500;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
        white-space: nowrap;
    }
    
    .filter-btn:hover, .filter-btn.active {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    /* Kalender Container */
    .calendar-container-custom {
        background: white;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        border: 1px solid #e9ecef;
        overflow: hidden;
        margin-bottom: 24px;
    }
    
    .calendar-container-custom .card-header {
        background: white;
        border-bottom: 2px solid #e9ecef;
        padding: 16px 20px;
    }
    
    .calendar-container-custom .card-body {
        padding: 20px;
    }
    
    #kalenderPengumumanIndex {
        min-height: 350px;
        max-height: 450px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .announcement-header { padding: 14px 16px; }
        .announcement-body { padding: 16px; }
        .filter-btn { font-size: 0.8rem; padding: 6px 10px; }
        #kalenderPengumumanIndex { min-height: 300px; max-height: 400px; }
    }
    
    @media (max-width: 576px) {
        .announcement-header { padding: 12px 14px; }
        .announcement-body { padding: 14px; }
    }
    
    /* FullCalendar Override */
    .fc { font-size: 0.85rem; }
    .fc-toolbar-title { font-size: 1.1rem !important; font-weight: 600; }
    .fc-daygrid-event { font-size: 0.75rem; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--primary-color);">Pengumuman</h2>
            <p class="text-muted mb-0">Kelola dan lihat seluruh pengumuman perusahaan.</p>
        </div>
        
        <!-- PERBAIKAN: Tombol ini sekarang bisa diakses oleh SEMUA role yang login -->
        <a href="{{ route('pengumuman.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>Buat Pengumuman
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Kalender Pengumuman -->
    <div class="calendar-container-custom">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-calendar-month me-2 text-success"></i>Kalender Pengumuman & Libur Nasional</h5>
        </div>
        <div class="card-body">
            <div id="kalenderPengumumanIndex"></div>
        </div>
    </div>

    <!-- Filter -->
    <div class="mb-4">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('pengumuman.index', ['filter' => 'semua']) }}" 
               class="filter-btn {{ ($filter ?? 'semua') === 'semua' ? 'active' : '' }}">
                Semua
            </a>
            <a href="{{ route('pengumuman.index', ['filter' => 'informasi']) }}" 
               class="filter-btn {{ ($filter ?? '') === 'informasi' ? 'active' : '' }}">
                Informasi
            </a>
            <a href="{{ route('pengumuman.index', ['filter' => 'imbauan']) }}" 
               class="filter-btn {{ ($filter ?? '') === 'imbauan' ? 'active' : '' }}">
                Imbauan
            </a>
            <a href="{{ route('pengumuman.index', ['filter' => 'penting']) }}" 
               class="filter-btn {{ ($filter ?? '') === 'penting' ? 'active' : '' }}">
                Penting
            </a>
            <a href="{{ route('pengumuman.index', ['filter' => 'maintenance']) }}" 
               class="filter-btn {{ ($filter ?? '') === 'maintenance' ? 'active' : '' }}">
                Maintenance
            </a>
        </div>
    </div>

    <!-- Daftar Pengumuman -->
    @forelse($pengumumans as $pengumuman)
        @php
            $tglSelesai = $pengumuman->tanggal_selesai ?? $pengumuman->tanggal_berakhir ?? null;
            $isAktif = is_null($tglSelesai) || \Carbon\Carbon::parse($tglSelesai)->endOfDay() >= now();
            $tglMulai = $pengumuman->tanggal_mulai ?? $pengumuman->tanggal_publish ?? $pengumuman->created_at;
            
            $kategoriLower = strtolower($pengumuman->kategori ?? $pengumuman->prioritas ?? 'normal');
            $badgeClass = 'badge-normal';
            if ($kategoriLower === 'mendesak') $badgeClass = 'badge-mendesak';
            elseif ($kategoriLower === 'penting') $badgeClass = 'badge-penting';
            elseif ($kategoriLower === 'informasi') $badgeClass = 'badge-informasi';
            elseif ($kategoriLower === 'imbauan') $badgeClass = 'badge-imbauan';
            elseif ($kategoriLower === 'maintenance') $badgeClass = 'badge-maintenance';
            
            // Cek apakah user adalah pembuat pengumuman ini (untuk hak edit/hapus)
            $isCreator = auth()->id() == ($pengumuman->dibuat_oleh ?? $pengumuman->created_by);
            $isAdmin = auth()->user()->hasRole(['Administrator', 'IT Administrator', 'Sekretariat', 'Kepegawaian']);
        @endphp
        
        <div class="announcement-card">
            <div class="announcement-header">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div class="flex-grow-1" style="min-width: 0;">
                        <h6 class="fw-bold mb-2 text-break">{{ $pengumuman->judul }}</h6>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            @if(isset($pengumuman->kategori) || isset($pengumuman->prioritas))
                                <span class="badge-prioritas {{ $badgeClass }}">
                                    {{ ucfirst($pengumuman->kategori ?? $pengumuman->prioritas ?? 'Normal') }}
                                </span>
                            @endif

                            <span class="text-muted small">
                                <i class="bi bi-calendar3 me-1"></i>
                                {{ \Carbon\Carbon::parse($tglMulai)->isoFormat('D MMMM Y') }}
                                @if($tglSelesai)
                                    - {{ \Carbon\Carbon::parse($tglSelesai)->isoFormat('D MMMM Y') }}
                                @endif
                            </span>

                            @if($isAktif)
                                <span class="badge bg-success badge-sm">Aktif</span>
                            @else
                                <span class="badge bg-secondary badge-sm">Expired</span>
                            @endif
                        </div>
                    </div>
                    <div class="dropdown flex-shrink-0">
                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('pengumuman.show', $pengumuman) }}">
                                    <i class="bi bi-eye me-2"></i>Lihat Detail
                                </a>
                            </li>
                            
                            {{-- PERBAIKAN: Edit & Hapus bisa dilakukan oleh Admin ATAU oleh Pembuatnya --}}
                            @if($isAdmin || $isCreator)
                                <li>
                                    <a class="dropdown-item" href="{{ route('pengumuman.edit', $pengumuman) }}">
                                        <i class="bi bi-pencil me-2"></i>Edit
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('pengumuman.destroy', $pengumuman) }}" method="POST" 
                                          onsubmit="return confirm('Yakin ingin menghapus pengumuman ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-trash me-2"></i>Hapus
                                        </button>
                                    </form>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            <div class="announcement-body">
                <p class="mb-3 text-break small">{{ Str::limit(strip_tags($pengumuman->isi), 150) }}</p>
                
                {{-- Preview Lampiran --}}
                @if($pengumuman->lampiran)
                    @php
                        $filePath = asset('storage/' . $pengumuman->lampiran);
                        $ext = strtolower(pathinfo($pengumuman->lampiran, PATHINFO_EXTENSION));
                    @endphp
                    @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                        <img src="{{ $filePath }}" class="img-fluid rounded mb-2" style="max-height: 200px;">
                        <div>
                            <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-zoom-in"></i> Lihat</a>
                            <a href="{{ $filePath }}" download class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i> Unduh</a>
                        </div>
                    @elseif($ext === 'pdf')
                        <div class="ratio ratio-16x9 border rounded mb-2">
                            <iframe src="{{ $filePath }}" title="Preview PDF"></iframe>
                        </div>
                        <div>
                            <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-outline-danger me-1"><i class="bi bi-file-earmark-pdf"></i> Buka PDF</a>
                            <a href="{{ $filePath }}" download class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i> Unduh</a>
                        </div>
                    @else
                        <div class="d-flex align-items-center p-2 bg-light border rounded mb-2">
                            <i class="bi bi-file-earmark-text fs-4 text-primary me-2"></i>
                            <div class="flex-grow-1 text-truncate">
                                <small class="fw-bold">{{ basename($pengumuman->lampiran) }}</small>
                            </div>
                            <a href="{{ $filePath }}" download class="btn btn-sm btn-outline-primary ms-2"><i class="bi bi-download"></i></a>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-5">
            <i class="bi bi-megaphone text-muted" style="font-size: 3rem;"></i>
            <h6 class="mt-3 text-muted">Belum Ada Pengumuman</h6>
            <p class="text-muted small">Jadilah yang pertama membuat pengumuman.</p>
            <a href="{{ route('pengumuman.create') }}" class="btn btn-primary mt-2">
                <i class="bi bi-plus-lg me-2"></i>Buat Pengumuman Sekarang
            </a>
        </div>
    @endforelse

    <div class="mt-4 d-flex justify-content-center">
        {{ $pengumumans->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('kalenderPengumumanIndex');
    if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: { 
                left: 'prev,next today', 
                center: 'title', 
                right: 'dayGridMonth,listWeek' 
            },
            events: '{{ route("api.kalender-pengumuman") }}',
            height: 'auto',
            firstDay: 1,
            eventClick: function(info) {
                if (info.event.url) {
                    info.jsEvent.preventDefault();
                    window.location.href = info.event.url;
                }
            },
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                list: 'Daftar'
            }
        });
        calendar.render();
    }
});
</script>
@endpush