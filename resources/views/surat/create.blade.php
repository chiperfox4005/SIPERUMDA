@extends('layouts.app')

@section('title', 'Buat Surat')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Buat Surat Baru</h2>
            <p class="text-muted mb-0">Isi formulir di bawah untuk membuat surat resmi.</p>
        </div>
        <a href="{{ route('surat.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <form action="{{ route('surat.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Informasi Surat</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jenis Surat <span class="text-danger">*</span></label>
                                <select name="jenis_surat" class="form-select @error('jenis_surat') is-invalid @enderror" required>
                                    <option value="">-- Pilih Jenis Surat --</option>
                                    @foreach($jenisOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('jenis_surat') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jenis_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Surat <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_surat" class="form-control @error('tanggal_surat') is-invalid @enderror" 
                                       value="{{ old('tanggal_surat', date('Y-m-d')) }}" required>
                                @error('tanggal_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Perihal <span class="text-danger">*</span></label>
                                <input type="text" name="perihal" class="form-control @error('perihal') is-invalid @enderror" 
                                       value="{{ old('perihal') }}" required placeholder="Contoh: Penugasan Pekerjaan di Cabang Barat">
                                @error('perihal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Isi Surat <span class="text-danger">*</span></label>
                                <textarea name="isi_surat" rows="10" class="form-control @error('isi_surat') is-invalid @enderror" 
                                          required placeholder="Tulis isi surat di sini...">{{ old('isi_surat') }}</textarea>
                                @error('isi_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">Gunakan ENTER untuk baris baru.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-person me-2"></i>Penerima</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Penerima</label>
                            <input type="text" name="penerima_nama" class="form-control" 
                                   value="{{ old('penerima_nama') }}" placeholder="Nama lengkap">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">NIP</label>
                            <input type="text" name="penerima_nip" class="form-control" 
                                   value="{{ old('penerima_nip') }}" placeholder="NIP">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="penerima_jabatan" class="form-control" 
                                   value="{{ old('penerima_jabatan') }}" placeholder="Jabatan">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tujuan/Unit</label>
                            <input type="text" name="tujuan" class="form-control" 
                                   value="{{ old('tujuan') }}" placeholder="Contoh: Cabang Barat">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-pen me-2"></i>Penandatangan</h5>
                    </div>
                    <div class="card-body">
                        <label class="form-label">Pejabat Penandatangan</label>
                        <select name="penandatangan_id" class="form-select">
                            <option value="">-- Pilih Pejabat --</option>
                            @foreach($penandatanganList as $p)
                                <option value="{{ $p->id }}" {{ old('penandatangan_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} - {{ $p->position }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" name="status" value="submitted" class="btn btn-primary">
                                <i class="bi bi-send me-2"></i>Ajukan Surat
                            </button>
                            <button type="submit" name="status" value="draft" class="btn btn-outline-secondary">
                                <i class="bi bi-save me-2"></i>Simpan Draft
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection