@extends('layouts.app')

@section('title', 'Upload Dokumentasi Agenda')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-cloud-upload me-2"></i>Upload Dokumentasi Agenda</h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info">
                        <strong>Agenda:</strong> {{ $agenda->judul }}<br>
                        <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($agenda->tanggal_mulai)->isoFormat('D MMMM Y') }}
                    </div>

                    <form action="{{ route('agenda.dokumentasi.store', $agenda) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Risalah Rapat -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">1. Risalah Rapat <span class="text-danger">*</span></label>
                            <input type="file" name="risalah_rapat" class="form-control" accept=".pdf,.doc,.docx">
                            @if($dokumentasi && $dokumentasi->risalah_rapat)
                                <small class="text-muted">File sudah ada. Upload baru untuk mengganti.</small>
                            @endif
                        </div>

                        <!-- Daftar Hadir -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">2. Daftar Hadir <span class="text-danger">*</span></label>
                            <input type="file" name="daftar_hadir" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
                            @if($dokumentasi && $dokumentasi->daftar_hadir)
                                <small class="text-muted">File sudah ada. Upload baru untuk mengganti.</small>
                            @endif
                        </div>

                        <!-- Foto Kegiatan -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">3. Foto Kegiatan</label>
                            <input type="file" name="foto_kegiatan[]" class="form-control" accept="image/*" multiple>
                            <small class="text-muted">Upload maksimal 5 foto (JPEG, PNG, JPG)</small>
                        </div>

                        <!-- Lampiran Lainnya -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">4. Lampiran Lainnya</label>
                            <textarea name="lampiran_lainnya" class="form-control" rows="3" placeholder="Deskripsi lampiran lainnya...">{{ $dokumentasi->lampiran_lainnya ?? '' }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('agenda.show', $agenda) }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload me-2"></i>Upload Dokumentasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection