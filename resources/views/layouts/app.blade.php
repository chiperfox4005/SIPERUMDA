<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIPERUMDA - @yield('title', 'Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    
    <style>
        :root { --primary-color: #1F3864; --sidebar-width: 260px; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .sidebar { width: var(--sidebar-width); background-color: var(--primary-color); min-height: 100vh; position: fixed; color: white; z-index: 1000; overflow-y: auto; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; border-radius: 8px; margin: 4px 10px; font-size: 0.95rem; text-decoration: none; display: flex; align-items: center; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.15); color: white; }
        .sidebar .nav-link i { margin-right: 10px; width: 20px; text-align: center; }
        .main-content { margin-left: var(--sidebar-width); padding: 20px 30px; }
        .topbar { background: white; padding: 15px 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .section-label { font-size: 0.7rem; letter-spacing: 1.5px; color: rgba(255,255,255,0.4); padding: 15px 20px 8px 20px; font-weight: 700; text-transform: uppercase; }
        .btn-fab, .floating-action-button, .fab-button, .fixed-bottom-btn, .carousel-control-prev, .carousel-control-next { display: none !important; }

        @media (max-width: 991.98px) {
            :root { --sidebar-width: 0px; }
            .sidebar { transform: translateX(-260px); transition: transform 0.3s ease; }
            .sidebar.show { transform: translateX(0); width: 260px; }
            .main-content { margin-left: 0 !important; padding: 15px; }
        }
    </style>
    @stack('styles')
</head>
<body>

    @php
        $user = auth()->user();
        $unreadNotifs = $user ? $user->unreadNotifications : collect();
        
        $countPeminjaman = $unreadNotifs->where('data.type', 'peminjaman_ruangan')->count();
        $countSurat = $unreadNotifs->where('data.type', 'surat')->count();
        $countAgenda = $unreadNotifs->where('data.type', 'agenda')->count();
        $countPengumuman = $unreadNotifs->where('data.type', 'pengumuman')->count();
        $totalUnread = $unreadNotifs->count();

        // ✅ DETEKSI ROLE
        $userRoles = $user ? $user->getRoleNames() : collect();
        
        $isAdmin = false;
        foreach ($userRoles as $role) {
            $roleLower = strtolower(trim($role));
            if (in_array($roleLower, ['administrator', 'it administrator', 'admin', 'super admin', 'pti'])) {
                $isAdmin = true;
                break;
            }
        }

        // Fallback untuk PTI berdasarkan Nama Bagian
        if (!$isAdmin && $user && isset($user->bagian) && isset($user->subBagian)) {
            $namaBagian = strtolower(trim($user->bagian->nama_bagian));
            $namaSubBagian = strtolower(trim($user->subBagian->nama_sub_bagian));
            if (str_contains($namaBagian, 'litbang') && str_contains($namaSubBagian, 'pengembangan teknologi informatika')) {
                $isAdmin = true;
            }
        }

        $isSekretariat = $userRoles->contains('Sekretariat');
        $isKepegawaian = $userRoles->contains('Kepegawaian');
        $isDireksi = $user ? $user->hasRole('Direksi') : false;
    @endphp

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column p-3" id="appSidebar">
        <div class="mb-4 px-2 mt-2">
            <h4 class="fw-bold mb-0">SIPERUMDA</h4>
            <small class="text-white-50">Sistem Informasi Pengelolaan Ruangan, Pengumuman, dan Agenda</small>
        </div>
        
        <ul class="nav flex-column">
            <!-- ============================================ -->
            <!-- MENU KHUSUS DIREKSI -->
            <!-- ============================================ -->
            @if($isDireksi)
                <li class="nav-item mb-2">
                    <a class="nav-link {{ request()->routeIs('direksi.*') ? 'active' : '' }}" href="{{ route('direksi.dashboard') }}" style="background: rgba(255,255,255,0.1); border-left: 4px solid #fbbf24;">
                        <i class="bi bi-graph-up me-2 text-warning"></i> Dashboard Eksekutif
                    </a>
                </li>
            @endif

            <!-- ============================================ -->
            <!-- MENU UMUM -->
            <!-- ============================================ -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-grid"></i> Beranda
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center {{ request()->routeIs('agenda.*') ? 'active' : '' }}" href="{{ route('agenda.index') }}">
                    <i class="bi bi-calendar-event me-2"></i> Agenda
                    @if($countAgenda > 0)<span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $countAgenda }}</span>@endif
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link d-flex align-items-center {{ request()->routeIs('pengumuman.*') ? 'active' : '' }}" href="{{ route('pengumuman.index') }}">
                    <i class="bi bi-megaphone me-2"></i> Pengumuman
                    @if($countPengumuman > 0)<span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $countPengumuman }}</span>@endif
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link d-flex align-items-center {{ request()->routeIs('peminjaman-ruangan.*') && !request()->routeIs('peminjaman-ruangan.approval') ? 'active' : '' }}" href="{{ route('peminjaman-ruangan.index') }}">
                    <i class="bi bi-door-open me-2"></i> Peminjaman Ruangan
                    @if($countPeminjaman > 0)<span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $countPeminjaman }}</span>@endif
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link d-flex align-items-center {{ request()->routeIs('surat.*') ? 'active' : '' }}" href="{{ route('surat.index') }}">
                    <i class="bi bi-file-earmark-text me-2"></i> Modul Surat
                    @if($countSurat > 0)<span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $countSurat }}</span>@endif
                </a>
            </li>

            <!-- ✅ MODUL SEKRETARIAT (HANYA UNTUK ROLE SEKRETARIAT, BUKAN ADMIN) -->
            @if($isSekretariat)
                <li class="nav-item mt-3">
                    <div class="section-label">Modul Sekretariat</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('peminjaman-ruangan.approval') ? 'active' : '' }}" href="{{ route('peminjaman-ruangan.approval') }}">
                        <i class="bi bi-clipboard-check me-2"></i> Persetujuan Peminjaman
                        @if($countPeminjaman > 0)<span class="badge bg-danger rounded-pill ms-auto">{{ $countPeminjaman }}</span>@endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('ruangan.*') ? 'active' : '' }}" href="{{ route('ruangan.index') }}">
                        <i class="bi bi-houses me-2"></i> Data Master Ruangan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('signatories.*') ? 'active' : '' }}" href="{{ route('signatories.index') }}">
                        <i class="bi bi-person-badge me-2"></i> Data Pejabat
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('surat.approval') ? 'active' : '' }}" href="{{ route('surat.approval') }}">
                        <i class="bi bi-file-earmark-check me-2"></i> Persetujuan Surat
                        @if($countSurat > 0)<span class="badge bg-danger rounded-pill ms-auto">{{ $countSurat }}</span>@endif
                    </a>
                </li>
            @endif

            <!-- MODUL KEPEGAWAIAN -->
            @if($isKepegawaian && !$isAdmin)
                <li class="nav-item mt-3">
                    <div class="section-label">Modul Kepegawaian</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('kepegawaian.pegawai.*') ? 'active' : '' }}" href="{{ route('kepegawaian.pegawai.index') }}">
                        <i class="bi bi-people me-2"></i> Data Pegawai
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('kepegawaian.bagian.*') ? 'active' : '' }}" href="{{ route('kepegawaian.bagian.index') }}">
                        <i class="bi bi-building me-2"></i> Data Bagian
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('kepegawaian.sub-bagian.*') ? 'active' : '' }}" href="{{ route('kepegawaian.sub-bagian.index') }}">
                        <i class="bi bi-diagram-3 me-2"></i> Data Sub Bagian
                    </a>
                </li>
            @endif

            <!-- ✅ MODUL ADMIN / PTI -->
            @if($isAdmin)
                <li class="nav-item mt-3">
                    <div class="section-label">Administrasi Sistem</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                        <i class="bi bi-people me-2"></i> Manajemen Pengguna
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}" href="{{ route('admin.audit-logs') }}">
                        <i class="bi bi-journal-text me-2"></i> Audit Log
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.backup.page') ? 'active' : '' }}" href="{{ route('admin.backup.page') }}">
                        <i class="bi bi-hdd me-2"></i> Backup & Restore
                    </a>
                </li>
            @endif

            <!-- MENU BAWAH -->
            <li class="nav-item mt-4 border-top border-white-50 pt-3">
                <a class="nav-link d-flex align-items-center {{ request()->routeIs('notifikasi*') ? 'active' : '' }}" href="{{ route('notifikasi.index') }}">
                    <i class="bi bi-bell me-2"></i> Notifikasi
                    @if($totalUnread > 0)<span class="badge bg-danger rounded-pill ms-auto">{{ $totalUnread }}</span>@endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                    <i class="bi bi-person me-2"></i> Profil
                </a>
            </li>
        </ul>
        
        <div class="mt-2">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent text-danger">
                    <i class="bi bi-box-arrow-right me-2"></i> Keluar
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <div class="topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-light d-lg-none border" type="button" onclick="document.getElementById('appSidebar').classList.toggle('show')">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="input-group" style="width: 250px; max-width: 100%;">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-0 bg-light" placeholder="Cari...">
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                
                <!-- Tombol Notifikasi (Diperbarui dengan AJAX markAsRead) -->
                <li class="nav-item dropdown list-unstyled">
                    <a class="nav-link position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell-fill fs-5 text-secondary"></i>
                        @php
                            $unreadCount = auth()->user()->unreadNotifications->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                            </span>
                        @endif
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notifDropdown" style="width: 320px; max-height: 400px; overflow-y: auto;">
                        <li><h6 class="dropdown-header">Notifikasi Terbaru</h6></li>
                        
                        @forelse(auth()->user()->notifications->take(5) as $notif)
                            @php
                                $data = $notif->data;
                                $bgClass = $data['color'] ?? 'warning';
                            @endphp
                            <li>
                                <a class="dropdown-item {{ $notif->read_at ? 'text-muted' : 'fw-bold' }}" href="{{ $data['url'] ?? '#' }}" onclick="markAsRead('{{ $notif->id }}')">
                                    <div class="d-flex align-items-start">
                                        <i class="bi {{ $data['icon'] ?? 'bi-bell' }} text-{{ $bgClass }} me-2 mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">{{ $notif->created_at->diffForHumans() }}</small>
                                            <div class="small">{!! $data['message'] ?? $data['title'] ?? '' !!}</div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                        @empty
                            <li><span class="dropdown-item text-center text-muted py-3">Tidak ada notifikasi</span></li>
                        @endforelse
                        
                        <li>
                            <a class="dropdown-item text-center small text-primary" href="{{ route('notifikasi.index') }}">Lihat Semua Notifikasi</a>
                        </li>
                    </ul>
                </li>

                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->nama_lengkap ?? auth()->user()->name) }}&background=1F3864&color=fff" class="rounded-circle me-2" width="38" height="38" alt="Profile">
                        <div>
                            <div class="fw-bold small">{{ auth()->user()->nama_lengkap ?? auth()->user()->name }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">
                                @if($isAdmin) Administrator (PTI)
                                @elseif($isSekretariat) Sekretariat
                                @elseif($isKepegawaian) Kepegawaian
                                @elseif($isDireksi) Direksi
                                @else {{ $userRoles->first() ?? 'Pegawai' }}
                                @endif
                            </div>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profil Saya</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    
    <!-- Script AJAX untuk menandai notifikasi sudah dibaca saat diklik -->
    <script>
    function markAsRead(notifId) {
        fetch(`/notifikasi/${notifId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
    }
    </script>

    @stack('scripts')
</body>
</html>