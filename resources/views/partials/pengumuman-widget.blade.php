<div class="enterprise-card">
    <div class="card-header-custom">
        <span><i class="bi bi-megaphone me-2"></i>Pengumuman Terbaru</span>
        <a href="{{ route('pengumuman.index') }}" class="link-primary">Lihat Semua</a>
    </div>
    <div class="card-body-custom" style="padding: 16px 24px;">
        @php
            $pengumumans = \App\Models\Pengumuman::with('creator')
                ->where('status', 'publish')
                ->where('tanggal_mulai', '<=', now())
                ->where(function($q) {
                    $q->whereNull('tanggal_selesai')
                      ->orWhere('tanggal_selesai', '>=', now());
                })
                ->latest()
                ->limit(3)
                ->get();
        @endphp
        
        @forelse($pengumumans as $pengumuman)
        <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="fw-bold mb-0">{{ $pengumuman->judul }}</h6>
                <span class="badge-prioritas badge-{{ $pengumuman->prioritas }}" style="font-size: 0.65rem;">
                    {{ ucfirst($pengumuman->prioritas) }}
                </span>
            </div>
            <p class="small text-muted mb-2">{{ Str::limit($pengumuman->isi, 100) }}</p>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="bi bi-calendar3 me-1"></i>{{ $pengumuman->tanggal_mulai->format('d M Y') }}
                </small>
                <a href="{{ route('pengumuman.show', $pengumuman) }}" class="btn btn-sm btn-link p-0">
                    Selengkapnya <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="text-center py-4 text-muted">
            <i class="bi bi-megaphone" style="font-size: 2rem;"></i>
            <p class="mb-0 mt-2 small">Belum ada pengumuman</p>
        </div>
        @endforelse
    </div>
</div>