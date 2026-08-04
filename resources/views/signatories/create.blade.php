@extends('layouts.app')

@section('title', 'Tambah Pejabat Penandatangan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Tambah Pejabat Penandatangan</h2>
            <p class="text-muted mb-0">Isi data pejabat yang berwenang menandatangani surat.</p>
        </div>
        <a href="{{ route('signatories.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('signatories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row g-3">
                    <!-- Nama Pejabat -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required placeholder="Contoh: Dr. H. Ahmad Fauzi, M.M.">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Jabatan -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jabatan <span class="text-danger">*</span></label>
                        <input type="text" name="position" class="form-control @error('position') is-invalid @enderror" 
                               value="{{ old('position') }}" required placeholder="Contoh: Direktur Utama">
                        @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- NIP -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">NIP <span class="text-danger">*</span></label>
                        <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" 
                               value="{{ old('nip') }}" required placeholder="Contoh: 196501011990011001">
                        @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" 
                                   value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Pejabat Aktif (Tampil di Dropdown)</label>
                        </div>
                    </div>

                    <!-- Upload Tanda Tangan -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Upload Tanda Tangan Digital <span class="text-danger">*</span></label>
                        <input type="file" name="signature_image" class="form-control @error('signature_image') is-invalid @enderror" 
                               accept=".png,.jpg,.jpeg" required>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Format: PNG (background transparan) atau JPG/JPEG. Maksimal 2MB.
                        </small>
                        @error('signature_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Preview TTD -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Preview Tanda Tangan</label>
                        <div class="border rounded p-3 bg-light text-center" style="min-height: 120px;">
                            <img id="previewTTD" src="" alt="Preview TTD" style="max-height: 100px; display: none;">
                            <p id="previewText" class="text-muted mb-0 mt-3">Belum ada file yang dipilih</p>
                        </div>
                    </div>

                    <!-- Validitas (Opsional) -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Berlaku Sejak</label>
                        <input type="date" name="valid_from" class="form-control @error('valid_from') is-invalid @enderror" 
                               value="{{ old('valid_from', date('Y-m-d')) }}">
                        @error('valid_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Berlaku Hingga (Kosongkan jika tidak ada batas)</label>
                        <input type="date" name="valid_until" class="form-control @error('valid_until') is-invalid @enderror" 
                               value="{{ old('valid_until') }}">
                        @error('valid_until') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Urutan Tampilan -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Urutan Tampilan</label>
                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" 
                               value="{{ old('sort_order', 0) }}" min="0">
                        <small class="text-muted">Angka lebih kecil akan tampil lebih atas di dropdown.</small>
                        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                    <a href="{{ route('signatories.index') }}" class="btn btn-light border">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-2"></i>Simpan Pejabat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Preview gambar TTD saat file dipilih
    document.querySelector('input[name="signature_image"]').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('previewTTD');
        const previewText = document.getElementById('previewText');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                previewText.style.display = 'none';
            }
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
            previewText.style.display = 'block';
        }
    });
</script>
@endpush
@endsection