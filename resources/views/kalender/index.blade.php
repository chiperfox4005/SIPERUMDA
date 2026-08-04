@extends('layouts.app')

@section('title', 'Kalender Libur Nasional')

@push('styles')
<style>
    .fc-event.libur {
        background-color: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #ffffff !important;
    }
    .legend-libur {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
    }
    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 4px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Kalender Libur Nasional</h2>
            <p class="text-muted mb-0">Daftar hari libur nasional tahun {{ now()->year }}</p>
        </div>
        <div class="legend-libur">
            <div class="legend-color" style="background-color: #dc2626;"></div>
            <span class="fw-semibold">Hari Libur Nasional</span>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <!-- Wadah untuk FullCalendar -->
            <div id='kalenderLibur'></div>
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
        </a>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('kalenderLibur');
    if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listYear'
            },
            // Mengambil data dari API Kalender Libur 
            events: '{{ route("api.kalender-libur") }}?year={{ now()->year }}',
            eventClassNames: 'libur',
            height: 'auto',
            firstDay: 1, // Mulai dari Senin
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                year: 'Tahun',
                list: 'Daftar'
            },
            eventContent: function(arg) {
                return {
                    html: '<div style="padding: 2px 4px; font-size: 12px; font-weight: 600;">' + arg.event.title + '</div>'
                }
            }
        });
        calendar.render();
    }
});
</script>
@endpush
@endsection