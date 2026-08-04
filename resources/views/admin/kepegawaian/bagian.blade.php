@extends('layouts.app')

@section('title', 'Data Bagian')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--primary-color, #1F3864);">Data Bagian</h2>
            <p class="text-muted mb-0">Kelola bagian organisasi perusahaan.</p>
        </div>
        <div>
            <a href="{{ route('kepegawaian.bagian.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Tambah Bagian
            </a>
        </div>
    </div>

    <!-- Tabel Bagian -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Nama Bagian</th>
                            <th>Keterangan</th>
                            <th>Jumlah Sub Bagian</th>
                            <th>Jumlah Pegawai</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bagians as $bagian)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $bagian->nama_bagian }}</td>
                            <td>{{ $bagian->keterangan }}</td>
                            <td>{{ $bagian->subBagians->count() }}</td>
                            <td>{{ $bagian->users->count() }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('kepegawaian.bagian.edit', $bagian) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('kepegawaian.bagian.destroy', $bagian) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus bagian ini?')">
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