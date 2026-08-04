@extends('layouts.app')

@section('title', 'Pilih Jenis Surat')

@section('content')
<div class="container-fluid py-4">
    <!-- ✅ HEADER DENGAN TOMBOL KEMBALI KE RIWAYAT -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Pilih Jenis Surat</h2>
            <p class="text-muted mb-0">Silakan pilih template surat yang ingin Anda ajukan.</p>
        </div>
        <a href="{{ route('surat.index') }}" class="btn btn-outline-secondary d-flex align-items-center">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Riwayat
        </a>
    </div>

    <div class="row g-4">
        @forelse($templates as $template)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm hover-card" style="transition: all 0.3s ease;">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                            <i class="bi bi-file-earmark-text fs-4"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold">{{ $template->name }}</h5>
                    </div>
                    <p class="text-muted small flex-grow-1">{{ $template->description ?? 'Template surat standar' }}</p>
                    
                    {{-- ✅ TOMBOL UNTUK MEMBUAT SURAT DARI TEMPLATE --}}
                    <a href="{{ route('surat.buat-template', $template) }}" class="btn btn-primary w-100 mt-3">
                        <i class="bi bi-pencil-square me-2"></i>Buat Surat Ini
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
            <p class="text-muted">Belum ada template surat yang tersedia.</p>
        </div>
        @endforelse
    </div>
</div>

@push('styles')
<style>
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
</style>
@endpush
@endsection