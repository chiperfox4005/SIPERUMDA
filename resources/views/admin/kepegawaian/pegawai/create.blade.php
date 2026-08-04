@extends('layouts.app')
@section('title', 'Tambah Pegawai')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0" style="color: #1F3864;">Tambah Pegawai Baru</h2>
        <a href="{{ route('kepegawaian.pegawai.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('kepegawaian.pegawai.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">NIP <span class="text-danger">*</span></label>
                        <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip') }}" required>
                        <small class="text-muted">NIP juga akan dijadikan sebagai password default.</small>
                        @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap') }}" required>
                        @error('nama_lengkap') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Bagian <span class="text-danger">*</span></label>
                        <select name="bagian_id" class="form-select @error('bagian_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Bagian --</option>
                            @foreach($bagians as $bagian)
                                <option value="{{ $bagian->id }}" {{ old('bagian_id') == $bagian->id ? 'selected' : '' }}>{{ $bagian->nama_bagian }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Sub Bagian <span class="text-danger">*</span></label>
                        <select name="sub_bagian_id" class="form-select @error('sub_bagian_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Sub Bagian --</option>
                            @foreach(\App\Models\SubBagian::all() as $sub)
                                <option value="{{ $sub->id }}" {{ old('sub_bagian_id') == $sub->id ? 'selected' : '' }}>{{ $sub->nama_sub_bagian }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Role / Hak Akses <span class="text-danger">*</span></label>
                        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ✅ KOLOM BARU: Password Awal --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password Awal</label>
                        <input type="text" name="password" class="form-control @error('password') is-invalid @enderror" value="{{ old('password') }}" placeholder="Contoh: 123456">
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Kosongkan kolom ini jika ingin password default sama dengan NIP.</small>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('kepegawaian.pegawai.index') }}" class="btn btn-light border">Batal</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-2"></i>Simpan Pegawai</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection