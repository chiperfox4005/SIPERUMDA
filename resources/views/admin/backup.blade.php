@extends('layouts.app')

@section('title', 'Backup & Restore')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Halaman -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--primary-color, #1F3864);">Backup & Restore Database</h2>
            <p class="text-muted mb-0">Kelola cadangan data sistem untuk keamanan dan pemulihan.</p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- 1. Section Backup (Dengan Form POST) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-database-check text-primary" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Backup Database</h4>
                    <p class="text-muted mb-4 px-3">
                        Klik tombol di bawah untuk membuat cadangan (backup) database MySQL saat ini. File akan otomatis disimpan di folder <code>storage/app/backups/</code>.
                    </p>
                    
                    <!-- PERBAIKAN: Gunakan route 'admin.backup.action' -->
                    <form action="{{ route('admin.backup.action') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-download me-2"></i> Backup Database Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 2. Section Restore (Placeholder) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-database-arrow-down text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Restore Database</h4>
                    <p class="text-muted mb-4 px-3">
                        Fitur ini memungkinkan Anda mengembalikan database ke kondisi sebelumnya menggunakan file backup yang sudah ada.
                    </p>
                    <button class="btn btn-outline-secondary btn-lg px-5" disabled>
                        <i class="bi bi-upload me-2"></i> Pilih File Backup (Segera Hadir)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Riwayat Backup (Opsional) -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Riwayat File Backup</h5>
        </div>
        <div class="card-body">
            @php
                $backupFiles = [];
                $backupDir = storage_path('app/backups');
                if (is_dir($backupDir)) {
                    $files = scandir($backupDir);
                    $backupFiles = array_diff($files, ['.', '..']);
                    rsort($backupFiles); // Urutkan dari yang terbaru
                }
            @endphp

            @if(count($backupFiles) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama File</th>
                                <th>Ukuran</th>
                                <th>Tanggal Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($backupFiles, 0, 5) as $file)
                            <tr>
                                <td class="fw-semibold">
                                    <i class="bi bi-file-earmark-zip me-2 text-primary"></i>{{ $file }}
                                </td>
                                <td>{{ number_format(filesize($backupDir . '/' . $file) / 1024, 2) }} KB</td>
                                <td>{{ date('d M Y, H:i', filemtime($backupDir . '/' . $file)) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                    <p class="mt-2 mb-0">Belum ada file backup yang tersedia. Silakan lakukan backup terlebih dahulu.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection