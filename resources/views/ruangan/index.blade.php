@extends('layouts.app')

@section('title', 'Data Master Ruangan')

@push('styles')
<style>
    :root {
        --primary-color: #1F3864;
        --card-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    
    .card-custom {
        border: none;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        margin-bottom: 24px;
        overflow: hidden;
        background: white;
    }
    .card-custom .card-header {
        background: white;
        border-bottom: 1px solid #e9ecef;
        padding: 16px 24px;
        font-weight: 600;
        color: var(--primary-color);
        font-size: 1.1rem;
    }
    .card-custom .card-body {
        padding: 24px;
    }
    
    .btn-primary-unified {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        border-radius: 8px;
        padding: 8px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
    .btn-primary-unified:hover {
        background-color: #152747;
        border-color: #152747;
        color: white;
    }
    
    .table-unified {
        margin-bottom: 0;
        font-size: 0.9rem;
    }
    .table-unified thead th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e9ecef;
        padding: 16px;
    }
    .table-unified tbody td {
        padding: 16px;
        vertical-align: middle;
        color: #212529;
        border-bottom: 1px solid #f1f3f5;
    }
    .table-unified tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .badge-unified {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .badge-aktif { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
    .badge-nonaktif { background-color: rgba(108, 117, 125, 0.1); color: #6c757d; }
    
    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e9ecef;
        background: white;
        color: #6c757d;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-action:hover {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    .btn-action.delete:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--primary-color);">Data Master Ruangan</h2>
            <p class="text-muted mb-0">Kelola daftar ruangan yang tersedia untuk dipinjam.</p>
        </div>
        <a href="{{ route('ruangan.create') }}" class="btn-primary-unified">
            <i class="bi bi-plus-lg me-2"></i>Tambah Ruangan
        </a>
    </div>

    <div class="card-custom">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-building me-2"></i>Daftar Ruangan</span>
            <span class="badge bg-light text-dark border">{{ $ruangans->total() ?? $ruangans->count() }} Ruangan</span>
        </div>
        <div class="card-body p-0">
            @if($ruangans->count() > 0)
                <div class="table-responsive">
                    <table class="table table-unified">
                        <thead>
                            <tr>
                                <th class="ps-4">Nama Ruangan</th>
                                <th>Kapasitas</th>
                                <th>Fasilitas</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ruangans as $ruangan)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $ruangan->nama_ruangan }}</td>
                                <td>{{ $ruangan->kapasitas }} Orang</td>
                                <td>{{ $ruangan->fasilitas ?? '-' }}</td>
                                <td>
                                    @if($ruangan->status == 'aktif')
                                        <span class="badge-unified badge-aktif">Aktif</span>
                                    @else
                                        <span class="badge-unified badge-nonaktif">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('ruangan.edit', $ruangan) }}" class="btn-action" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('ruangan.destroy', $ruangan) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus ruangan ini? Tindakan ini tidak dapat dibatalkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action delete" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $ruangans->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">Belum Ada Data Ruangan</h5>
                    <p class="text-muted mb-4">Silakan tambahkan ruangan baru untuk mulai mengelola peminjaman.</p>
                    <a href="{{ route('ruangan.create') }}" class="btn-primary-unified">
                        <i class="bi bi-plus-lg me-2"></i>Tambah Ruangan Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection