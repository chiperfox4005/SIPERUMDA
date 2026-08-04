@extends('layouts.app')

@section('title', 'Detail Agenda')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Detail Agenda / Kegiatan</h2>
                <a href="{{ route('agenda.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
                </a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Informasi Utama -->
                        <div class="col-md-8">
                            <h4 class="fw-bold text-primary mb-3">{{ $agenda->judul }}</h4>
                            <p class="text-muted mb-4">{{ $agenda->acara }}</p>

                            <table class="table table-borderless">
                                <tr>
                                    <th>Hari & Tanggal</th>
                                    <td>{{ \Carbon\Carbon::parse($agenda->tanggal_mulai)->locale('id')->isoFormat('dddd, D MMMM Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Waktu</th>
                                    <td>{{ $agenda->jam_mulai }} - {{ $agenda->jam_selesai ?? 'Selesai' }} WIB</td>
                                </tr>
                                <tr>
                                    <th>Lokasi / Tempat</th>
                                    <td>
                                        {{ $agenda->tempat }}
                                        @if($agenda->ruangan)
                                            <br><small class="text-primary">({{ $agenda->ruangan->nama_ruangan }})</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Penanggung Jawab</th>
                                    <td>{{ $agenda->pimpinan_rapat ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Inisiator</th>
                                    <td>{{ $agenda->inisiator }}</td>
                                </tr>
                                <tr>
                                    <th>PIC / Notulen</th>
                                    <td>{{ $agenda->notulen ?? '-' }}</td>
                                </tr>
                            </table>

                            {{-- Bagian Lampiran --}}
                            <div class="mt-4">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-paperclip"></i> Lampiran
                                </h6>

                                @if($agenda->lampiran)
                                    @php
                                        $filePath = asset('storage/' . $agenda->lampiran);
                                        $ext = strtolower(pathinfo($agenda->lampiran, PATHINFO_EXTENSION));
                                    @endphp

                                    {{-- 1. JIKA FILE ADALAH GAMBAR (JPG, PNG, JPEG, WEBP) --}}
                                    @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                        <div class="text-center p-3 border rounded bg-light">
                                            <img src="{{ $filePath }}" class="img-fluid rounded shadow-sm" alt="Lampiran Agenda" style="max-height: 400px; width: auto; max-width: 100%;">
                                            <div class="mt-3">
                                                <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-primary me-2">
                                                    <i class="bi bi-zoom-in"></i> Lihat Ukuran Penuh
                                                </a>
                                                <a href="{{ $filePath }}" download class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-download"></i> Unduh
                                                </a>
                                            </div>
                                        </div>

                                    {{-- 2. JIKA FILE ADALAH PDF --}}
                                    @elseif($ext === 'pdf')
                                        <div class="border rounded bg-white shadow-sm overflow-hidden">
                                            <div class="ratio ratio-16x9">
                                                <iframe src="{{ $filePath }}" title="Preview PDF Lampiran" style="border: none;"></iframe>
                                            </div>
                                            <div class="p-2 bg-light border-top d-flex justify-content-end">
                                                <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-danger me-2">
                                                    <i class="bi bi-file-earmark-pdf"></i> Buka di Tab Baru
                                                </a>
                                                <a href="{{ $filePath }}" download class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-download"></i> Unduh PDF
                                                </a>
                                            </div>
                                        </div>

                                    {{-- 3. JIKA FILE ADALAH DOKUMEN OFFICE (WORD, EXCEL, DLL) --}}
                                    @else
                                        <div class="d-flex align-items-center p-3 bg-light border rounded shadow-sm">
                                            <div class="me-3">
                                                @if(in_array($ext, ['doc', 'docx']))
                                                    <i class="bi bi-file-earmark-word text-primary" style="font-size: 2.5rem;"></i>
                                                @elseif(in_array($ext, ['xls', 'xlsx']))
                                                    <i class="bi bi-file-earmark-excel text-success" style="font-size: 2.5rem;"></i>
                                                @else
                                                    <i class="bi bi-file-earmark text-secondary" style="font-size: 2.5rem;"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <p class="mb-1 fw-bold text-truncate" title="{{ basename($agenda->lampiran) }}">
                                                    {{ basename($agenda->lampiran) }}
                                                </p>
                                                <small class="text-muted">Format: {{ strtoupper($ext) }}</small>
                                            </div>
                                            <div class="ms-2">
                                                <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-primary me-1">
                                                    <i class="bi bi-eye"></i> Lihat
                                                </a>
                                                <a href="{{ $filePath }}" download class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-download"></i> Unduh
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="alert alert-secondary d-flex align-items-center" role="alert">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <span>Tidak ada lampiran untuk agenda ini.</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Sidebar Informasi Tambahan -->
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 mb-4">
                                <h6 class="fw-bold mb-3"><i class="bi bi-people me-2"></i>Daftar Peserta / Undangan</h6>
                                @if(count($pesertaList) > 0)
                                    <ul class="list-unstyled mb-0">
                                        @foreach($pesertaList as $peserta)
                                            <li class="mb-2 d-flex align-items-start">
                                                <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                                <span>
                                                    @if(is_array($peserta))
                                                        {{ $peserta['bagian_sub_bagian'] ?? 'Tidak diketahui' }}
                                                    @else
                                                        {{ $peserta }}
                                                    @endif
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted small mb-0">Tidak ada daftar peserta spesifik.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Tambahan (Jika ada) -->
                    @if($agenda->catatan && count($catatanList) > 0)
                        <div class="mt-4 pt-4 border-top">
                            <h6 class="fw-bold mb-3"><i class="bi bi-sticky me-2"></i>Catatan</h6>
                            <ul class="list-unstyled mb-0">
                                @foreach($catatanList as $catatan)
                                    <li class="mb-2">
                                        @if(is_array($catatan))
                                            • {{ $catatan['text'] ?? '' }}
                                        @else
                                            • {{ $catatan }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Metadata -->
                    <div class="mt-4 pt-4 border-top d-flex justify-content-between text-muted small">
                        <div>
                            <strong>Diajukan oleh:</strong> {{ $agenda->creator->nama_lengkap ?? 'User' }} (NIP: {{ $agenda->created_by }})
                        </div>
                        <div>
                            <strong>Tanggal Pengajuan:</strong> {{ $agenda->created_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection