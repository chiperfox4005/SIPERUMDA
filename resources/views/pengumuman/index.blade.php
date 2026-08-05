@extends('layouts.app')

@section('title', 'Pengumuman')

@push('styles')
<style>
    :root {
        --primary-color: #1F3864;
        --card-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    
    body { overflow-x: hidden; }
    
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
    
    .announcement-body { padding: 20px; word-break: break-word; }
    
    .badge-prioritas {
        padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; white-space: nowrap;
    }
    .badge-mendesak { background-color: #fee2e2; color: #991b1b; }
    .badge-penting { background-color: #fef3c7; color: #92400e; }
    .badge-normal { background-color: #dbeafe; color: #1e40af; }
    .badge-informasi { background-color: #dbeafe; color: #1e40af; }
    .badge-imbauan { background-color: #e0e7ff; color: #4f46e5; }
    .badge-maintenance { background-color: #f3f4f6; color: #6b7280; }

    .badge-status {
        padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .status-draft { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .status-aktif { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .status-expired { background-color: #f8f9fa; color: #6c757d; border: 1px solid #e2e3e5; }
    
    .filter-btn {
        padding: 6px 14px; border-radius: 8px; border: 1px solid #e9ecef; background: white;
        color: #495057; font-weight: 500; font-size: 0.85rem; transition: all 0.2s ease; text-decoration: none; display: inline-block; white-space: nowrap; cursor: pointer;
    }
    .filter-btn:hover, .filter-btn.active { background-color: var(--primary-color); color: white; border-color: var(--primary-color); }

    .action-icons {
        display: flex;
        gap: 4px;
        align-items: center;
    }
    
    .action-icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.9rem;
        text-decoration: none;
    }
    
    .action-icon:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    .btn-edit { background-color: #fef3c7; color: #92400e; }
    .btn-edit:hover { background-color: #fde68a; }
    
    .btn-status { background-color: #dbeafe; color: #1e40af; }
    .btn-status:hover { background-color: #bfdbfe; }
    
    .btn-delete { background-color: #fee2e2; color: #991b1b; }
    .btn-delete:hover { background-color: #fecaca; }

    .calendar-container-custom {
        background: white; border-radius: 12px; box-shadow: var(--card-shadow); border: 1px solid #e9ecef; overflow: hidden; margin-bottom: 24px;
    }
    .calendar-container-custom .card-header { background: white; border-bottom: 2px solid #e9ecef; padding: 16px 20px; }
    .calendar-container-custom .card-body { padding: 20px; }
    #kalenderPengumumanIndex { min-height: 350px; max-height: 450px; }
    
    @media (max-width: 768px) {
        .announcement-header { padding: 14px 16px; }
        .announcement-body { padding: 16px; }
        .filter-btn { font-size: 0.8rem; padding: 6px 10px; }
        #kalenderPengumumanIndex { min-height: 300px; max-height: 400px; }
        .action-icons { flex-direction: column; }
        .action-icon { width: 28px; height: 28px; font-size: 0.8rem; }
    }
    
    .fc { font-size: 0.85rem; }
    .fc-toolbar-title { font-size: 1.1rem !important; font-weight: 600; }
    .fc-daygrid-event { font-size: 0.75rem; white-space: normal !important; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--primary-color);">Pengumuman</h2>
            <p class="text-muted mb-0">Kelola dan lihat seluruh pengumuman perusahaan.</p>
        </div>
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

    <div class="calendar-container-custom">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-calendar-month me-2 text-success"></i>Kalender Pengumuman & Libur Nasional</h5>
        </div>
        <div class="card-body">
            <div id="kalenderPengumumanIndex"></div>
        </div>
    </div>

    <div class="mb-4">
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="filter-btn active" data-filter="all">Semua</button>
            <button type="button" class="filter-btn" data-filter="informasi">Informasi</button>
            <button type="button" class="filter-btn" data-filter="imbauan">Imbauan</button>
            <button type="button" class="filter-btn" data-filter="penting">Penting</button>
            <button type="button" class="filter-btn" data-filter="maintenance">Maintenance</button>
            <button type="button" class="filter-btn" data-filter="draft">Draft</button>
            <button type="button" class="filter-btn" data-filter="aktif">Aktif</button>
            <button type="button" class="filter-btn" data-filter="expired">Expired</button>
        </div>
    </div>

    @forelse($pengumumans as $pengumuman)
        @php
            $tglSelesai = $pengumuman->tanggal_selesai ?? $pengumuman->tanggal_berakhir ?? null;
            $isAktif = is_null($tglSelesai) || \Carbon\Carbon::parse($tglSelesai)->endOfDay() >= now();
            $tglMulai = $pengumuman->tanggal_mulai ?? $pengumuman->tanggal_publish ?? $pengumuman->created_at;
            
            $statusDisplay = 'status-aktif';
            $statusText = 'Aktif';
            if (isset($pengumuman->status) && $pengumuman->status === 'draft') {
                $statusDisplay = 'status-draft';
                $statusText = 'Draft';
            } elseif (!$isAktif) {
                $statusDisplay = 'status-expired';
                $statusText = 'Expired';
            }

            $kategoriLower = strtolower($pengumuman->kategori ?? $pengumuman->prioritas ?? 'normal');
            $badgeClass = 'badge-normal';
            if ($kategoriLower === 'mendesak') $badgeClass = 'badge-mendesak';
            elseif ($kategoriLower === 'penting') $badgeClass = 'badge-penting';
            elseif ($kategoriLower === 'informasi') $badgeClass = 'badge-informasi';
            elseif ($kategoriLower === 'imbauan') $badgeClass = 'badge-imbauan';
            elseif ($kategoriLower === 'maintenance') $badgeClass = 'badge-maintenance';
            
            // ✅ ATURAN BARU: HANYA PEMBUAT YANG BOLEH KELOLA. TIDAK ADA PERAN KHUSUS.
            $currentUserId = (string) auth()->id();
            $creatorId = (string) ($pengumuman->dibuat_oleh ?? $pengumuman->user_id ?? $pengumuman->created_by ?? '0');
            
            $canManage = $currentUserId === $creatorId;
        @endphp
        
        <div class="announcement-card" 
             data-status="{{ strtolower($statusText) }}" 
             data-kategori="{{ $kategoriLower }}">
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

                            <span class="badge-status {{ $statusDisplay }}">
                                @if($statusText === 'Draft') <i class="bi bi-pencil-fill me-1"></i> @endif
                                {{ $statusText }}
                            </span>

                            <span class="text-muted small">
                                <i class="bi bi-calendar3 me-1"></i>
                                {{ \Carbon\Carbon::parse($tglMulai)->isoFormat('D MMMM Y') }}
                                @if($tglSelesai)
                                    - {{ \Carbon\Carbon::parse($tglSelesai)->isoFormat('D MMMM Y') }}
                                @endif
                            </span>
                        </div>
                    </div>
                    
                    {{-- ✅ TOMBOL AKSI HANYA MUNCUL JIKA $canManage BENAR (Hanya Pembuat) --}}
                    @if($canManage)
                    <div class="action-icons flex-shrink-0">
                        <!-- Tombol Edit -->
                        <a href="{{ route('pengumuman.edit', $pengumuman) }}" 
                           class="action-icon btn-edit" 
                           title="Edit Pengumuman"
                           data-bs-toggle="tooltip">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        
                        <!-- Tombol Ubah Status -->
                        <button type="button" 
                                class="action-icon btn-status" 
                                title="Ubah Status"
                                data-bs-toggle="modal"
                                data-bs-target="#statusModal{{ $pengumuman->id }}"
                                data-bs-toggle="tooltip">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                        
                        <!-- Tombol Hapus -->
                        <form action="{{ route('pengumuman.destroy', $pengumuman) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Yakin ingin menghapus pengumuman ini secara permanen?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="action-icon btn-delete" 
                                    title="Hapus Permanen"
                                    data-bs-toggle="tooltip">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            <div class="announcement-body">
                <p class="mb-3 text-break small">{{ Str::limit(strip_tags($pengumuman->isi), 150) }}</p>
                
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

        @if($canManage)
        <div class="modal fade" id="statusModal{{ $pengumuman->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('pengumuman.update-status', $pengumuman) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title fs-6"><i class="bi bi-arrow-repeat me-2"></i>Ubah Status Pengumuman</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3 small text-muted">Judul: <strong class="text-dark">{{ Str::limit($pengumuman->judul, 60) }}</strong></p>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Pilih Status Baru</label>
                                <select name="status" class="form-select" required>
                                    <option value="draft" {{ (isset($pengumuman->status) && $pengumuman->status === 'draft') ? 'selected' : '' }}>📝 Simpan sebagai Draft</option>
                                    <option value="publish" {{ (isset($pengumuman->status) && $pengumuman->status === 'publish') ? 'selected' : '' }}>📢 Publikasikan (Aktif)</option>
                                    <option value="expired" {{ (isset($pengumuman->status) && $pengumuman->status === 'expired') ? 'selected' : '' }}>📁 Tutup / Arsip (Expired)</option>
                                </select>
                                <div class="form-text small">Mengubah ke "Publikasikan" akan membuat pengumuman muncul di halaman utama.</div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-sm btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

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
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filterType = this.getAttribute('data-filter');
            const cards = document.querySelectorAll('.announcement-card');
            
            cards.forEach(card => {
                const cardStatus = card.getAttribute('data-status').toLowerCase();
                const cardKategori = card.getAttribute('data-kategori').toLowerCase();
                
                let show = false;
                if (filterType === 'all') {
                    show = true;
                } else if (filterType === 'draft' || filterType === 'aktif' || filterType === 'expired') {
                    show = cardStatus === filterType;
                } else {
                    show = cardKategori === filterType;
                }
                
                if (show) {
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 10);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });
        });
    });
    
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
            height: 'auto',
            firstDay: 1,
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                list: 'Daftar'
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                fetch('{{ route("api.kalender-pengumuman") }}')
                    .then(response => response.json())
                    .then(data => {
                        successCallback(data);
                    })
                    .catch(failureCallback);
            },
            eventClick: function(info) {
                if (info.event.url && info.event.url !== '#') {
                    info.jsEvent.preventDefault();
                    window.location.href = info.event.url;
                }
            }
        });
        calendar.render();
    }
});
</script>
@endpush