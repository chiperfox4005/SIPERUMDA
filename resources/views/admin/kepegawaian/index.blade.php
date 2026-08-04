@extends('layouts.app')

@section('title', 'Data Pegawai')

@section('content')
<style>
    .pagination svg { display: none !important; width: 0 !important; height: 0 !important; }
    .pagination .page-link { padding: 0.375rem 0.75rem !important; font-size: 0.875rem !important; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Data Pegawai</h2>
            <p class="text-muted mb-0">Kelola data pegawai, bagian, dan hak akses.</p>
        </div>
        <a href="{{ route('kepegawaian.pegawai.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i>Tambah Pegawai
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('kepegawaian.pegawai.index') }}" method="GET" class="mb-4 row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted fw-semibold">Cari NIP atau Nama</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Ketik NIP atau Nama..." value="{{ $search ?? '' }}">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                        @if($search || $perPage == 9999)
                            <a href="{{ route('kepegawaian.pegawai.index') }}" class="btn btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-semibold">Tampilan Data</label>
                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 per halaman</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 per halaman</option>
                        <option value="9999" {{ $perPage == 9999 ? 'selected' : '' }}>Tampilkan Semua</option>
                    </select>
                </div>
            </form>

            @if(isset($users) && $users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">NIP & Nama</th>
                                <th>Bagian / Sub Bagian</th>
                                <th class="text-center">Role</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold">{{ $user->nama_lengkap ?? $user->name }}</div>
                                    <small class="text-muted">NIP: {{ $user->nip }}</small>
                                </td>
                                <td>
                                    <div>{{ $user->bagian->nama_bagian ?? '-' }}</div>
                                    <small class="text-muted">{{ $user->subBagian->nama_sub_bagian ?? '' }}</small>
                                </td>
                                <td class="text-center">
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td class="text-center">
                                    @if($user->status == 'aktif')
                                        <span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle-fill me-1"></i>Aktif</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-x-circle-fill me-1"></i>Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('kepegawaian.pegawai.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('kepegawaian.pegawai.reset', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Reset password menjadi NIP?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Reset Password">
                                                <i class="bi bi-key"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('kepegawaian.pegawai.status', $user) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn btn-sm {{ $user->status == 'aktif' ? 'btn-outline-danger' : 'btn-outline-success' }}" title="{{ $user->status == 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="bi {{ $user->status == 'aktif' ? 'bi-person-x' : 'bi-person-check' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('kepegawaian.pegawai.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pegawai ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
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

                @if($users->hasPages() && $perPage != 9999)
                <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="text-muted small">
                        Menampilkan {{ $users->firstItem() }} sampai {{ $users->lastItem() }} dari {{ $users->total() }} hasil
                    </span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            @if($users->onFirstPage())
                                <li class="page-item disabled"><span class="page-link">‹ Sebelumnya</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $users->previousPageUrl() }}">‹ Sebelumnya</a></li>
                            @endif
                            @if($users->hasMorePages())
                                <li class="page-item"><a class="page-link" href="{{ $users->nextPageUrl() }}">Selanjutnya ›</a></li>
                            @else
                                <li class="page-item disabled"><span class="page-link">Selanjutnya ›</span></li>
                            @endif
                        </ul>
                    </nav>
                </div>
                @endif

            @else
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-0">Belum ada data pegawai.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection