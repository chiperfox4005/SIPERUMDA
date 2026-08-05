@extends('layouts.app')

@section('title', 'System Backup')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Cadangan Sistem (Backup)</h2>
            <p class="text-muted mb-0">Unduh cadangan database MySQL terbaru untuk keamanan data.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-5 text-center">
            <i class="bi bi-database-check text-primary" style="font-size: 4rem;"></i>
            <h4 class="mt-3 fw-bold">Backup Database MySQL</h4>
            <p class="text-muted mb-4">
                Fitur ini akan membuat file <code>.sql</code> dari seluruh database <strong>{{ env('DB_DATABASE') }}</strong> 
                dan secara otomatis mengunduhnya ke perangkat Anda.
            </p>
            
            <form action="{{ route('admin.backup.action') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-download me-2"></i> Buat & Unduh Backup Sekarang
                </button>
            </form>

            <div class="alert alert-info mt-4 text-start small">
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>Catatan:</strong> Fitur ini memerlukan fungsi <code>exec()</code> yang aktif di <code>php.ini</code> (biasanya sudah aktif di Laragon/XAMPP). 
                Jika gagal, pastikan path <code>mysqldump</code> sudah terdaftar di Environment Variables Windows Anda.
            </div>
        </div>
    </div>
</div>
@endsection