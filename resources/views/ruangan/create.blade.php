@extends('layouts.app')

@section('title', 'Tambah Ruangan Baru')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Tambah Ruangan Baru</h2>
            <p class="text-muted mb-0">Lengkapi data di bawah ini untuk menambahkan ruangan ke dalam sistem.</p>
        </div>
        <a href="{{ route('ruangan.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar Ruangan
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('ruangan.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            <!-- Nama Ruangan -->
                            <div class="col-md-12">
                                <label for="nama_ruangan" class="form-label fw-semibold">Nama Ruangan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_ruangan') is-invalid @enderror" 
                                       id="nama_ruangan" name="nama_ruangan" value="{{ old('nama_ruangan') }}" 
                                       placeholder="Contoh: Ruang Rapat Besar" required>
                                @error('nama_ruangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Kapasitas & Lokasi -->
                            <div class="col-md-6">
                                <label for="kapasitas" class="form-label fw-semibold">Kapasitas (Orang) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('kapasitas') is-invalid @enderror" 
                                       id="kapasitas" name="kapasitas" value="{{ old('kapasitas') }}" 
                                       placeholder="Contoh: 20" min="1" required>
                                @error('kapasitas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="lokasi" class="form-label fw-semibold">Lokasi / Lantai</label>
                                <input type="text" class="form-control @error('lokasi') is-invalid @enderror" 
                                       id="lokasi" name="lokasi" value="{{ old('lokasi') }}" 
                                       placeholder="Contoh: Lantai 2, Gedung A">
                                @error('lokasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fasilitas -->
                            <div class="col-md-12">
                                <label for="fasilitas" class="form-label fw-semibold">Fasilitas Ruangan</label>
                                <textarea class="form-control @error('fasilitas') is-invalid @enderror" 
                                          id="fasilitas" name="fasilitas" rows="3" 
                                          placeholder="Contoh: Proyektor, Whiteboard, AC, Sound System">{{ old('fasilitas') }}</textarea>
                                @error('fasilitas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Pisahkan setiap fasilitas dengan koma (,).</div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-12">
                                <label for="status" class="form-label fw-semibold">Status Ruangan <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif (Dapat Dipinjam)</option>
                                    <option value="non-aktif" {{ old('status') == 'non-aktif' ? 'selected' : '' }}>Non-Aktif (Dalam Perbaikan / Tidak Tersedia)</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                            <a href="{{ route('ruangan.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary px-4" style="background-color: #1F3864; border-color: #1F3864;">
                                <i class="bi bi-save me-2"></i>Simpan Ruangan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection