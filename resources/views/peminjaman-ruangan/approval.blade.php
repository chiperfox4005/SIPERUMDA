@extends('layouts.app')

@section('title', 'Persetujuan Peminjaman')

@push('styles')
<style>
    :root {
        --blue-900: #1e3a8a;
        --blue-600: #2563eb;
        --blue-50: #eff6ff;
        --gray-50: #f8fafc;
        --gray-200: #e2e8f0;
        --gray-500: #64748b;
        --gray-900: #0f172a;
    }

    .apv-container { max-width: 1100px; margin: 0 auto; padding: 24px 0; }
    
    /* Header */
    .apv-header { margin-bottom: 32px; }
    .apv-header h2 { font-size: 1.75rem; font-weight: 700; color: var(--blue-900); margin-bottom: 4px; letter-spacing: -0.5px; }
    .apv-header p { color: var(--gray-500); font-size: 0.95rem; margin: 0; }

    /* Stats */
    .stats-row { display: flex; gap: 16px; margin-bottom: 32px; flex-wrap: wrap; }
    .stat-box { 
        flex: 1; min-width: 120px; background: white; padding: 16px 20px; 
        border-radius: 12px; border: 1px solid var(--gray-200); 
        display: flex; align-items: center; gap: 16px; transition: all 0.2s;
    }
    .stat-box:hover { border-color: var(--blue-600); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08); }
    .stat-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .stat-icon.pending { background: #fffbeb; color: #d97706; }
    .stat-icon.approved { background: #f0fdf4; color: #16a34a; }
    .stat-icon.rejected { background: #fef2f2; color: #dc2626; }
    .stat-info h4 { margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--gray-900); line-height: 1; }
    .stat-info span { font-size: 0.8rem; color: var(--gray-500); font-weight: 500; }

    /* Filters */
    .filter-group { display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; background: var(--gray-50); padding: 6px; border-radius: 12px; width: fit-content; }
    .filter-btn { 
        padding: 8px 18px; border-radius: 8px; border: none; background: transparent; 
        color: var(--gray-500); font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: all 0.2s;
    }
    .filter-btn:hover { color: var(--blue-900); background: white; }
    .filter-btn.active { background: var(--blue-600); color: white; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2); }

    /* Cards */
    .apv-card { 
        background: white; border-radius: 12px; border: 1px solid var(--gray-200); 
        margin-bottom: 16px; position: relative; overflow: hidden; 
        transition: all 0.25s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .apv-card:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.1); 
        border-color: #bfdbfe; 
    }
    
    /* Aksen Biru di Kiri Card */
    .card-accent { position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--blue-600); }
    .card-accent.direksi { background: #f59e0b; }
    .card-accent.bentrok { background: #ef4444; }

    .card-header-custom { 
        padding: 16px 24px; border-bottom: 1px solid var(--gray-200); 
        display: flex; justify-content: space-between; align-items: center; 
        background: var(--gray-50);
    }
    .user-info { display: flex; align-items: center; gap: 14px; }
    .user-avatar { 
        width: 40px; height: 40px; border-radius: 10px; background: var(--blue-600); 
        color: white; display: flex; align-items: center; justify-content: center; 
        font-weight: 700; font-size: 1rem; letter-spacing: 0.5px;
    }
    .user-avatar.av-direksi { background: #f59e0b; color: #78350f; }
    .user-name { font-weight: 700; font-size: 0.95rem; color: var(--gray-900); margin-bottom: 2px; }
    .user-meta { font-size: 0.8rem; color: var(--gray-500); }
    
    .status-badge { 
        padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; 
        text-transform: uppercase; letter-spacing: 0.5px;
    }
    .status-menunggu { background: #fffbeb; color: #b45309; }
    .status-disetujui { background: #f0fdf4; color: #15803d; }
    .status-ditolak { background: #fef2f2; color: #b91c1c; }
    .status-dijadwalkanulang { background: var(--blue-50); color: var(--blue-600); }

    .card-body-custom { padding: 24px; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
    .info-item label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
    .info-item .val { font-size: 0.95rem; font-weight: 600; color: var(--gray-900); }
    .info-item .val.highlight { color: var(--blue-600); }

    .purpose-box { 
        margin-top: 20px; padding: 16px; background: var(--gray-50); 
        border-radius: 8px; border-left: 3px solid var(--blue-600); 
    }
    .purpose-box label { font-size: 0.75rem; font-weight: 600; color: var(--gray-500); text-transform: uppercase; margin-bottom: 6px; display: block; }
    .purpose-box .val { font-size: 0.95rem; color: var(--gray-900); line-height: 1.5; }

    /* Conflict Alert */
    .conflict-alert { 
        margin-top: 20px; padding: 16px; background: #fffbeb; 
        border: 1px solid #fcd34d; border-radius: 8px; 
        display: flex; gap: 12px; align-items: flex-start;
    }
    .conflict-alert i { color: #d97706; font-size: 1.2rem; margin-top: 2px; }
    .conflict-alert strong { color: #92400e; font-size: 0.85rem; display: block; margin-bottom: 6px; }
    .conflict-alert ul { margin: 0; padding-left: 20px; font-size: 0.85rem; color: #92400e; }

    .card-actions { 
        padding: 16px 24px; border-top: 1px solid var(--gray-200); 
        display: flex; justify-content: flex-end; gap: 10px; background: white; 
    }
    .btn-act { 
        padding: 8px 18px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; 
        cursor: pointer; display: inline-flex; align-items: center; gap: 6px; 
        text-decoration: none; transition: all 0.2s; border: 1px solid transparent;
    }
    .btn-act:hover { transform: translateY(-1px); }
    .btn-outline { background: white; border-color: var(--gray-200); color: var(--gray-900); }
    .btn-outline:hover { border-color: var(--blue-600); color: var(--blue-600); background: var(--blue-50); }
    .btn-success { background: #16a34a; color: white; }
    .btn-success:hover { background: #15803d; color: white; }
    .btn-danger { background: #dc2626; color: white; }
    .btn-danger:hover { background: #b91c1c; color: white; }

    .badge-direksi { background: #f59e0b; color: #78350f; padding: 2px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; margin-left: 8px; text-transform: uppercase; }
</style>
@endpush

@section('content')
<div class="apv-container">
    <!-- Header Dinamis -->
    <div class="apv-header">
        <h2>Persetujuan Peminjaman</h2>
        <p>Kelola antrian pengajuan peminjaman ruangan.</p>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-icon pending"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-info">
                <h4>{{ $peminjamanRuangans->where('status_persetujuan', 'menunggu')->count() }}</h4>
                <span>Menunggu</span>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon approved"><i class="bi bi-check-circle"></i></div>
            <div class="stat-info">
                <h4>{{ $peminjamanRuangans->where('status_persetujuan', 'disetujui')->count() }}</h4>
                <span>Disetujui</span>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon rejected"><i class="bi bi-x-circle"></i></div>
            <div class="stat-info">
                <h4>{{ $peminjamanRuangans->where('status_persetujuan', 'ditolak')->count() }}</h4>
                <span>Ditolak</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-group">
        <a href="{{ route('peminjaman-ruangan.approval') }}" class="filter-btn {{ !request('status') ? 'active' : '' }}">Semua</a>
        <a href="{{ route('peminjaman-ruangan.approval', ['status' => 'menunggu']) }}" class="filter-btn {{ request('status') == 'menunggu' ? 'active' : '' }}">Menunggu</a>
        <a href="{{ route('peminjaman-ruangan.approval', ['status' => 'dijadwalkan_ulang']) }}" class="filter-btn {{ request('status') == 'dijadwalkan_ulang' ? 'active' : '' }}">Dijadwalkan Ulang</a>
        <a href="{{ route('peminjaman-ruangan.approval', ['status' => 'disetujui']) }}" class="filter-btn {{ request('status') == 'disetujui' ? 'active' : '' }}">Disetujui</a>
        <a href="{{ route('peminjaman-ruangan.approval', ['status' => 'ditolak']) }}" class="filter-btn {{ request('status') == 'ditolak' ? 'active' : '' }}">Ditolak</a>
    </div>

    <!-- Cards -->
    @forelse($peminjamanRuangans as $p)
        @php
            $jabatan = strtolower($p->pemohon->jabatan ?? '');
            $role = strtolower($p->pemohon->role ?? '');
            $isDireksi = $role === 'direksi' || str_contains($jabatan, 'direksi') || str_contains($jabatan, 'direktur');
            $namaPemohon = $p->pemohon->nama_lengkap ?? $p->pemohon->name ?? 'Unknown';
            $initial = strtoupper(substr($namaPemohon, 0, 1));
            $isBentrok = isset($p->bentrokDengan) && $p->bentrokDengan->isNotEmpty();
            $needAction = in_array($p->status_persetujuan, ['menunggu', 'dijadwalkan_ulang']);
            $statusClass = 'status-' . str_replace('_', '', $p->status_persetujuan);
        @endphp

        <div class="apv-card">
            <div class="card-accent {{ $isDireksi && $needAction ? 'direksi' : '' }} {{ $isBentrok ? 'bentrok' : '' }}"></div>
            
            <div class="card-header-custom">
                <div class="user-info">
                    <div class="user-avatar {{ $isDireksi ? 'av-direksi' : '' }}">{{ $initial }}</div>
                    <div>
                        <div class="user-name">
                            {{ $namaPemohon }}
                            @if($isDireksi) <span class="badge-direksi">Direksi</span> @endif
                        </div>
                        <div class="user-meta">NIP: {{ $p->user_id }} · Diajukan {{ $p->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                <span class="status-badge {{ $statusClass }}">
                    {{ ucfirst(str_replace('_', ' ', $p->status_persetujuan)) }}
                </span>
            </div>

            <div class="card-body-custom">
                <div class="info-grid">
                    <div class="info-item">
                        <label>Ruangan</label>
                        <div class="val highlight"><i class="bi bi-door-open me-1"></i> {{ $p->ruangan->nama_ruangan ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <label>Tanggal</label>
                        <div class="val"><i class="bi bi-calendar-event me-1"></i> {{ \Carbon\Carbon::parse($p->tanggal_pemakaian)->locale('id')->isoFormat('dddd, D MMMM Y') }}</div>
                    </div>
                    <div class="info-item">
                        <label>Waktu</label>
                        <div class="val"><i class="bi bi-clock me-1"></i> {{ $p->waktu_mulai }} - {{ $p->waktu_selesai }} WIB</div>
                    </div>
                    <div class="info-item">
                        <label>Peserta</label>
                        <div class="val"><i class="bi bi-people me-1"></i> {{ $p->jumlah_peserta ?? '-' }} Orang</div>
                    </div>
                </div>

                <div class="purpose-box">
                    <label>Keperluan</label>
                    <div class="val">{{ $p->keperluan }}</div>
                </div>

                @if($isBentrok)
                    <div class="conflict-alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div>
                            <strong>Terdeteksi Bentrok Jadwal</strong>
                            Ruangan ini sudah dipesan pada waktu yang tumpang tindih oleh:
                            <ul>
                                @foreach($p->bentrokDengan as $bentrok)
                                    <li>
                                        <strong>{{ $bentrok->waktu_mulai }} - {{ $bentrok->waktu_selesai }}</strong> 
                                        a.n. {{ $bentrok->pemohon->nama_lengkap ?? $bentrok->pemohon->name ?? 'NIP '.$bentrok->user_id }} 
                                        <span class="badge bg-secondary" style="font-size: 0.65rem;">{{ ucfirst(str_replace('_', ' ', $bentrok->status_persetujuan)) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>

            @if($needAction && auth()->user()->hasRole(['Sekretariat', 'IT Administrator']))
            <div class="card-actions">
                <a href="{{ route('peminjaman-ruangan.show', $p) }}" class="btn-act btn-outline"><i class="bi bi-eye"></i> Detail</a>
                <button type="button" class="btn-act btn-success" data-bs-toggle="modal" data-bs-target="#approveModal-{{ $p->id }}">
                    <i class="bi bi-check-lg"></i> Setujui
                </button>
                <button type="button" class="btn-act btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $p->id }}">
                    <i class="bi bi-x-lg"></i> Tolak
                </button>
            </div>
            @endif
        </div>

        {{-- Modal Setujui --}}
        @if($needAction && auth()->user()->hasRole(['Sekretariat', 'IT Administrator']))
        <div class="modal fade" id="approveModal-{{ $p->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('peminjaman-ruangan.approve', $p) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
                        <div class="modal-header" style="background: #f0fdf4; border-bottom: 1px solid #dcfce7;">
                            <h5 class="modal-title fw-bold text-success"><i class="bi bi-check-circle me-2"></i>Konfirmasi Persetujuan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="mb-3">Setujui peminjaman ruangan oleh <strong>{{ $namaPemohon }}</strong>?</p>
                            @if($isBentrok)
                                <div class="alert alert-warning small mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-exclamation-triangle-fill"></i> 
                                    <span>Terdapat bentrok jadwal. Pastikan ini disengaja.</span>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Catatan (Opsional)</label>
                                <textarea name="catatan_persetujuan" class="form-control" rows="2" placeholder="Tambahkan catatan..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success px-4">Ya, Setujui</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Tolak --}}
        <div class="modal fade" id="rejectModal-{{ $p->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('peminjaman-ruangan.reject', $p) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
                        <div class="modal-header" style="background: #fef2f2; border-bottom: 1px solid #fee2e2;">
                            <h5 class="modal-title fw-bold text-danger"><i class="bi bi-x-circle me-2"></i>Konfirmasi Penolakan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="mb-3">Tolak peminjaman ruangan oleh <strong>{{ $namaPemohon }}</strong>?</p>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Alasan Penolakan <span class="text-danger">*</span></label>
                                <textarea name="catatan_penolakan" class="form-control" rows="3" required placeholder="Jelaskan alasan penolakan..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger px-4">Ya, Tolak</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif
    @empty
        <div class="text-center py-5 mt-4">
            <div class="mb-3"><i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i></div>
            <h5 class="text-muted fw-semibold">Tidak ada data peminjaman</h5>
            <p class="text-muted small">Belum ada pengajuan peminjaman ruangan pada filter ini.</p>
        </div>
    @endforelse
</div>
@endsection