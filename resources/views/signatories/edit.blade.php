@extends('layouts.app')

@section('title', 'Edit Pejabat Penandatangan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Edit Pejabat Penandatangan</h2>
            <p class="text-muted mb-0">Perbarui data pejabat penandatangan surat.</p>
        </div>
        <a href="{{ route('signatories.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('signatories.update', $signatory) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row g-3">
                    <!-- Nama Pejabat -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $signatory->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Jabatan -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jabatan <span class="text-danger">*</span></label>
                        <input type="text" name="position" class="form-control @error('position') is-invalid @enderror" 
                               value="{{ old('position', $signatory->position) }}" required>
                        @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- NIP -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">NIP <span class="text-danger">*</span></label>
                        <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" 
                               value="{{ old('nip', $signatory->nip) }}" required>
                        @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" 
                                   value="1" {{ old('is_active', $signatory->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Pejabat Aktif (Tampil di Dropdown)</label>
                        </div>
                    </div>

                    <!-- Upload Tanda Tangan Baru -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Upload Tanda Tangan Digital Baru (Opsional)</label>
                        <input type="file" name="signature_image" class="form-control @error('signature_image') is-invalid @enderror" 
                               accept=".png,.jpg,.jpeg">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Kosongkan jika tidak ingin mengubah TTD. Format: PNG/JPG/JPEG, Maksimal 2MB.
                        </small>
                        @error('signature_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- TTD Saat Ini -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Tanda Tangan Saat Ini</label>
                        <div class="border rounded p-3 bg-light text-center" style="min-height: 120px;">
                            @if($signatory->signature_image)
                                <img src="{{ asset('storage/' . $signatory->signature_image) }}" 
                                     alt="TTD {{ $signatory->name }}" 
                                     style="max-height: 100px;"
                                     class="img-fluid">
                            @else
                                <p class="text-muted mb-0">Belum ada tanda tangan</p>
                            @endif
                        </div>
                    </div>

                    <!-- Validitas -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Berlaku Sejak</label>
                        <input type="date" name="valid_from" class="form-control @error('valid_from') is-invalid @enderror" 
                               value="{{ old('valid_from', $signatory->valid_from?->format('Y-m-d')) }}">
                        @error('valid_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Berlaku Hingga</label>
                        <input type="date" name="valid_until" class="form-control @error('valid_until') is-invalid @enderror" 
                               value="{{ old('valid_until', $signatory->valid_until?->format('Y-m-d')) }}">
                        @error('valid_until') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Urutan Tampilan -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Urutan Tampilan</label>
                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" 
                               value="{{ old('sort_order', $signatory->sort_order ?? 0) }}" min="0">
                        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                    <a href="{{ route('signatories.index') }}" class="btn btn-light border">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-2"></i>Perbarui Pejabat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection