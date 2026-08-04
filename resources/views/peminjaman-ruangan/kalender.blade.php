@extends('layouts.app')

@section('title', 'Kalender Ruangan')

@push('styles')
<style>
    .fc-event { cursor: pointer; font-size: 0.85rem; }
    .legend-container { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
    .legend-item { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; }
    .legend-color { width: 16px; height: 16px; border-radius: 4px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Kalender Ketersediaan Ruangan</h2>
            <p class="text-muted mb-0">Monitor status ruangan bulan {{ now()->locale('id')->isoFormat('MMMM Y') }}</p>
        </div>
        <a href="{{ route('peminjaman-ruangan.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Ajukan Peminjaman
        </a>
    </div>

    <!-- Legend Status -->
    <div class="legend-container mb-4">
        <div class="legend-item"><div class="legend-color" style="background-color: #10b981;"></div><span>Disetujui</span></div>
        <div class="legend-item"><div class="legend-color" style="background-color: #f59e0b;"></div><span>Menunggu Persetujuan</span></div>
        <div class="legend-item"><div class="legend-color" style="background-color: #ef4444;"></div><span>Dibatalkan</span></div>
        <div class="legend-item"><div class="legend-color" style="background-color: #9ca3af;"></div><span>Ditolak</span></div>
    </div>

    <!-- Tampilan List HTML (Jika Menggunakan Data Array/Collection $kalenderRuangan) -->
    @if(isset($kalenderRuangan) && count($kalenderRuangan) > 0)
    <div class="card border-0 shadow-sm mb-4 d-none d-lg-block">
        <div class="card-body">
            <h5 class="fw-bold mb-3"><i class="bi bi-list-stars me-2 text-primary"></i>Ringkasan Agenda Ruangan Bulan Ini</h5>
            <div class="row g-3">
                @foreach($kalenderRuangan as $tanggal => $items)
                    <div class="col-md-4 col-lg-3">
                        <div class="border rounded p-3 bg-light h-100">
                            <div class="fw-bold text-primary mb-2 border-bottom pb-1">
                                {{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMM Y') }}
                            </div>
                            @foreach($items as $item)
                                @php
                                    $badgeClass = 'bg-success'; // Default disetujui
                                    $statusLabel = 'Disetujui';
                                    
                                    if ($item->status_persetujuan === 'menunggu') {
                                        $badgeClass = 'bg-warning text-dark';
                                        $statusLabel = 'Menunggu';
                                    } elseif ($item->status_persetujuan === 'ditolak') {
                                        $badgeClass = 'bg-secondary';
                                        $statusLabel = 'Ditolak';
                                    } elseif ($item->status_persetujuan === 'dibatalkan') {
                                        $badgeClass = 'bg-danger'; // MERAH
                                        $statusLabel = 'DIBATALKAN';
                                    }
                                @endphp

                                <div class="p-2 mb-2 rounded text-white {{ $badgeClass }}" style="font-size: 0.8rem;">
                                    <strong>{{ $statusLabel }}</strong> - {{ $item->ruangan->nama_ruangan ?? 'Ruangan' }}<br>
                                    <i class="bi bi-clock me-1"></i>{{ $item->waktu_mulai }} - {{ $item->waktu_selesai }}<br>
                                    <small><i class="bi bi-person me-1"></i>{{ $item->pemohon->nama_lengkap ?? $item->pemohon->name ?? 'Unknown' }}</small>
                                    
                                    @if($item->status_persetujuan === 'dibatalkan')
                                        <br><small class="text-warning fw-semibold"><i class="bi bi-exclamation-circle me-1"></i>{{ Str::limit($item->catatan_pembatalan ?? 'Dibatalkan oleh pengelola', 30) }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Kalender Visual FullCalendar -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div id='kalenderRuangan'></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('kalenderRuangan');
    if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            events: '{{ route("api.kalender-ruangan") }}',
            eventClick: function(info) {
                if (info.event.url) {
                    info.jsEvent.preventDefault();
                    window.location.href = info.event.url;
                }
            },
            // Penyesuaian warna dan tampilan item di FullCalendar
            eventDidMount: function(info) {
                var status = info.event.extendedProps.status_persetujuan || info.event.extendedProps.status;
                if (status === 'dibatalkan') {
                    info.el.style.backgroundColor = '#ef4444';
                    info.el.style.borderColor = '#dc2626';
                    info.el.style.color = '#ffffff';
                } else if (status === 'menunggu') {
                    info.el.style.backgroundColor = '#f59e0b';
                    info.el.style.borderColor = '#d97706';
                    info.el.style.color = '#000000';
                } else if (status === 'ditolak') {
                    info.el.style.backgroundColor = '#9ca3af';
                    info.el.style.borderColor = '#6b7280';
                } else if (status === 'disetujui') {
                    info.el.style.backgroundColor = '#10b981';
                    info.el.style.borderColor = '#059669';
                }
            },
            eventDisplay: 'block',
            height: 'auto',
            firstDay: 1,
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                day: 'Hari',
                list: 'Daftar'
            }
        });
        calendar.render();
    }
});
</script>
@endpush
@endsection