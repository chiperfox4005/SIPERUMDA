@extends('layouts.app')

@section('title', 'Detail Permohonan Surat')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0" style="color: #1F3864;">Detail Permohonan Surat</h2>
        <a href="{{ route('surat.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Riwayat
        </a>
    </div>

    <div class="row">
        <!-- Kolom Kiri: Isi Surat (Data Dinamis) -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-file-earmark-text me-2"></i>{{ $submission->template->name ?? 'Jenis Surat' }}
                    </h5>
                    @php
                        $badgeClass = match($submission->status) {
                            'submitted' => 'bg-warning text-dark',
                            'approved' => 'bg-success',
                            'rejected' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                        $statusLabel = match($submission->status) {
                            'submitted' => 'Menunggu Verifikasi',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            default => ucfirst($submission->status)
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} px-3 py-2">{{ $statusLabel }}</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        {{-- Loop melalui data JSON yang dinamis --}}
                        @foreach($dataJson as $key => $value)
                            @if(!empty($value))
                            <div class="col-md-12">
                                <label class="text-muted small text-uppercase fw-bold mb-1">
                                    {{ ucwords(str_replace('_', ' ', $key)) }}
                                </label>
                                <div class="p-3 bg-light rounded border">
                                    @if($key === 'peserta')
                                        {{-- Format peserta agar baris baru (\r\n) terbaca sebagai <br> --}}
                                        {!! nl2br(e($value)) !!}
                                    @else
                                        {{ $value }}
                                    @endif
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Metadata & Aksi -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Informasi Pengajuan</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Tanggal Pengajuan</span>
                            <span class="fw-semibold">{{ $submission->created_at->format('d M Y, H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Pengaju</span>
                            <span class="fw-semibold">{{ $submission->creator->nama_lengkap ?? 'Unknown' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">NIP Pengaju</span>
                            <span class="fw-semibold">{{ $submission->user_id }}</span>
                        </li>
                        
                        @if($submission->status === 'approved' && $submission->nomor_surat)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-success bg-opacity-10 rounded mt-2">
                            <span class="text-success fw-bold">Nomor Surat</span>
                            <span class="fw-bold text-success">{{ $submission->nomor_surat }}</span>
                        </li>
                        @endif
                        
                        @if($submission->status === 'rejected' && $submission->rejection_reason)
                        <li class="list-group-item px-0 bg-danger bg-opacity-10 mt-2 rounded p-3">
                            <span class="text-danger fw-bold d-block mb-1"><i class="bi bi-x-circle me-1"></i>Alasan Penolakan:</span>
                            <span class="text-danger">{{ $submission->rejection_reason }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    @if($submission->status === 'approved' && $submission->pdf_path)
                        <a href="{{ route('surat.download', $submission) }}" class="btn btn-success w-100 mb-2 fw-bold">
                            <i class="bi bi-file-earmark-pdf-fill me-2"></i>Download PDF Surat
                        </a>
                    @elseif($submission->status === 'approved' && !$submission->pdf_path)
                        <button class="btn btn-secondary w-100 mb-2" disabled>
                            <i class="bi bi-hourglass-split me-2"></i>PDF Sedang Diproses
                        </button>
                    @endif
                    
                    @if($submission->status === 'submitted')
                        <div class="alert alert-info small mb-0 border-0">
                            <i class="bi bi-clock-history me-1"></i> Surat Anda sedang menunggu persetujuan dari Sekretariat.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection