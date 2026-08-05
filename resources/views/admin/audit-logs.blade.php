@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Audit Logs (Riwayat Aktivitas)</h2>
            <p class="text-muted mb-0">Memantau semua perubahan data yang dilakukan oleh pengguna.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                            <th>Model / Data</th>
                            <th>Detail Perubahan</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="ps-4 small">{{ $log->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $log->user_name ?? 'System' }}</div>
                                <small class="text-muted">ID: {{ $log->user_id }}</small>
                            </td>
                            <td>
                                @if($log->action === 'created')
                                    <span class="badge bg-success">Dibuat</span>
                                @elseif($log->action === 'updated')
                                    <span class="badge bg-warning text-dark">Diubah</span>
                                @elseif($log->action === 'deleted')
                                    <span class="badge bg-danger">Dihapus</span>
                                @elseif($log->action === 'backup_database')
                                    <span class="badge bg-info text-dark">Backup DB</span>
                                @else
                                    <span class="badge bg-secondary">{{ $log->action }}</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ class_basename($log->model_type) }}</strong><br>
                                <small class="text-muted">ID: {{ $log->model_id }}</small>
                            </td>
                            <td>
                                @if($log->action === 'updated' && $log->old_data && $log->new_data)
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $log->id }}">
                                        Lihat Detail
                                    </button>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $log->ip_address }}</td>
                        </tr>

                        <!-- Modal Detail Perubahan -->
                        @if($log->action === 'updated')
                        <div class="modal fade" id="detailModal{{ $log->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detail Perubahan Data</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-danger">Data Lama (Old)</h6>
                                                <pre class="bg-light p-3 rounded small">{{ json_encode($log->old_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-success">Data Baru (New)</h6>
                                                <pre class="bg-light p-3 rounded small">{{ json_encode($log->new_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-text fs-1 d-block mb-2"></i>
                                Belum ada riwayat aktivitas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection