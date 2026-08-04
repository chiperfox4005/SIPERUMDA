@extends('layouts.app')

@section('title', 'Daftar Surat')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Manajemen Surat</h2>
            <p class="text-muted mb-0">Kelola surat tugas, dinas, izin, dan lainnya.</p>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->hasRole(['Sekretariat', 'IT Administrator', 'Administrator', 'Kepegawaian']))
                <a href="{{ route('surat.approval') }}" class="btn btn-warning">
                    <i class="bi bi-clipboard-check me-2"></i>Persetujuan Surat
                </a>
            @endif
            <a href="{{ route('surat.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Buat Surat
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Surat</th>
                            <th>Jenis</th>
                            <th>Perihal</th>
                            <th>Tanggal</th>
                            <th>Pembuat</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($surats as $surat)
                        <tr>
                            <td>
                                <strong>{{ $surat->nomor_surat ?? '-' }}</strong>
                            </td>
                            <td>{{ $surat->jenis_label }}</td>
                            <td>{{ Str::limit($surat->perihal, 40) }}</td>
                            <td>{{ $surat->tanggal_surat->format('d M Y') }}</td>
                            <td>{{ $surat->pembuat->nama_lengkap ?? $surat->dibuat_oleh }}</td>
                            <td>
                                <span class="badge {{ $surat->status_badge['class'] }}">
                                    {{ $surat->status_badge['label'] }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('surat.show', $surat) }}" class="btn btn-outline-primary" title="Lihat">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($surat->status === 'draft' && $surat->dibuat_oleh === (string) auth()->user()->nip)
                                        <a href="{{ route('surat.edit', $surat) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                    @if($surat->status === 'approved')
                                        <a href="{{ route('surat.download', $surat) }}" class="btn btn-outline-success" title="Download PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-text" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0">Belum ada surat</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $surats->links() }}
    </div>
</div>
@endsection