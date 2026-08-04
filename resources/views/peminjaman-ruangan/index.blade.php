@extends('layouts.app')

@section('title', 'Peminjaman Ruangan')

@section('content')
<style>
    /* Pengaman lokal untuk mencegah tombol pagination atau elemen lain membesar */
    .pagination svg { display: none !important; width: 0 !important; height: 0 !important; }
    .pagination .page-link { padding: 0.375rem 0.75rem !important; font-size: 0.875rem !important; }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Peminjaman Ruangan</h2>
            <p class="text-muted mb-0">Ajukan dan pantau status peminjaman ruangan Anda.</p>
        </div>
        <a href="{{ route('peminjaman-ruangan.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Ajukan Peminjaman
        </a>
    </div>

    <!-- Notifikasi Sukses -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Kalender Status Ruangan & Libur -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <i class="bi bi-calendar-month me-2 text-success"></i>
                Kalender Status Ruangan & Libur Nasional
            </h5>
        </div>
        <div class="card-body">
            <div id="kalenderPeminjamanIndex" style="min-height: 400px;"></div>
        </div>
    </div>

    <!-- Tabel Daftar Peminjaman -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($peminjamans->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Nama Ruangan</th>
                                <th>Tanggal & Waktu</th>
                                <th>Keperluan</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($peminjamans as $peminjaman)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $peminjaman->ruangan->nama_ruangan ?? 'Ruangan Dihapus' }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($peminjaman->tanggal_pemakaian)->format('d M Y') }}<br>
                                    <small class="text-muted">{{ $peminjaman->waktu_mulai }} - {{ $peminjaman->waktu_selesai }} WIB</small>
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($peminjaman->keperluan, 40) }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($peminjaman->status_persetujuan) {
                                            'disetujui' => 'bg-success bg-opacity-10 text-success',
                                            'ditolak' => 'bg-danger bg-opacity-10 text-danger',
                                            'dijadwalkan_ulang' => 'bg-danger bg-opacity-10 text-danger',
                                            'dibatalkan' => 'bg-secondary bg-opacity-10 text-secondary',
                                            'menunggu_konfirmasi' => 'bg-info bg-opacity-10 text-info',
                                            default => 'bg-warning bg-opacity-10 text-warning'
                                        };
                                        
                                        $statusLabel = match($peminjaman->status_persetujuan) {
                                            'disetujui' => 'Disetujui',
                                            'ditolak' => 'Ditolak',
                                            'dijadwalkan_ulang' => 'Dijadwalkan Ulang',
                                            'dibatalkan' => 'Dibatalkan',
                                            'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                                            default => 'Menunggu'
                                        };

                                        $catatan = $peminjaman->catatan_penolakan ?? $peminjaman->catatan_pembatalan ?? '';
                                    @endphp
                                    
                                    <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                    
                                    @if(in_array($peminjaman->status_persetujuan, ['ditolak', 'dijadwalkan_ulang', 'dibatalkan']) && !empty($catatan))
                                        <div class="mt-2 p-2 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded small text-danger">
                                            <i class="bi bi-exclamation-circle-fill me-1"></i>
                                            <strong>Catatan:</strong> {{ $catatan }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        
                                        @if($peminjaman->status_persetujuan === 'disetujui' && auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']))
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalRescheduleCatatan{{ $peminjaman->id }}" title="Jadwalkan Ulang dengan Catatan">
                                                <i class="bi bi-calendar-x"></i> Reschedule
                                            </button>
                                        @endif

                                        <a href="{{ route('peminjaman-ruangan.show', $peminjaman) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                                        
                                        @if($peminjaman->status_persetujuan === 'menunggu' && $peminjaman->user_id === (string) auth()->user()->nip)
                                            <form action="{{ route('peminjaman-ruangan.destroy', $peminjaman) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin membatalkan peminjaman ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Batalkan">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    @if($peminjaman->status_persetujuan === 'disetujui' && auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator']))
                                    <div class="modal fade" id="modalRescheduleCatatan{{ $peminjaman->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form action="{{ route('peminjaman-ruangan.reschedule-dengan-catatan', $peminjaman) }}" method="POST">
                                                @csrf
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title"><i class="bi bi-calendar-x me-2"></i>Jadwalkan Ulang / Batalkan</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <div class="alert alert-warning small mb-3">
                                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                            Tindakan ini akan mengubah status menjadi <strong class="text-danger">Dijadwalkan Ulang</strong> (Merah) dan memberi tahu pemohon melalui notifikasi.
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Alasan / Catatan Reschedule <span class="text-danger">*</span></label>
                                                            <textarea name="catatan_reschedule" class="form-control" rows="3" required placeholder="Contoh: Ada rapat direksi mendadak, mohon cari jadwal lain."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger">Konfirmasi Reschedule</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination Sederhana (Tanpa Ikon SVG yang membesar) -->
                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="text-muted small">
                        Menampilkan {{ $peminjamans->firstItem() ?? 1 }} sampai {{ $peminjamans->lastItem() ?? $peminjamans->count() }} dari {{ $peminjamans->total() }} hasil
                    </span>
                    @if($peminjamans->hasPages())
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                @if($peminjamans->onFirstPage())
                                    <li class="page-item disabled"><span class="page-link">‹ Sebelumnya</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $peminjamans->previousPageUrl() }}">‹ Sebelumnya</a></li>
                                @endif

                                @if($peminjamans->hasMorePages())
                                    <li class="page-item"><a class="page-link" href="{{ $peminjamans->nextPageUrl() }}">Selanjutnya ›</a></li>
                                @else
                                    <li class="page-item disabled"><span class="page-link">Selanjutnya ›</span></li>
                                @endif
                            </ul>
                        </nav>
                    @endif
                </div>

            @else
                <div class="text-center py-5">
                    <i class="bi bi-door-open text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">Belum Ada Pengajuan</h5>
                    <p class="text-muted mb-4">Anda belum pernah mengajukan peminjaman ruangan.</p>
                    <a href="{{ route('peminjaman-ruangan.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Ajukan Sekarang
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('kalenderPeminjamanIndex');
    if (calendarEl) {
        new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: { 
                left: 'prev,next today', 
                center: 'title', 
                right: 'dayGridMonth,listWeek' 
            },
            events: '{{ route("api.kalender-ruangan") }}',
            height: 'auto',
            firstDay: 1,
            eventClick: function(info) {
                if (info.event.url) {
                    info.jsEvent.preventDefault();
                    window.location.href = info.event.url;
                }
            },
            eventDisplay: 'block',
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                day: 'Hari',
                list: 'Daftar'
            }
        }).render();
    }
});
</script>
@endpush