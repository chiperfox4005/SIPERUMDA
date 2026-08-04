@extends('layouts.app')
@section('title', 'Data Sub Bagian')
@section('content')
<style>
    .pagination svg { display: none !important; width: 0 !important; height: 0 !important; }
    .pagination .page-link { padding: 0.375rem 0.75rem !important; font-size: 0.875rem !important; }
</style>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Data Sub Bagian</h2>
            <p class="text-muted mb-0">Kelola struktur sub bagian di instansi Anda.</p>
        </div>
        <a href="{{ route('kepegawaian.sub-bagian.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Tambah Sub Bagian
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($subBagians->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Nama Sub Bagian</th>
                            <th>Induk Bagian</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subBagians as $index => $sub)
                        <tr>
                            <td class="ps-4">{{ $subBagians->firstItem() + $index }}</td>
                            <td class="fw-semibold">{{ $sub->nama_sub_bagian }}</td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $sub->bagian->nama_bagian }}</span></td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('kepegawaian.sub-bagian.edit', $sub) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('kepegawaian.sub-bagian.destroy', $sub) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus sub bagian ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($subBagians->hasPages())
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <span class="text-muted small">Menampilkan {{ $subBagians->firstItem() }} - {{ $subBagians->lastItem() }} dari {{ $subBagians->total() }}</span>
                <nav><ul class="pagination pagination-sm mb-0">
                    @if($subBagians->onFirstPage()) <li class="page-item disabled"><span class="page-link">‹ Sebelumnya</span></li>
                    @else <li class="page-item"><a class="page-link" href="{{ $subBagians->previousPageUrl() }}">‹ Sebelumnya</a></li> @endif
                    @if($subBagians->hasMorePages()) <li class="page-item"><a class="page-link" href="{{ $subBagians->nextPageUrl() }}">Selanjutnya ›</a></li>
                    @else <li class="page-item disabled"><span class="page-link">Selanjutnya ›</span></li> @endif
                </ul></nav>
            </div>
            @endif
            @else
            <div class="text-center py-5 text-muted"><i class="bi bi-diagram-3 fs-1 d-block mb-2"></i>Belum ada data sub bagian.</div>
            @endif
        </div>
    </div>
</div>
@endsection