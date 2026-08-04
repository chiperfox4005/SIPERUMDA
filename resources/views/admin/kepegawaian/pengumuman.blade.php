@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--primary-color, #1F3864);">Pengumuman</h2>
            <p class="text-muted mb-0">Buat dan kelola pengumuman untuk seluruh pegawai.</p>
        </div>
        <div>
            <a href="{{ route('kepegawaian.pengumuman.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Buat Pengumuman
            </a>
        </div>
    </div>

    <!-- Tabel Pengumuman -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Judul</th>
                            <th>Kategori</th>
                            <th>Tanggal Terbit</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengumumans as $pengumuman)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $pengumuman->judul }}</td>
                            <td>{{ $pengumuman->kategori }}</td>
                            <td>{{ $pengumuman->tanggal_publish->format('d M Y') }}</td>
                            <td>
                                @if($pengumuman->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Arsip</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('kepegawaian.pengumuman.show', $pengumuman) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('kepegawaian.pengumuman.edit', $pengumuman) }}" class="btn btn-sm btn-outline-warning me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('kepegawaian.pengumuman.destroy', $pengumuman) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pengumuman ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection