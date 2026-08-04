@extends('layouts.app')
@section('title', 'Tambah Sub Bagian')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0" style="color: #1F3864;">Tambah Sub Bagian Baru</h2>
        <a href="{{ route('kepegawaian.sub-bagian.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('kepegawaian.sub-bagian.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Induk Bagian <span class="text-danger">*</span></label>
                    <select name="bagian_id" class="form-select @error('bagian_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Bagian --</option>
                        @foreach($bagians as $bagian)
                            <option value="{{ $bagian->id }}" {{ old('bagian_id') == $bagian->id ? 'selected' : '' }}>{{ $bagian->nama_bagian }}</option>
                        @endforeach
                    </select>
                    @error('bagian_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Sub Bagian <span class="text-danger">*</span></label>
                    <input type="text" name="nama_sub_bagian" class="form-control @error('nama_sub_bagian') is-invalid @enderror" value="{{ old('nama_sub_bagian') }}" required placeholder="Contoh: Sub Bagian Umum">
                    @error('nama_sub_bagian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('kepegawaian.sub-bagian.index') }}" class="btn btn-light border">Batal</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-2"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection