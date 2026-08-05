@extends('layouts.app')

@section('title', 'Edit Ruangan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Edit Ruangan</h2>
            <p class="text-muted mb-0">Perbarui informasi ruangan "{{ $ruangan->nama_ruangan }}".</p>
        </div>
        <a href="{{ route('ruangan.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('ruangan.update', $ruangan->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="nama_ruangan" class="form-label fw-semibold">Nama Ruangan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_ruangan') is-invalid @enderror" 
                                       id="nama_ruangan" name="nama_ruangan" value="{{ old('nama_ruangan', $ruangan->nama_ruangan) }}" required>
                                @error('nama_ruangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="kode_ruangan" class="form-label fw-semibold">Kode Ruangan</label>
                                <input type="text" class="form-control @error('kode_ruangan') is-invalid @enderror" 
                                       id="kode_ruangan" name="kode_ruangan" value="{{ old('kode_ruangan', $ruangan->kode_ruangan) }}">
                            </div>

                            <div class="col-md-6">
                                <label for="kategori" class="form-label fw-semibold">Kategori</label>
                                <input type="text" class="form-control @error('kategori') is-invalid @enderror" 
                                       id="kategori" name="kategori" value="{{ old('kategori', $ruangan->kategori) }}">
                            </div>

                            <div class="col-md-6">
                                <label for="kapasitas" class="form-label fw-semibold">Kapasitas (Orang) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('kapasitas') is-invalid @enderror" 
                                       id="kapasitas" name="kapasitas" value="{{ old('kapasitas', $ruangan->kapasitas) }}" min="1" required>
                            </div>

                            <div class="col-md-6">
                                <label for="lokasi" class="form-label fw-semibold">Lokasi</label>
                                <input type="text" class="form-control @error('lokasi') is-invalid @enderror" 
                                       id="lokasi" name="lokasi" value="{{ old('lokasi', $ruangan->lokasi) }}">
                            </div>

                            <div class="col-md-12">
                                <label for="fasilitas" class="form-label fw-semibold">Fasilitas</label>
                                <textarea class="form-control @error('fasilitas') is-invalid @enderror" 
                                          id="fasilitas" name="fasilitas" rows="3">{{ old('fasilitas', $ruangan->fasilitas) }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Memerlukan Surat?</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="memerlukan_surat" name="memerlukan_surat" value="1" 
                                           {{ old('memerlukan_surat', $ruangan->memerlukan_surat) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="memerlukan_surat">Ya, wajib melampirkan surat</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="aktif" {{ old('status', $ruangan->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="non-aktif" {{ old('status', $ruangan->status) == 'non-aktif' ? 'selected' : '' }}>Non-Aktif</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label for="foto" class="form-label fw-semibold">Foto Ruangan</label>
                                <input type="file" class="form-control @error('foto') is-invalid @enderror" id="foto" name="foto">
                                @if($ruangan->foto)
                                    <div class="mt-2">
                                        <small class="text-muted">Foto saat ini:</small><br>
                                        <img src="{{ asset('storage/' . $ruangan->foto) }}" alt="Foto Ruangan" class="img-thumbnail mt-1" style="max-height: 150px;">
                                    </div>
                                @endif
                                @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                            <a href="{{ route('ruangan.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary px-4" style="background-color: #1F3864; border-color: #1F3864;">
                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection