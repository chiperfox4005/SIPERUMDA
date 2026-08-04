@extends('layouts.app')

@section('title', 'Manajemen Pejabat Penandatangan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Manajemen Pejabat Penandatangan</h2>
            <p class="text-muted mb-0">Kelola data pejabat yang berwenang menandatangani surat resmi.</p>
        </div>
        <a href="{{ route('signatories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Tambah Pejabat Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Nama Pejabat</th>
                            <th>Jabatan</th>
                            <th>NIP</th>
                            <th class="text-center">Tanda Tangan</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($signatories as $index => $signatory)
                        <tr>
                            <td class="ps-4">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $signatory->name }}</div>
                            </td>
                            <td>{{ $signatory->position }}</td>
                            <td>{{ $signatory->nip }}</td>
                            <td class="text-center">
                                @if($signatory->signature_image)
                                    <img src="{{ asset('storage/' . $signatory->signature_image) }}" 
                                         alt="TTD {{ $signatory->name }}" 
                                         style="max-height: 50px; max-width: 150px;"
                                         class="img-fluid">
                                @else
                                    <span class="badge bg-secondary">Belum Ada</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <form action="{{ route('signatories.toggle', $signatory) }}" method="POST" class="d-inline">
                                    @csrf
                                    @if($signatory->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                        <button type="submit" class="btn btn-sm btn-outline-warning ms-1" title="Nonaktifkan">
                                            <i class="bi bi-toggle-on"></i>
                                        </button>
                                    @else
                                        <span class="badge bg-danger">Nonaktif</span>
                                        <button type="submit" class="btn btn-sm btn-outline-success ms-1" title="Aktifkan">
                                            <i class="bi bi-toggle-off"></i>
                                        </button>
                                    @endif
                                </form>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('signatories.edit', $signatory) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('signatories.destroy', $signatory) }}" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Yakin ingin menghapus pejabat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                                Belum ada data pejabat penandatangan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection