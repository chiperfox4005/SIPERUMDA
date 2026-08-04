@extends('layouts.app')

@section('title', 'Profil Saya')

@push('styles')
<style>
    :root {
        --primary-color: #1F3864;
        --card-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    
    body { 
        background-color: #f4f6f9; 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    }

    .profile-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, #2a4a85 100%);
        border-radius: 12px;
        padding: 28px;
        color: white;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(31, 56, 100, 0.15);
    }

    .card-custom {
        border: none;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        margin-bottom: 24px;
        overflow: hidden;
        background: white;
    }
    
    .card-custom .card-header {
        background: white;
        border-bottom: 1px solid #e9ecef;
        padding: 16px 24px;
        font-weight: 600;
        color: var(--primary-color);
        font-size: 1.1rem;
    }
    
    .card-custom .card-body {
        padding: 24px;
    }

    .profile-avatar-wrapper {
        text-align: center;
        padding: 24px 0;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin: 0 auto 16px auto;
        object-fit: cover;
    }

    .profile-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: #212529;
        margin-bottom: 4px;
    }

    .profile-nip {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 12px;
    }

    .profile-role {
        display: inline-block;
        background-color: rgba(31, 56, 100, 0.08);
        color: var(--primary-color);
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 24px;
    }

    .profile-divider {
        border-top: 1px solid #e9ecef;
        margin: 20px 0;
    }

    .profile-info-item {
        margin-bottom: 16px;
    }

    .profile-info-label {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 4px;
        display: block;
        font-weight: 500;
    }

    .profile-info-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #212529;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid #ced4da;
        padding: 10px 14px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(31, 56, 100, 0.1);
        outline: none;
    }

    .form-control:disabled {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    .btn-primary-unified {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        border-radius: 8px;
        padding: 10px 24px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary-unified:hover {
        background-color: #152747;
        border-color: #152747;
        color: white;
        transform: translateY(-1px);
    }

    .btn-light-unified {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        padding: 10px 24px;
        font-weight: 500;
        border-radius: 8px;
        color: #495057;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
    }

    .btn-light-unified:hover {
        background-color: #e9ecef;
        color: #212529;
    }

    .form-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid #e9ecef;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="profile-header">
        <h1 class="fw-bold mb-2" style="font-size: 1.75rem;">Profil Saya</h1>
        <p class="mb-0" style="opacity: 0.9; font-size: 1rem;">Kelola informasi pribadi, foto profil, dan keamanan akun Anda.</p>
    </div>

    <div class="row g-4">
        <!-- Sidebar Informasi -->
        <div class="col-lg-4">
            <div class="card-custom">
                <div class="card-body">
                    <div class="profile-avatar-wrapper">
                        <img src="{{ $user->foto_profil ? asset('storage/'.$user->foto_profil) : 'https://ui-avatars.com/api/?name='.urlencode($user->nama_lengkap).'&background=1F3864&color=fff&size=128' }}" 
                             class="profile-avatar" alt="Foto Profil">
                        <h4 class="profile-name">{{ $user->nama_lengkap }}</h4>
                        <p class="profile-nip">NIP: {{ $user->nip }}</p>
                        <span class="profile-role">{{ $user->roles->first()->name ?? 'Pegawai' }}</span>
                    </div>
                    
                    <div class="profile-divider"></div>
                    
                    <div class="profile-info-item">
                        <span class="profile-info-label"><i class="bi bi-building me-2"></i>Bagian</span>
                        <span class="profile-info-value">{{ $user->bagian->nama_bagian ?? '-' }}</span>
                    </div>
                    
                    <div class="profile-info-item">
                        <span class="profile-info-label"><i class="bi bi-diagram-3 me-2"></i>Sub Bagian</span>
                        <span class="profile-info-value">{{ $user->subBagian->nama_sub_bagian ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Edit -->
        <div class="col-lg-8">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Informasi Dasar -->
                <div class="card-custom">
                    <div class="card-header">
                        <i class="bi bi-person-gear me-2"></i>Informasi Dasar
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">NIP</label>
                                <input type="text" class="form-control" value="{{ $user->nip }}" disabled>
                                <small class="text-muted" style="font-size: 0.8rem;">NIP tidak dapat diubah.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required>
                                @error('nama_lengkap') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Foto Profil</label>
                                <input type="file" name="foto_profil" class="form-control @error('foto_profil') is-invalid @enderror" accept="image/*">
                                <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">Format: JPG, PNG. Maksimal 2MB. Kosongkan jika tidak ingin mengubah.</small>
                                @error('foto_profil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Keamanan -->
                <div class="card-custom">
                    <div class="card-header">
                        <i class="bi bi-shield-lock me-2"></i>Keamanan (Ubah Password)
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Password Lama</label>
                                <input type="password" name="password_lama" class="form-control @error('password_lama') is-invalid @enderror" placeholder="Kosongkan jika tidak ingin mengubah password">
                                @error('password_lama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="password_baru" class="form-control @error('password_baru') is-invalid @enderror" placeholder="Minimal 8 karakter">
                                @error('password_baru') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="password_baru_confirmation" class="form-control" placeholder="Ulangi password baru">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="form-footer">
                    <a href="{{ route('dashboard') }}" class="btn-light-unified">
                        <i class="bi bi-x-lg me-2"></i>Batal
                    </a>
                    <button type="submit" class="btn-primary-unified">
                        <i class="bi bi-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection