@extends('layouts.app')

@section('title', 'Verifikasi & Persetujuan Surat')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Verifikasi & Persetujuan Surat</h2>
            <p class="text-muted mb-0">Kelola antrian pengajuan dan lihat hasil surat yang telah diverifikasi.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Tanggal</th>
                            <th>Jenis Surat</th>
                            <th>Pengaju</th>
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
                            if ($sub->status === 'submitted') { $badgeClass = 'bg-warning text-dark'; $statusLabel = 'Menunggu'; }
                            elseif ($sub->status === 'approved') { $badgeClass = 'bg-success'; $statusLabel = 'Terverifikasi'; }
                            elseif ($sub->status === 'rejected') { $badgeClass = 'bg-danger'; $statusLabel = 'Ditolak'; }
                        @endphp
                        <tr>
                            <td class="ps-4">{{ $sub->created_at->format('d M Y') }}</td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10">{{ $sub->template->name ?? 'Surat' }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $sub->creator->nama_lengkap ?? 'User' }}</div>
                                <small class="text-muted">NIP: {{ $sub->user_id }}</small>
                            </td>
                            <td>{{ Str::limit($perihal, 35) }}</td>
                            <td class="text-center"><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    @if($sub->status === 'submitted')
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalApproval{{ $sub->id }}">
                                            <i class="bi bi-check-circle me-1"></i> Verifikasi
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalReject{{ $sub->id }}">
                                            <i class="bi bi-x-circle me-1"></i> Tolak
                                        </button>
                                    @endif
                                    @if($sub->status === 'approved')
                                        <a href="{{ route('surat.download', $sub) }}" class="btn btn-sm btn-success">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF
                                        </a>
                                    @endif
                                    @if($sub->status === 'rejected')
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalRejectDetail{{ $sub->id }}">
                                            <i class="bi bi-eye me-1"></i> Alasan
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        @if($sub->status === 'submitted')
                        <!-- Modal Approval -->
                        <div class="modal fade" id="modalApproval{{ $sub->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <form action="{{ route('surat.approve', $sub) }}" method="POST">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">Verifikasi Surat</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-light border mb-4">
                                                <h6 class="fw-bold mb-2"><i class="bi bi-file-earmark-text me-2"></i>Data Pengajuan:</h6>
                                                <ul class="mb-0 small">
                                                    @foreach($dataJson as $key => $value)
                                                        @if(!empty($value))
                                                        <li class="mb-1"><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? implode(', ', $value) : $value }}</li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-7">
                                                    <label class="form-label fw-semibold">Nomor Surat <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" name="nomor_surat" id="nomor_surat_{{ $sub->id }}" class="form-control" required placeholder="001/SIPERUMDA/VIII/2026">
                                                        <button type="button" class="btn btn-outline-secondary" id="btnAuto_{{ $sub->id }}" onclick="autoGenerateNomor({{ $sub->id }})">
                                                            <i class="bi bi-magic"></i> Auto
                                                        </button>
                                                    </div>
                                                    <small class="text-muted" id="autoStatus_{{ $sub->id }}"></small>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label fw-semibold">Penandatangan <span class="text-danger">*</span></label>
                                                    <select name="signatory_id" class="form-select" required>
                                                        <option value="">-- Pilih Pejabat --</option>
                                                        @foreach(\App\Models\Signatory::where('is_active', true)->orderBy('name')->get() as $sign)
                                                            <option value="{{ $sign->id }}">{{ $sign->name }} ({{ $sign->position }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Setujui & Generate PDF</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Modal Reject -->
                        <div class="modal fade" id="modalReject{{ $sub->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('surat.reject', $sub) }}" method="POST">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Tolak Pengajuan</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                                                <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Contoh: Data peserta belum lengkap."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger">Kirim Penolakan</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif

                        @if($sub->status === 'rejected')
                        <div class="modal fade" id="modalRejectDetail{{ $sub->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Alasan Penolakan</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="mb-0">{{ $sub->rejection_reason }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>Tidak ada data pengajuan surat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ✅ PERBAIKAN: Auto Generate Nomor Surat dengan Error Handling
    function autoGenerateNomor(submissionId) {
        const input = document.getElementById(`nomor_surat_${submissionId}`);
        const btn = document.getElementById(`btnAuto_${submissionId}`);
        const statusText = document.getElementById(`autoStatus_${submissionId}`);
        
        // Simpan text asli button
        const originalText = btn.innerHTML;
        
        // Tampilkan loading
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';
        btn.disabled = true;
        if (statusText) statusText.innerHTML = '<span class="text-info"><i class="bi bi-hourglass-split"></i> Sedang generate nomor...</span>';

        // ✅ PERBAIKAN: Tambah CSRF token dan error handling
        fetch('{{ route("surat.generate-nomor") }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.nomor_surat) {
                input.value = data.nomor_surat;
                if (statusText) {
                    statusText.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> Nomor berhasil di-generate!</span>';
                }
                btn.innerHTML = '<i class="bi bi-check-circle"></i> Selesai';
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-success');
                
                // Reset button setelah 2 detik
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            } else {
                throw new Error('Response tidak valid');
            }
        })
        .catch(error => {
            console.error('Error generate nomor:', error);
            
            // ✅ FALLBACK: Generate manual jika API gagal
            const now = new Date();
            const tahun = now.getFullYear();
            const bulanRomawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][now.getMonth()];
            const randomNum = String(Math.floor(Math.random() * 999) + 1).padStart(3, '0');
            const fallbackNomor = `${randomNum}/SIPERUMDA/${bulanRomawi}/${tahun}`;
            
            input.value = fallbackNomor;
            
            if (statusText) {
                statusText.innerHTML = `<span class="text-warning"><i class="bi bi-exclamation-triangle"></i> Generate otomatis gagal. Menggunakan nomor alternatif: ${fallbackNomor}</span>`;
            }
            
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>
@endpush
@endsection