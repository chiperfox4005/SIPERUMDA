@extends('layouts.app')

@section('title', 'Riwayat Agenda')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Riwayat Agenda</h2>
            <p class="text-muted mb-0">Daftar pengajuan agenda dan undangan yang melibatkan Anda.</p>
        </div>
        <a href="{{ route('agenda.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Buat Agenda Baru
        </a>
    </div>

    <!-- Kalender Agenda -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="bi bi-calendar-month me-2 text-primary"></i>Kalender Agenda & Libur Nasional</h5>
        </div>
        <div class="card-body">
            <div id="kalenderAgendaIndex" style="min-height: 400px;"></div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">No.</th>
                            <th>Judul Agenda</th>
                            <th>Tanggal</th>
                            <th>Lokasi / Tempat</th>
                            <th>Pengaju</th>
                            <!-- ✅ KOLOM AKSI DITAMBAHKAN DI SINI -->
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agendas as $agenda)
                        <tr style="cursor: pointer;" onclick="window.location.href='{{ route('agenda.show', $agenda) }}'">
                            <td class="ps-4 fw-semibold text-muted">
                                {{ ($agendas->currentPage() - 1) * $agendas->perPage() + $loop->iteration }}
                            </td>
                            <td>
                                <div class="fw-bold text-primary">{{ Str::limit($agenda->judul, 40) }}</div>
                                <small class="text-muted">{{ Str::limit($agenda->acara, 50) }}</small>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($agenda->tanggal_mulai)->format('d M Y') }}
                                <br>
                                <small class="text-muted">{{ $agenda->jam_mulai }} - {{ $agenda->jam_selesai ?? 'Selesai' }}</small>
                            </td>
                            <td>
                                {{ $agenda->tempat ?? '-' }}
                                @if($agenda->ruangan)
                                    <br><small class="text-primary">({{ $agenda->ruangan->nama_ruangan }})</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $creatorName = $agenda->creator->nama_lengkap ?? $agenda->creator->name ?? 'User';
                                    $creatorNip = $agenda->created_by ?? '-';
                                @endphp
                                <div class="fw-semibold">{{ $creatorName }}</div>
                                <small class="text-muted">NIP: {{ $creatorNip }}</small>
                            </td>
                            
                            <!-- ✅ LOGIKA TOMBOL EDIT & HAPUS (HANYA UNTUK PEMBUAT) -->
                            <td class="text-center">
                                @php
                                    $userNip = (string) auth()->user()->nip;
                                    // Cek apakah NIP yang login sama persis dengan pembuat agenda
                                    $isCreator = ($agenda->created_by === $userNip);
                                    
                                    // Opsional: Hanya izinkan edit/hapus jika status masih 'submitted' (belum diproses sekretariat)
                                    // Jika ingin bisa edit/hapus kapan saja selama dia pembuat, hapus kondisi '&& $agenda->status === 'submitted''
                                    $canManage = $isCreator && $agenda->status === 'submitted';
                                @endphp

                                @if($canManage)
                                    <!-- event.stopPropagation() MENCEGAH baris berpindah ke halaman detail saat tombol diklik -->
                                    <div class="d-flex justify-content-center gap-2" onclick="event.stopPropagation();">
                                        <a href="{{ route('agenda.edit', $agenda->id) }}" class="btn btn-sm btn-warning" title="Edit Agenda">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('agenda.destroy', $agenda->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus agenda ini? Tindakan ini tidak dapat dibatalkan.'); event.stopPropagation();">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus Agenda">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <!-- ✅ AKHIR LOGIKA TOMBOL -->
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada data agenda.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0">
            {{ $agendas->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('kalenderAgendaIndex');
    if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: { 
                left: 'prev,next today', 
                center: 'title', 
                right: 'dayGridMonth,listWeek' 
            },
            events: '{{ route("api.kalender-agenda") }}',
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
                list: 'Daftar'
            }
        });
        calendar.render();
    }
});
</script>
@endpush