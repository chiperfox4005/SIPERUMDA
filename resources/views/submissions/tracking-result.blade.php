<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Tracking - SIPERUMDA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
            padding: 40px 20px;
        }
        .header {
            background: linear-gradient(135deg, #1F3864 0%, #3b82f6 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .nip-badge {
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 10px;
            display: inline-block;
            margin-top: 10px;
        }
        .surat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 5px solid #1F3864;
        }
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .status-menunggu { background: #fef3c7; color: #d97706; }
        .status-disetujui { background: #dcfce7; color: #16a34a; }
        .status-ditolak { background: #fee2e2; color: #dc2626; }
        .timeline {
            margin-top: 20px;
            padding-left: 20px;
            border-left: 2px solid #e2e8f0;
        }
        .timeline-item {
            margin-bottom: 15px;
            position: relative;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -26px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #1F3864;
        }
        .timeline-item.completed::before {
            background: #16a34a;
        }
        .timeline-item.pending::before {
            background: #d97706;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #1F3864;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
        }
        .back-btn:hover {
            background: #3b82f6;
            color: white;
        }
        .download-btn {
            background: #16a34a;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .download-btn:hover {
            background: #15803d;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="bi bi-search me-2"></i>HASIL TRACKING SURAT</h1>
            <p>SIPERUMDA - Sistem Informasi Pengelolaan Ruangan</p>
            <div class="nip-badge">
                <i class="bi bi-person-badge me-2"></i>NIP/NPP: <strong>{{ $nip }}</strong>
            </div>
        </div>

        <div class="mb-4">
            <h5 class="text-muted mb-3">
                <i class="bi bi-inbox me-2"></i>
                Total Surat Ditemukan: <strong>{{ $surats->count() }}</strong>
            </h5>
        </div>

        @foreach($surats as $surat)
            <div class="surat-card">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="fw-bold mb-2">{{ $surat->perihal }}</h5>
                        <p class="text-muted mb-2">
                            <i class="bi bi-calendar me-1"></i>
                            {{ \Carbon\Carbon::parse($surat->tanggal_surat)->isoFormat('D MMMM Y') }}
                        </p>
                        <p class="mb-1">
                            <strong>Nomor Surat:</strong> 
                            @if($surat->nomor_surat)
                                <span class="text-success">{{ $surat->nomor_surat }}</span>
                            @else
                                <span class="text-muted">Belum ada (Menunggu Persetujuan)</span>
                            @endif
                        </p>
                        <p class="mb-1">
                            <strong>Inisiator:</strong> {{ $surat->inisiator }}
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span class="status-badge status-{{ $surat->status }}">
                            @if($surat->status == 'menunggu')
                                <i class="bi bi-clock me-1"></i>Menunggu Persetujuan
                            @elseif($surat->status == 'disetujui')
                                <i class="bi bi-check-circle me-1"></i>Disetujui
                            @elseif($surat->status == 'ditolak')
                                <i class="bi bi-x-circle me-1"></i>Ditolak
                            @else
                                {{ ucfirst($surat->status) }}
                            @endif
                        </span>
                        
                        @if($surat->status == 'disetujui' && $surat->file)
                            <br>
                            <a href="{{ route('surat.download', $surat) }}" class="download-btn">
                                <i class="bi bi-download me-1"></i>Download PDF
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Timeline Status -->
                <div class="timeline">
                    <div class="timeline-item completed">
                        <strong>Diajukan</strong><br>
                        <small class="text-muted">{{ $surat->created_at->isoFormat('D MMMM Y, HH:mm') }} WIB</small>
                    </div>
                    
                    @if($surat->status != 'menunggu')
                        <div class="timeline-item {{ $surat->status == 'disetujui' ? 'completed' : 'pending' }}">
                            <strong>Diproses Sekretariat</strong><br>
                            <small class="text-muted">
                                @if($surat->tanggal_disetujui)
                                    {{ \Carbon\Carbon::parse($surat->tanggal_disetujui)->isoFormat('D MMMM Y, HH:mm') }} WIB
                                @else
                                    Menunggu...
                                @endif
                            </small>
                        </div>
                        
                        @if($surat->status == 'disetujui')
                            <div class="timeline-item completed">
                                <strong>Selesai - Surat Tersedia</strong><br>
                                <small class="text-muted">
                                    PDF dengan TTD digital telah tersedia untuk diunduh
                                </small>
                            </div>
                        @elseif($surat->status == 'ditolak')
                            <div class="timeline-item pending">
                                <strong class="text-danger">Ditolak</strong><br>
                                <small class="text-muted">
                                    Silakan hubungi Sekretariat untuk informasi lebih lanjut
                                </small>
                            </div>
                        @endif
                    @else
                        <div class="timeline-item pending">
                            <strong>Menunggu Persetujuan Sekretariat</strong><br>
                            <small class="text-muted">Surat sedang dalam proses verifikasi</small>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="text-center">
            <a href="{{ route('surat.track') }}" class="back-btn">
                <i class="bi bi-arrow-left me-2"></i>Lacak Surat Lain
            </a>
        </div>
    </div>
</body>
</html>