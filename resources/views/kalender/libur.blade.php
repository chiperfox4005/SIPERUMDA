@extends('layouts.app')

@section('title', 'Kalender Libur Nasional')

@push('styles')
<style>
    .fc-event.libur {
        background-color: #dc2626 !important;
        border-color: #dc2626 !important;
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
            <span>Hari Libur Nasional</span>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div id='kalenderLibur'></div>
        </div>
    </div>

    <!-- Daftar Libur -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Daftar Hari Libur Nasional {{ now()->year }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Nama Libur</th>
                        </tr>
                    </thead>
                    <tbody id="daftarLibur">
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Kalender FullCalendar
    var calendarEl = document.getElementById('kalenderLibur');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listYear'
        },
        events: '{{ route("api.kalender-libur.events") }}?year={{ now()->year }}',
        eventClassNames: 'libur',
        height: 'auto',
        firstDay: 1,
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            list: 'Daftar'
        }
    });
    calendar.render();

    // Load daftar libur
    fetch('{{ route("api.libur.tahun", now()->year) }}')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('daftarLibur');
            let html = '';
            
            data.forEach((libur, index) => {
                const tanggal = new Date(libur.date);
                const hari = tanggal.toLocaleDateString('id-ID', { weekday: 'long' });
                const tanggalFormat = tanggal.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${tanggalFormat}</td>
                        <td>${hari}</td>
                        <td><strong>${libur.name}</strong></td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('daftarLibur').innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-danger py-4">
                        <i class="bi bi-exclamation-triangle me-2"></i>Gagal memuat data libur
                    </td>
                </tr>
            `;
        });
});
</script>
@endpush
@endsection