@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0" style="color: #1F3864;">Pusat Notifikasi</h2>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifikasi.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-check-all me-1"></i> Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @forelse($notifications as $notif)
                <a href="{{ $notif->data['url'] ?? '#' }}" class="d-block text-decoration-none text-reset border-bottom p-3 hover-bg-light {{ $notif->read_at ? 'bg-white' : 'bg-light' }}">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <div class="bg-white rounded-circle p-2 shadow-sm">
                                <i class="{{ $notif->data['icon'] ?? 'bi bi-bell' }} fs-4 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="mb-1 fw-bold {{ $notif->read_at ? 'text-muted' : 'text-dark' }}">
                                    {{ $notif->data['title'] }}
                                    @if(!$notif->read_at)
                                        <span class="badge bg-danger rounded-pill ms-2" style="font-size: 0.6rem;">BARU</span>
                                    @endif
                                </h6>
                                <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 text-muted small">{{ $notif->data['message'] }}</p>
                            
                            @if(!$notif->read_at)
                                <form action="{{ route('notifikasi.mark-as-read', $notif->id) }}" method="POST" class="d-inline mt-1">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link text-decoration-none p-0" style="font-size: 0.8rem;">
                                        <i class="bi bi-check-circle me-1"></i>Tandai dibaca
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                    <h6 class="mt-3 text-muted">Belum Ada Notifikasi</h6>
                    <p class="text-muted small">Anda akan mendapat notifikasi di sini ketika ada pembaruan.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-3">
        {{ $notifications->links() }}
    </div>
</div>

<style>
    .hover-bg-light:hover { background-color: #f8f9fa !important; }
</style>
@endsection