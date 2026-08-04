@extends('layouts.app')

@section('title', 'Detail Surat')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Detail Surat</h2>
            <p class="text-muted mb-0">No: {{ $surat->nomor_surat ?? 'Belum ada nomor' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('surat.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
            @if($surat->status === 'approved')
                <a href="{{ route('surat.download', $surat) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Download PDF
                </a>
            @endif
            @if($surat->status === 'draft' && $surat->dibuat_oleh === (string) auth()->user()->nip)
                <a href="{{ route('surat.edit', $surat) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-2"></i>Edit
                </a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Informasi Surat</h5>
                    <span class="badge {{ $surat->status_badge['class'] }} fs-6">
                        {{ $surat->status_badge['label'] }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Jenis Surat</div>
                        <div class="col-md-8">{{ $surat->jenis_label }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Nomor Surat</div>
                        <div class="col-md-8">{{ $surat->nomor_surat ?? '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Tanggal</div>
                        <div class="col-md-8">{{ $surat->tanggal_surat->locale('id')->isoFormat('dddd, D MMMM Y') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Perihal</div>
                        <div class="col-md-8">{{ $surat->perihal }}</div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="fw-semibold mb-2">Isi Surat:</div>
                        <div class="bg-light p-4 rounded" style="white-space: pre-wrap; font-family: 'Times New Roman', serif; line-height: 1.8;">
                            {{ $surat->isi_surat }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>Penerima</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Nama:</strong> {{ $surat->penerima_nama ?? '-' }}</p>
                    <p class="mb-1"><strong>NIP:</strong> {{ $surat->penerima_nip ?? '-' }}</p>
                    <p class="mb-1"><strong>Jabatan:</strong> {{ $surat->penerima_jabatan ?? '-' }}</p>
                    <p class="mb-0"><strong>Tujuan:</strong> {{ $surat->tujuan ?? '-' }}</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-pen me-2"></i>Penandatangan</h6>
                </div>
                <div class="card-body">
                    @if($surat->penandatangan)
                        <p class="mb-1"><strong>{{ $surat->penandatangan->name }}</strong></p>
                        <p class="mb-1 text-muted">{{ $surat->penandatangan->position }}</p>
                        <p class="mb-0 text-muted small">NPP: {{ $surat->penandatangan->npp }}</p>
                    @else
                        <p class="text-muted mb-0">Belum ditentukan</p>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Metadata</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Dibuat oleh:</strong><br> {{ $surat->pembuat->nama_lengkap ?? $surat->dibuat_oleh }}</p>
                    <p class="mb-1"><strong>Tanggal dibuat:</strong><br> {{ $surat->created_at->format('d M Y, H:i') }}</p>
                    @if($surat->penyetuju)
                        <p class="mb-1 text-success"><strong>Disetujui oleh:</strong><br> {{ $surat->penyetuju->nama_lengkap }}</p>
                        <p class="mb-0 text-success"><strong>Tanggal:</strong><br> {{ $surat->tanggal_disetujui->format('d M Y, H:i') }}</p>
                    @endif
                    @if($surat->catatan_penolakan)
                        <div class="alert alert-danger mt-3 mb-0 small">
                            <strong>Catatan Penolakan:</strong><br>
                            {{ $surat->catatan_penolakan }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection