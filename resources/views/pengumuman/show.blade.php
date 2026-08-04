@extends('layouts.app')

@section('title', $pengumuman->judul)

@push('styles')
<style>
    .announcement-content {
        line-height: 1.8;
        color: #334155;
        font-size: 1.05rem;
    }
    .announcement-content p {
        margin-bottom: 1.5rem;
    }
    .badge-priority-penting { background-color: #fef3c7; color: #d97706; }
    .badge-priority-mendesak { background-color: #fee2e2; color: #dc2626; }
    .badge-priority-umum { background-color: #e2e8f0; color: #475569; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <!-- Back Button -->
            <div class="mb-4">
                <a href="{{ route('pengumuman.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pengumuman
                </a>
            </div>

            <!-- Main Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    
                    <!-- Header -->
                    <div class="mb-4 border-bottom pb-3">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            @php
                                $priorityClass = match($pengumuman->prioritas ?? 'umum') {
                                    'mendesak' => 'badge-priority-mendesak',
                                    'penting' => 'badge-priority-penting',
                                    default => 'badge-priority-umum',
                                };
                            @endphp
                            <span class="badge {{ $priorityClass }} px-3 py-2 rounded-pill text-uppercase fw-bold" style="font-size: 0.75rem;">
                                {{ strtoupper($pengumuman->prioritas ?? 'Umum') }}
                            </span>
                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill" style="font-size: 0.75rem;">
                                {{ $pengumuman->kategori ?? 'Umum' }}
                            </span>
                        </div>
                        
                        <h1 class="fw-bold text-dark mb-3" style="font-size: 1.75rem; line-height: 1.3;">
                            {{ $pengumuman->judul }}
                        </h1>
                        
                        <div class="d-flex flex-wrap align-items-center text-muted gap-3" style="font-size: 0.9rem;">
                            <span>
                                <i class="bi bi-calendar3 me-1"></i> 
                                {{ \Carbon\Carbon::parse($pengumuman->tanggal_publish)->isoFormat('D MMMM Y') }}
                            </span>
                            <span>
                                <i class="bi bi-person-circle me-1"></i> 
                                {{ $pengumuman->creator->nama_lengkap ?? $pengumuman->creator->name ?? 'Administrator' }}
                            </span>
                            @if($pengumuman->tanggal_berakhir)
                            <span class="text-warning">
                                <i class="bi bi-clock-history me-1"></i> 
                                Berlaku hingga {{ \Carbon\Carbon::parse($pengumuman->tanggal_berakhir)->isoFormat('D MMMM Y') }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="announcement-content mb-5">
                        {!! $pengumuman->isi !!}
                    </div>

                    <!-- Attachments -->
                    @if($pengumuman->lampiran)
                    <div class="mt-4 p-3 border rounded bg-light">
                        <h6 class="fw-bold mb-3 text-primary">
                            <i class="bi bi-paperclip"></i> Lampiran:
                        </h6>

                        @php
                            // Ambil URL file dari storage
                            $filePath = asset('storage/' . $pengumuman->lampiran);
                            // Ambil ekstensi file (jpg, pdf, docx, dll)
                            $ext = strtolower(pathinfo($pengumuman->lampiran, PATHINFO_EXTENSION));
                        @endphp

                        {{-- 1. JIKA FILE ADALAH GAMBAR (JPG, PNG, JPEG, WEBP) --}}
                        @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                            <div class="text-center">
                                <img src="{{ $filePath }}" class="img-fluid rounded shadow-sm" alt="Lampiran Pengumuman" style="max-height: 400px; width: auto; max-width: 100%;">
                                <div class="mt-3">
                                    <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="bi bi-zoom-in"></i> Lihat Ukuran Penuh
                                    </a>
                                    <a href="{{ $filePath }}" download class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-download"></i> Unduh Gambar
                                    </a>
                                </div>
                            </div>

                        {{-- 2. JIKA FILE ADALAH PDF --}}
                        @elseif($ext === 'pdf')
                            <div class="ratio ratio-16x9 border rounded bg-white shadow-sm">
                                <iframe src="{{ $filePath }}" title="Preview PDF" style="border: none;"></iframe>
                            </div>
                            <div class="mt-3">
                                <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-danger">
                                    <i class="bi bi-file-earmark-pdf"></i> Buka PDF di Tab Baru
                                </a>
                                <a href="{{ $filePath }}" download class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download"></i> Unduh PDF
                                </a>
                            </div>

                        {{-- 3. JIKA FILE ADALAH DOKUMEN OFFICE (WORD, EXCEL, DLL) --}}
                        @else
                            <div class="d-flex align-items-center p-3 bg-white border rounded shadow-sm">
                                <div class="me-3">
                                    @if(in_array($ext, ['doc', 'docx']))
                                        <i class="bi bi-file-earmark-word text-primary" style="font-size: 2.5rem;"></i>
                                    @elseif(in_array($ext, ['xls', 'xlsx']))
                                        <i class="bi bi-file-earmark-excel text-success" style="font-size: 2.5rem;"></i>
                                    @else
                                        <i class="bi bi-file-earmark text-secondary" style="font-size: 2.5rem;"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="mb-1 fw-bold text-truncate" title="{{ basename($pengumuman->lampiran) }}">
                                        {{ basename($pengumuman->lampiran) }}
                                    </p>
                                    <small class="text-muted">Format: {{ strtoupper($ext) }}</small>
                                </div>
                                <div class="ms-2">
                                    <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-primary me-1">
                                        <i class="bi bi-eye"></i> Lihat
                                    </a>
                                    <a href="{{ $filePath }}" download class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-download"></i> Unduh
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</div>
@endsection