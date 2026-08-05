@extends('layouts.app')

@section('title', 'Buat ' . $template->name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Buat {{ $template->name }}</h2>
            <p class="text-muted mb-0">Lengkapi data di bawah ini. Preview akan muncul secara otomatis.</p>
        </div>
        <a href="{{ route('surat.pilih-template') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Ganti Jenis Surat
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4">
            <h6 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Gagal Mengirim Surat:</h6>
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- FORM INPUT (Kiri) -->
        <div class="col-lg-7">
            <form action="{{ route('surat.store') }}" method="POST" enctype="multipart/form-data" class="card border-0 shadow-sm">
                @csrf
                <input type="hidden" name="template_id" value="{{ $template->id }}">
                <div class="card-body p-4">
                    <div class="row g-3">
                        @foreach($template->form_schema['fields'] ?? [] as $field)
                            {{-- ✅ PERBAIKAN: Field "Kepada/Penerima" di-render sebagai HIDDEN dengan nilai default --}}
                            @if(stripos($field['label'], 'kepada') !== false || stripos($field['label'], 'penerima') !== false)
                                <input type="hidden" name="{{ $field['name'] }}" value="{{ old($field['name'], 'Peserta Rapat') }}">
                                @continue
                            @endif

                            <div class="{{ in_array($field['type'], ['textarea', 'participant_selector']) ? 'col-12' : 'col-md-6' }}">
                                <label class="form-label fw-semibold">
                                    {{ $field['label'] }} 
                                    @if(!empty($field['required'])) <span class="text-danger">*</span> @endif
                                </label>

                                @php
                                    $requiredAttr = !empty($field['required']) ? 'required' : '';
                                    $oldValue = old($field['name']);
                                    $previewId = 'preview_' . str_replace(' ', '_', $field['name']);
                                @endphp

                                @if(in_array($field['type'], ['text', 'number', 'email']))
                                    <input type="{{ $field['type'] }}" name="{{ $field['name'] }}" id="input_{{ $field['name'] }}"
                                           class="form-control preview-trigger" data-target="{{ $previewId }}"
                                           value="{{ $oldValue }}" {{ $requiredAttr }}>
                                
                                @elseif($field['type'] === 'textarea')
                                    <textarea name="{{ $field['name'] }}" id="input_{{ $field['name'] }}" rows="4" 
                                              class="form-control preview-trigger" data-target="{{ $previewId }}"
                                              {{ $requiredAttr }}>{{ $oldValue }}</textarea>
                                
                                @elseif($field['type'] === 'date')
                                    {{-- ✅ PERBAIKAN: Tambah class date-trigger untuk update preview tanggal --}}
                                    <input type="date" name="{{ $field['name'] }}" id="input_{{ $field['name'] }}"
                                           class="form-control preview-trigger date-trigger" data-target="{{ $previewId }}"
                                           value="{{ $oldValue }}" {{ $requiredAttr }}>
                                
                                @elseif($field['type'] === 'time')
                                    <input type="time" name="{{ $field['name'] }}" id="input_{{ $field['name'] }}"
                                           class="form-control preview-trigger" data-target="{{ $previewId }}"
                                           value="{{ $oldValue }}" {{ $requiredAttr }}>
                                
                                @elseif($field['type'] === 'select' && $field['name'] === 'tempat')
                                    <select name="{{ $field['name'] }}" id="input_ruangan" class="form-select preview-trigger" data-target="preview_tempat" {{ $requiredAttr }}>
                                        <option value="">-- Pilih Ruangan --</option>
                                        @foreach($options['ruangans'] ?? [] as $opt)
                                            <option value="{{ $opt->nama_ruangan }}" data-id="{{ $opt->id }}" {{ $oldValue == $opt->nama_ruangan ? 'selected' : '' }}>
                                                {{ $opt->nama_ruangan }}
                                            </option>
                                        @endforeach
                                    </select>
                                    
                                    <div id="roomInfoBox" class="mt-2 p-3 border rounded bg-light d-none">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bi bi-building text-primary mt-1"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-primary" id="selectedRoomName">-</div>
                                                <small class="text-muted d-block mb-2">Informasi Peminjaman Ruangan</small>
                                                <div id="ruanganStatus"></div>
                                                <div id="bookingInfo" class="mt-2 small text-success d-none">
                                                    <i class="bi bi-check-circle-fill"></i> Surat ini akan otomatis membuat peminjaman ruangan.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                
                                @elseif($field['type'] === 'select')
                                    <select name="{{ $field['name'] }}" id="input_{{ $field['name'] }}" class="form-select preview-trigger" data-target="{{ $previewId }}" {{ $requiredAttr }}>
                                        <option value="">-- Pilih {{ $field['label'] }} --</option>
                                        @if(isset($options[$field['source']]))
                                            @foreach($options[$field['source']] as $opt)
                                                @php
                                                    $optValue = $opt->nip ?? $opt->id;
                                                    $optLabel = $opt->nama_ruangan ?? $opt->nama_lengkap ?? $opt->name ?? 'Opsi';
                                                @endphp
                                                <option value="{{ $optLabel }}" {{ $oldValue == $optLabel ? 'selected' : '' }}>
                                                    {{ $optLabel }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>

                                @elseif($field['type'] === 'participant_selector')
                                    <div class="accordion mb-3" id="accordionPeserta">
                                        @foreach($options['bagians'] ?? [] as $bagian)
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBagian{{ $bagian->id }}">
                                                    {{ $bagian->nama_bagian }}
                                                </button>
                                            </h2>
                                            <div id="collapseBagian{{ $bagian->id }}" class="accordion-collapse collapse" data-bs-parent="#accordionPeserta">
                                                <div class="accordion-body p-3">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input peserta-checkbox" type="checkbox" value="Kabag. {{ $bagian->nama_bagian }}" id="kabag_{{ $bagian->id }}">
                                                        <label class="form-check-label fw-bold text-primary" for="kabag_{{ $bagian->id }}">Kabag. {{ $bagian->nama_bagian }}</label>
                                                    </div>
                                                    @foreach($bagian->subBagians as $sub)
                                                    <div class="form-check mb-2 ms-3">
                                                        <input class="form-check-input peserta-checkbox" type="checkbox" value="Kasie. {{ $sub->nama_sub_bagian }}" id="sub_{{ $sub->id }}">
                                                        <label class="form-check-label" for="sub_{{ $sub->id }}">Kasie. {{ $sub->nama_sub_bagian }}</label>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="p-3 border rounded bg-light">
                                        <label class="form-label fw-semibold mb-2 small"><i class="bi bi-person-plus me-1"></i> Tambah Peserta Manual</label>
                                        <div id="manualPesertaContainer"></div>
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="tambahPesertaManual()">
                                            <i class="bi bi-plus-circle"></i> Tambah Nama
                                        </button>
                                    </div>
                                    <div class="mt-2 p-2 bg-white border rounded small text-muted" style="white-space: pre-line; min-height: 40px; max-height: 100px; overflow-y: auto;" id="pesertaPreview">Belum ada peserta...</div>
                                    <textarea name="{{ $field['name'] }}" id="finalPesertaInput" class="d-none preview-trigger" data-target="preview_peserta_list" {{ $requiredAttr }}>{{ $oldValue }}</textarea>
                                @endif
                            </div>
                        @endforeach

                        {{-- FIELD MANUAL: INISIATOR, NOTULEN, CATATAN --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Inisiator <span class="text-danger">*</span></label>
                            <input type="text" name="inisiator" id="input_inisiator"
                                   class="form-control preview-trigger" data-target="preview_inisiator"
                                   value="{{ old('inisiator') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Notulen <span class="text-danger">*</span></label>
                            <input type="text" name="notulen" id="input_notulen"
                                   class="form-control preview-trigger" data-target="preview_notulen"
                                   value="{{ old('notulen') }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan</label>
                            <textarea name="catatan" id="input_catatan" rows="3"
                                      class="form-control preview-trigger" data-target="preview_catatan">{{ old('catatan') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                        <a href="{{ route('surat.pilih-template') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-send me-2"></i>Ajukan Surat</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- LIVE PREVIEW (Kanan) -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-eye"></i> Live Preview Surat
                </div>
                <div class="card-body p-0">
                    <div class="p-3" style="font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.4; min-height: 600px; background: #fff; color: #000;">
                        <div style="text-align: left; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 2px solid #000;">
                            <div style="font-size: 13pt; font-weight: bold; text-transform: uppercase; line-height: 1.2;">PERUSAHAAN UMUM DAERAH AIR MINUM</div>
                            <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase; line-height: 1.2;">TIRTA MOEDAL KOTA SEMARANG</div>
                        </div>

                        <div style="text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; margin: 10px 0 15px 0;">UNDANGAN RAPAT</div>
                        <div style="text-align: center; font-size: 11pt; margin-bottom: 15px;">Nomor : [Akan diisi oleh Sekretariat]</div>

                        <div class="preview-section-card mb-3 p-3 border rounded" style="background-color: #f8f9fa;">
                            <div class="row mb-2">
                                <div class="col-4 fw-bold">Hari / Tanggal</div>
                                <div class="col-1 text-center">:</div>
                                <div class="col-7" id="preview_tanggal">[Tanggal]</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 fw-bold">Jam</div>
                                <div class="col-1 text-center">:</div>
                                <div class="col-7"><span id="preview_jam_mulai">[Jam]</span> - <span id="preview_jam_selesai">[Jam]</span> WIB</div>
                            </div>
                            <div class="row">
                                <div class="col-4 fw-bold">Tempat</div>
                                <div class="col-1 text-center">:</div>
                                <div class="col-7" id="preview_tempat">[Tempat]</div>
                            </div>
                        </div>

                        <div class="preview-section-card mb-3 p-3 border rounded" style="background-color: #f8f9fa;">
                            <div class="row mb-2">
                                <div class="col-4 fw-bold">Acara</div>
                                <div class="col-1 text-center">:</div>
                                <div class="col-7" id="preview_acara">[Acara]</div>
                            </div>
                            <div class="row">
                                <div class="col-4 fw-bold">Pimpinan Rapat</div>
                                <div class="col-1 text-center">:</div>
                                <div class="col-7" id="preview_pimpinan">[Pimpinan]</div>
                            </div>
                        </div>

                        <div class="preview-section-card mb-3 p-3 border rounded" style="background-color: #f8f9fa;">
                            <div class="row">
                                <div class="col-4 fw-bold" style="vertical-align: top;">Dimohon hadir</div>
                                <div class="col-1 text-center" style="vertical-align: top;">:</div>
                                <div class="col-7" style="vertical-align: top;">
                                    <ol style="margin: 0; padding-left: 20px; counter-reset: item;" id="preview_peserta_list">
                                        <li style="counter-increment: item; margin-bottom: 2px;">[Peserta]</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class="preview-section-card mb-3 p-3 border rounded" style="background-color: #f8f9fa;">
                            <div class="row mb-2">
                                <div class="col-4 fw-bold">Inisiator</div>
                                <div class="col-1 text-center">:</div>
                                <div class="col-7" id="preview_inisiator">[Inisiator]</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 fw-bold">Notulen</div>
                                <div class="col-1 text-center">:</div>
                                <div class="col-7" id="preview_notulen">[Notulen]</div>
                            </div>
                            <div class="row">
                                <div class="col-4 fw-bold" style="vertical-align: top;">Catatan</div>
                                <div class="col-1 text-center" style="vertical-align: top;">:</div>
                                <div class="col-7" style="vertical-align: top;" id="preview_catatan">[Catatan]</div>
                            </div>
                        </div>

                        <div style="margin: 15px 0; font-style: italic;">
                            Demikian atas perhatian dan kehadirannya diucapkan terima kasih.
                        </div>

                        <div style="margin-top: 20px; float: right; width: 250px; text-align: center;">
                            {{-- ✅ PERBAIKAN: Tampilkan tanggal lengkap dari input user --}}
                            <div style="margin-bottom: 50px;">Semarang, <span id="preview_tanggal_bulan">[Tanggal Surat]</span></div>
                            <div style="line-height: 1.8; margin-bottom: 60px; font-size: 11pt;">
                                An. Direksi Perusahaan Umum Daerah Air Minum<br>
                                Tirta Moedal Kota Semarang<br>
                                Direktur Umum<br>
                                u.b<br>
                                <strong>Kepala Bagian Sekretariat</strong>
                            </div>
                            <div style="height: 60px;"></div>
                            <div style="font-weight: bold; text-decoration: underline; font-size: 11pt;" id="preview_nama_pejabat">[NAMA PEJABAT]</div>
                            <div style="font-size: 11pt;">Staf Madya<br>NPP. <span id="preview_nip">[NIP]</span></div>
                        </div>

                        <div style="clear: both; margin-top: 40px; font-size: 11pt;">
                            <div style="margin-bottom: 5px; text-decoration: underline; font-weight: bold;">Tembusan:</div>
                            <ol style="margin: 0; padding-left: 20px;">
                                <li style="margin: 2px 0;">Direktur Utama (Sebagai Laporan)</li>
                                <li style="margin: 2px 0;">Pertinggal.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // 1. HAPUS FIELD "KEPADA" JIKA ADA (sudah di-handle di backend dengan hidden input)

    // 2. DATA HARI DAN BULAN INDONESIA
    const hariIndonesia = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const bulanIndonesia = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    // 3. FUNGSI FORMAT TANGGAL
    function formatTanggalIndonesia(dateString) {
        if (!dateString) return '[Tanggal]';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '[Tanggal]';
        
        const hari = hariIndonesia[date.getDay()];
        const tanggal = date.getDate();
        const bulan = bulanIndonesia[date.getMonth()];
        const tahun = date.getFullYear();
        
        return `${hari}, ${tanggal} ${bulan} ${tahun}`;
    }

    function formatTanggalSingkat(dateString) {
        if (!dateString) return '[Tanggal Surat]';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '[Tanggal Surat]';
        
        const tanggal = date.getDate();
        const bulan = bulanIndonesia[date.getMonth()];
        const tahun = date.getFullYear();
        
        return `${tanggal} ${bulan} ${tahun}`;
    }

    // 4. LIVE PREVIEW LOGIC
    function updatePreview(input) {
        const targetId = input.dataset.target;
        const targetEl = document.getElementById(targetId);
        if (targetEl) {
            if (targetId === 'preview_peserta_list') {
                const lines = input.value.split('\n').filter(line => line.trim() !== '');
                if (lines.length > 0) {
                    targetEl.innerHTML = lines.map(line => `<li style="counter-increment: item; margin-bottom: 2px;">${line}</li>`).join('');
                } else {
                    targetEl.innerHTML = '<li style="counter-increment: item; margin-bottom: 2px;">[Peserta]</li>';
                }
            } else {
                let val = input.value || `[${input.name.replace(/_/g, ' ')}]`;
                val = val.replace(/Yth\.?\s*/gi, '').trim();
                targetEl.innerText = val;
            }
        }
    }

    // 5. UPDATE PREVIEW TANGGAL (KHUSUS FIELD DATE)
    function updatePreviewTanggal(input) {
        const tanggalLengkap = formatTanggalIndonesia(input.value);
        const tanggalSingkat = formatTanggalSingkat(input.value);
        
        const previewTanggal = document.getElementById('preview_tanggal');
        const previewTanggalBulan = document.getElementById('preview_tanggal_bulan');
        
        if (previewTanggal) previewTanggal.innerText = tanggalLengkap;
        if (previewTanggalBulan) previewTanggalBulan.innerText = tanggalSingkat;
    }

    // Pasang event listener
    document.querySelectorAll('.preview-trigger').forEach(input => {
        input.addEventListener('input', function() { updatePreview(this); });
        input.addEventListener('change', function() { 
            if (this.type === 'date') {
                updatePreviewTanggal(this);
            } else {
                updatePreview(this); 
            }
        });
    });

    // 6. PESERTA SELECTOR LOGIC
    function updatePesertaList() {
        let pesertaList = [];
        document.querySelectorAll('.peserta-checkbox:checked').forEach(cb => pesertaList.push(cb.value));
        document.querySelectorAll('.manual-peserta-row').forEach(row => {
            let bagian = row.querySelector('.manual-peserta-bagian').value;
            let nama = row.querySelector('.manual-peserta-nama').value.trim();
            if (bagian && nama) pesertaList.push(`${nama} (${bagian})`);
        });
        
        let finalString = pesertaList.join('\n');
        const finalInput = document.getElementById('finalPesertaInput');
        if (finalInput) {
            finalInput.value = finalString;
            updatePreview(finalInput);
        }
        
        document.getElementById('pesertaPreview').innerText = finalString || 'Belum ada peserta...';
    }

    function tambahPesertaManual() {
        const container = document.getElementById('manualPesertaContainer');
        const div = document.createElement('div');
        div.className = 'input-group mb-2 manual-peserta-row';
        let optionsHtml = '<option value="">Pilih Bagian...</option>';
        @foreach($options['bagians'] ?? [] as $bagian)
            optionsHtml += '<option value="{{ $bagian->nama_bagian }}">{{ $bagian->nama_bagian }}</option>';
        @endforeach
        div.innerHTML = `
            <select class="form-select form-select-sm manual-peserta-bagian" style="max-width: 40%;" required onchange="updatePesertaList()">${optionsHtml}</select>
            <input type="text" class="form-control form-control-sm manual-peserta-nama" placeholder="Nama Staf" required onkeyup="updatePesertaList()">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove(); updatePesertaList();"><i class="bi bi-trash"></i></button>
        `;
        container.appendChild(div);
    }

    document.querySelectorAll('.peserta-checkbox').forEach(cb => cb.addEventListener('change', updatePesertaList));

    // 7. CEK KETERSEDIAAN RUANGAN
    function cekKetersediaanRuangan() {
        const ruanganSelect = document.getElementById('input_ruangan');
        const tanggal = document.querySelector('input[name="tanggal"]')?.value;
        const jamMulai = document.querySelector('input[name="jam_mulai"]')?.value;
        const jamSelesai = document.querySelector('input[name="jam_selesai"]')?.value;
        const roomInfoBox = document.getElementById('roomInfoBox');
        const selectedRoomName = document.getElementById('selectedRoomName');
        const statusDiv = document.getElementById('ruanganStatus');
        const bookingInfo = document.getElementById('bookingInfo');

        if (!ruanganSelect || !tanggal || !jamMulai || !jamSelesai) {
            if (roomInfoBox) roomInfoBox.classList.add('d-none');
            return;
        }

        const selectedOption = ruanganSelect.options[ruanganSelect.selectedIndex];
        const ruanganId = selectedOption?.dataset?.id;

        if (!ruanganId) {
            if (roomInfoBox) roomInfoBox.classList.add('d-none');
            return;
        }

        if (roomInfoBox) {
            roomInfoBox.classList.remove('d-none');
            selectedRoomName.innerText = selectedOption.text;
        }
        if (statusDiv) statusDiv.innerHTML = '<span class="text-muted"><i class="bi bi-hourglass-split"></i> Memeriksa ketersediaan...</span>';
        if (bookingInfo) bookingInfo.classList.add('d-none');

        fetch(`{{ route('peminjaman-ruangan.availability') }}?ruangan_id=${ruanganId}&tanggal=${tanggal}&waktu_mulai=${jamMulai}&waktu_selesai=${jamSelesai}`)
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(data => {
                if (!statusDiv) return;
                if (data.available) {
                    statusDiv.innerHTML = `<div class="alert alert-success py-2 mb-0"><i class="bi bi-check-circle-fill"></i> Ruangan tersedia pada jadwal ini.</div>`;
                    if (bookingInfo) bookingInfo.classList.remove('d-none');
                } else {
                    statusDiv.innerHTML = `<div class="alert alert-danger py-2 mb-0"><i class="bi bi-x-circle-fill"></i> <strong>Ruangan sudah terpakai!</strong><br><small>${data.message}</small><br><a href="{{ route('peminjaman-ruangan.kalender') }}" target="_blank" class="text-white"><u>Lihat kalender untuk cari ruangan lain</u></a></div>`;
                }
            })
            .catch((err) => {
                console.error('Error cek ketersediaan:', err);
                if (statusDiv) statusDiv.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Gagal memeriksa ketersediaan.</span>';
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const elRuangan = document.getElementById('input_ruangan');
        const elTanggal = document.querySelector('input[name="tanggal"]');
        const elJamMulai = document.querySelector('input[name="jam_mulai"]');
        const elJamSelesai = document.querySelector('input[name="jam_selesai"]');

        // Update preview tanggal saat load jika ada nilai default
        if (elTanggal && elTanggal.value) {
            updatePreviewTanggal(elTanggal);
        }

        [elRuangan, elTanggal, elJamMulai, elJamSelesai].forEach(el => {
            if (el) el.addEventListener('change', cekKetersediaanRuangan);
        });
    });
</script>
@endpush
@endsection