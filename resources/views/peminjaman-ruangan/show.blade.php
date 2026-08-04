@extends('layouts.app')

@section('title', 'Detail Peminjaman Ruangan')

@push('styles')
<style>
    .detail-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        margin-bottom: 24px;
    }
    .detail-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 16px 24px;
        font-weight: 600;
        color: #1F3864;
        font-size: 1.1rem;
    }
    .detail-body { padding: 24px; }
    .info-row { display: flex; margin-bottom: 16px; }
    .info-label { width: 160px; font-weight: 600; color: #6c757d; font-size: 0.9rem; }
    .info-value { flex: 1; color: #212529; font-weight: 500; }
    .status-badge {
        display: inline-flex; align-items: center; padding: 6px 12px;
        border-radius: 20px; font-size: 0.85rem; font-weight: 600;
    }
    .status-menunggu { background-color: #fff3cd; color: #856404; }
    .status-disetujui { background-color: #d4edda; color: #155724; }
    .status-ditolak { background-color: #f8d7da; color: #721c24; }
    .status-dibatalkan { background-color: #e2e8f0; color: #475569; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="color: #1F3864;">Detail Peminjaman Ruangan</h2>
        <p class="text-muted mb-0">ID Permohonan: #{{ $peminjamanRuangan->id }}</p>
    </div>
    <div>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

    <div class="row">
        <!-- Kolom Kiri: Detail Peminjaman -->
        <div class="col-lg-8">
            <div class="detail-card">
                <div class="detail-header"><i class="bi bi-info-circle me-2"></i>Informasi Peminjaman</div>
                <div class="detail-body">
                    <div class="info-row">
                        <div class="info-label">Nama Pemohon</div>
                        <div class="info-value">{{ $peminjamanRuangan->pemohon->nama_lengkap ?? $peminjamanRuangan->pemohon->name ?? 'Unknown' }} (NIP: {{ $peminjamanRuangan->user_id }})</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Ruangan</div>
                        <div class="info-value fw-bold text-primary">{{ $peminjamanRuangan->ruangan->nama_ruangan ?? 'Tidak Diketahui' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Tanggal Pemakaian</div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($peminjamanRuangan->tanggal_pemakaian)->locale('id')->isoFormat('dddd, D MMMM Y') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Waktu</div>
                        <div class="info-value">{{ $peminjamanRuangan->waktu_mulai }} - {{ $peminjamanRuangan->waktu_selesai }} WIB</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Jumlah Peserta</div>
                        <div class="info-value">{{ $peminjamanRuangan->jumlah_peserta ?? '-' }} Orang</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Keperluan</div>
                        <div class="info-value">{{ $peminjamanRuangan->keperluan }}</div>
                    </div>
                    
                    {{-- BAGIAN LAMPIRAN --}}
                    @if($peminjamanRuangan->lampiran)
                    <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                        <div class="info-label mb-2">Lampiran</div>
                        <div class="info-value w-100">
                            @php
                                $filePath = asset('storage/' . $peminjamanRuangan->lampiran);
                                $ext = strtolower(pathinfo($peminjamanRuangan->lampiran, PATHINFO_EXTENSION));
                            @endphp

                            @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                <img src="{{ $filePath }}" class="img-fluid rounded border mb-2" style="max-height: 350px;">
                                <div>
                                    <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-primary me-1"><i class="bi bi-zoom-in"></i> Lihat Penuh</a>
                                    <a href="{{ $filePath }}" download class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i> Unduh</a>
                                </div>
                            @elseif($ext === 'pdf')
                                <div class="ratio ratio-16x9 border rounded mb-2">
                                    <iframe src="{{ $filePath }}" title="Preview PDF"></iframe>
                                </div>
                                <div>
                                    <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-danger me-1"><i class="bi bi-file-earmark-pdf"></i> Buka PDF</a>
                                    <a href="{{ $filePath }}" download class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i> Unduh</a>
                                </div>
                            @else
                                <div class="d-flex align-items-center p-3 bg-light border rounded">
                                    <i class="bi bi-file-earmark-text fs-1 text-primary me-3"></i>
                                    <div class="flex-grow-1">
                                        <strong>{{ basename($peminjamanRuangan->lampiran) }}</strong><br>
                                        <small class="text-muted">{{ strtoupper($ext) }}</small>
                                    </div>
                                    <a href="{{ $filePath }}" download class="btn btn-sm btn-primary"><i class="bi bi-download"></i> Unduh</a>
                                </div>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="info-row">
                        <div class="info-label">Lampiran</div>
                        <div class="info-value text-muted"><em>Tidak ada lampiran</em></div>
                    </div>
                    @endif
                    {{-- AKHIR BAGIAN LAMPIRAN --}}

                    @if($peminjamanRuangan->status_persetujuan === 'ditolak' && $peminjamanRuangan->catatan_penolakan)
                    <div class="alert alert-danger mt-3 mb-0">
                        <strong><i class="bi bi-exclamation-triangle me-2"></i>Catatan Penolakan:</strong><br>
                        {{ $peminjamanRuangan->catatan_penolakan }}
                    </div>
                    @endif

                    @if($peminjamanRuangan->status_persetujuan === 'dibatalkan' && $peminjamanRuangan->catatan_pembatalan)
                    <div class="alert alert-warning mt-3 mb-0">
                        <strong><i class="bi bi-exclamation-triangle me-2"></i>Catatan Pembatalan Persetujuan:</strong><br>
                        {{ $peminjamanRuangan->catatan_pembatalan }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Status & Metadata -->
        <div class="col-lg-4">
            <div class="detail-card">
                <div class="detail-header"><i class="bi bi-shield-check me-2"></i>Status Persetujuan</div>
                <div class="detail-body text-center">
                    @php
                        $statusClass = match($peminjamanRuangan->status_persetujuan) {
                            'menunggu' => 'status-menunggu',
                            'disetujui' => 'status-disetujui',
                            'ditolak' => 'status-ditolak',
                            'dibatalkan' => 'status-dibatalkan',
                            default => 'bg-secondary text-white'
                        };
                        $statusText = match($peminjamanRuangan->status_persetujuan) {
                            'menunggu' => 'Menunggu Persetujuan',
                            'disetujui' => 'Disetujui',
                            'ditolak' => 'Ditolak',
                            'dibatalkan' => 'Dibatalkan',
                            default => ucfirst($peminjamanRuangan->status_persetujuan)
                        };
                    @endphp
                    
                    <div class="mb-4">
                        <span class="status-badge {{ $statusClass }} fs-5 px-4 py-2">{{ $statusText }}</span>
                    </div>

                    <hr class="my-4">
                    
                    <div class="text-start small">
                        <p class="mb-2"><strong>Tanggal Pengajuan:</strong><br> {{ $peminjamanRuangan->created_at->format('d M Y, H:i') }}</p>
                        
                        @if($peminjamanRuangan->disetujui_oleh)
                            <p class="mb-2 text-success"><strong>Disetujui Oleh:</strong><br> {{ $peminjamanRuangan->approver->nama_lengkap ?? $peminjamanRuangan->approver->name ?? 'Admin' }}</p>
                            <p class="mb-2 text-success"><strong>Tanggal Persetujuan:</strong><br> {{ \Carbon\Carbon::parse($peminjamanRuangan->tanggal_disetujui ?? $peminjamanRuangan->updated_at)->format('d M Y, H:i') }}</p>
                        @endif

                        @if($peminjamanRuangan->status_persetujuan === 'dibatalkan')
                            <p class="mb-2 text-warning"><strong>Dibatalkan Oleh:</strong><br> {{ $peminjamanRuangan->ditolakOleh->nama_lengkap ?? $peminjamanRuangan->ditolakOleh->name ?? 'Sekretariat' }}</p>
                            <p class="mb-0 text-warning"><strong>Tanggal Pembatalan:</strong><br> {{ \Carbon\Carbon::parse($peminjamanRuangan->tanggal_ditolak ?? $peminjamanRuangan->updated_at)->format('d M Y, H:i') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TOLAK PERMOHONAN --}}
@if($peminjamanRuangan->status_persetujuan === 'menunggu' && auth()->user()->hasRole(['Sekretariat', 'IT Administrator']))
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('peminjaman-ruangan.reject', $peminjamanRuangan) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Tolak Permohonan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">Anda akan menolak permohonan oleh <strong>{{ $peminjamanRuangan->pemohon->nama_lengkap ?? 'Pemohon' }}</strong>.</p>
                    <label class="form-label fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="catatan_penolakan" class="form-control" rows="3" required placeholder="Jelaskan alasan penolakan secara jelas dan profesional..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-lg me-2"></i>Tolak Permohonan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal Batalkan Persetujuan (Hanya untuk Sekretariat) -->
@if($peminjamanRuangan->status_persetujuan === 'disetujui' && auth()->user()->hasRole(['Sekretariat', 'IT Administrator']))
<div class="modal fade" id="revokeModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('peminjaman-ruangan.revoke', $peminjamanRuangan) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Batalkan Persetujuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        <strong>Peringatan:</strong> Tindakan ini akan membatalkan peminjaman yang sudah disetujui dan <strong>membebaskan ruangan</strong> untuk digunakan oleh pihak lain (misal: kebutuhan mendadak Direksi).
                    </div>
                    <p>Apakah Anda yakin ingin membatalkan permohonan ini? Harap berikan alasan yang jelas.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Alasan Pembatalan <span class="text-danger">*</span></label>
                        <textarea name="catatan_pembatalan" class="form-control" rows="3" required placeholder="Contoh: Ruangan dibutuhkan mendadak untuk Rapat Direksi..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak, Batalkan Aksi</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold">Ya, Batalkan Peminjaman</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

@endsection