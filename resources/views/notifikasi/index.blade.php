@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Notifikasi</h2>
            <p class="text-muted mb-0">Pusat informasi dan pemberitahuan terbaru untuk Anda.</p>
        </div>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifikasi.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-check-all me-1"></i> Tandai Semua Sudah Dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if(auth()->user()->notifications->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach(auth()->user()->notifications as $notification)
                        @php
                            // ✅ PERBAIKAN: Gunakan ?? agar aman jika key tidak ada
                            $data = $notification->data ?? [];
                            $title = $data['title'] ?? 'Notifikasi Sistem';
                            $message = $data['message'] ?? 'Anda memiliki pemberitahuan baru.';
                            $url = $data['url'] ?? '#';
                            $icon = $data['icon'] ?? 'bi bi-bell';
                            $color = $data['color'] ?? 'primary';
                            $isRead = !is_null($notification->read_at);
                        @endphp
                        
                        <a href="{{ $url }}" class="list-group-item list-group-item-action p-3 {{ $isRead ? 'bg-light' : 'bg-white' }}" onclick="markAsRead('{{ $notification->id }}')">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="mt-1">
                                        <i class="{{ $icon }} fs-4 text-{{ $color }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold {{ $isRead ? 'text-muted' : 'text-dark' }}">{{ $title }}</h6>
                                        <p class="mb-1 small text-secondary">{!! $message !!}</p>
                                        <small class="text-muted" style="font-size: 0.8rem;">
                                            <i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                                @if(!$isRead)
                                    <span class="badge bg-primary rounded-pill">Baru</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
                
                <div class="p-3 border-top bg-light">
                    {{ auth()->user()->notifications()->paginate(15)->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                    <h6 class="mt-3 text-muted">Belum Ada Notifikasi</h6>
                    <p class="text-muted small">Anda akan melihat pemberitahuan di sini ketika ada aktivitas terbaru.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function markAsRead(notifId) {
    fetch(`/notifikasi/${notifId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).catch(err => console.error('Gagal menandai notifikasi:', err));
}
</script>
@endpush
@endsection