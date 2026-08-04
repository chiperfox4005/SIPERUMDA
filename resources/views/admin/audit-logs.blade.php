@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--primary-color, #1F3864);">Audit Log Sistem</h2>
            <p class="text-muted mb-0">Riwayat aktivitas pengguna dan perubahan sistem.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if(isset($logs) && $logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Waktu</th>
                                <th>Pengguna</th>
                                <th>Aktivitas</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                            <tr>
                                <td class="ps-4">{{ \Carbon\Carbon::parse($log->created_at)->isoFormat('D MMMM Y HH:mm') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $log->user ? $log->user->nama_lengkap : 'Sistem' }}</div>
                                    <small class="text-muted">{{ $log->user ? $log->user->nip : '-' }}</small>
                                </td>
                                <td>{{ $log->description ?? $log->aktivitas ?? 'Aktivitas tidak diketahui' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $log->ip_address ?? '-' }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $logs->links() }}
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-journal-text" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-0">Belum ada aktivitas audit yang tercatat.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection