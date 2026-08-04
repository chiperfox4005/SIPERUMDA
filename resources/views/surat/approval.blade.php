@extends('layouts.app')

@section('title', 'Persetujuan Surat')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Persetujuan Surat</h2>
            <p class="text-muted mb-0">Verifikasi dan kelola pengajuan surat.</p>
        </div>
        <a href="{{ route('surat.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Surat Menunggu Persetujuan -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-warning bg-opacity-10">
            <h5 class="mb-0"><i class="bi bi-hourglass-split me-2 text-warning"></i>Menunggu Persetujuan ({{ $menunggu->count() }})</h5>
        </div>
        <div class="card-body">
            @forelse($menunggu as $surat)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1">{{ $surat->jenis_label }}</h6>
                            <p class="mb-1"><strong>Perihal:</strong> {{ $surat->perihal }}</p>
                            <small class="text-muted">
                                <i class="bi bi-person me-1"></i>{{ $surat->pembuat->nama_lengkap ?? $surat->dibuat_oleh }} | 
                                <i class="bi bi-calendar me-1"></i>{{ $surat->tanggal_surat->format('d M Y') }}
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('surat.show', $surat) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Lihat
                            </a>
                            <form action="{{ route('surat.approve', $surat) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" 
                                        onclick="return confirm('Setujui surat ini?')">
                                    <i class="bi bi-check-lg"></i> Setujui
                                </button>
                            </form>
                            <button type="button" class="btn btn-sm btn-danger" 
                                    data-bs-toggle="modal" data-bs-target="#rejectModal{{ $surat->id }}">
                                <i class="bi bi-x-lg"></i> Tolak
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Tolak -->
                <div class="modal fade" id="rejectModal{{ $surat->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form action="{{ route('surat.reject', $surat) }}" method="POST">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Tolak Surat</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Berikan alasan penolakan surat ini:</p>
                                    <textarea name="catatan_penolakan" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger">Tolak Surat</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-muted text-center py-4">Tidak ada surat yang menunggu persetujuan.</p>
            @endforelse
        </div>
    </div>

    <!-- Riwayat -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Persetujuan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No. Surat</th>
                            <th>Jenis</th>
                            <th>Perihal</th>
                            <th>Pembuat</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $surat)
                        <tr>
                            <td>{{ $surat->nomor_surat }}</td>
                            <td>{{ $surat->jenis_label }}</td>
                            <td>{{ Str::limit($surat->perihal, 30) }}</td>
                            <td>{{ $surat->pembuat->nama_lengkap ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $surat->status_badge['class'] }}">
                                    {{ $surat->status_badge['label'] }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('surat.show', $surat) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($surat->status === 'approved')
                                    <a href="{{ route('surat.download', $surat) }}" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection