@extends('layouts.app')

@section('title', 'Riwayat Permohonan Surat')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Riwayat Permohonan Surat Saya</h2>
            <p class="text-muted mb-0">Pantau status pengajuan dan unduh surat yang telah disetujui.</p>
        </div>
        {{-- ✅ TOMBOL INI MENGARAHKAN KE PILIH TEMPLATE --}}
        <a href="{{ route('surat.pilih-template') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Buat Surat Baru
        </a>
    </div>

    {{-- ✅ NOTIFIKASI SUKSES: Pastikan blok ini ada dan tidak terhapus --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>Berhasil!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ✅ NOTIFIKASI ERROR: Untuk menangkap jika ada masalah --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Gagal!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Tanggal Ajukan</th>
                            <th>Jenis Surat</th>
                            <th>Nomor Surat</th>
                            <th>Perihal</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($submissions as $sub)
                        @php
                            $dataJson = is_string($sub->data_json) ? json_decode($sub->data_json, true) : ($sub->data_json ?? []);
                            $perihal = $dataJson['acara'] ?? $dataJson['perihal'] ?? $dataJson['judul'] ?? '-';
                            
                            $badgeClass = 'bg-secondary';
                            $statusLabel = ucfirst($sub->status);
                            
                            if ($sub->status === 'submitted') {
                                $badgeClass = 'bg-warning text-dark';
                                $statusLabel = 'Menunggu Verifikasi';
                            } elseif ($sub->status === 'approved') {
                                $badgeClass = 'bg-success';
                                $statusLabel = 'Disetujui';
                            } elseif ($sub->status === 'rejected') {
                                $badgeClass = 'bg-danger';
                                $statusLabel = 'Ditolak';
                            }
                        @endphp
                        <tr>
                            <td class="ps-4">{{ $sub->created_at->format('d M Y') }}</td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10">
                                    {{ $sub->template->name ?? 'Surat' }}
                                </span>
                            </td>
                            <td>
                                @if($sub->status === 'approved' && $sub->nomor_surat)
                                    <strong class="text-primary">{{ $sub->nomor_surat }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($perihal, 40) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                @if($sub->status === 'rejected' && $sub->rejection_reason)
                                    <br>
                                    <small class="text-danger mt-1 d-inline-block" style="cursor: pointer;" title="Alasan: {{ $sub->rejection_reason }}">
                                        <i class="bi bi-info-circle"></i> Lihat Alasan
                                    </small>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('surat.show', $sub) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    
                                    {{-- ✅ TOMBOL DOWNLOAD: Hanya muncul jika Disetujui DAN ada file PDF --}}
                                    @if($sub->status === 'approved' && $sub->pdf_path)
                                        <a href="{{ route('surat.download', $sub) }}" class="btn btn-sm btn-success fw-bold" title="Download PDF Surat">
                                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download
                                        </a>
                                    @elseif($sub->status === 'approved' && !$sub->pdf_path)
                                        <span class="badge bg-info text-dark">PDF Diproses...</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Anda belum memiliki riwayat pengajuan surat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    @if($submissions->hasPages())
    <div class="mt-3">
        {{ $submissions->links() }}
    </div>
    @endif
</div>
@endsection