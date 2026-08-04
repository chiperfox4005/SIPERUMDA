@extends('layouts.app')

@section('title', 'Buat Agenda/Kegiatan Baru')

@push('styles')
<style>
    .peserta-item { display: flex; align-items: center; padding: 6px 0; border-bottom: 1px dashed #e2e8f0; }
    .peserta-item:last-child { border-bottom: none; }
    .peserta-item input[type="checkbox"] { margin-right: 10px; transform: scale(1.2); }
    .peserta-item label { cursor: pointer; flex-grow: 1; margin: 0; font-size: 0.9rem; }
    .sub-bagian-label { padding-left: 25px; color: #475569; font-size: 0.85rem; }
    .manual-participant-row { display: flex; gap: 10px; margin-top: 10px; align-items: center; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Buat Agenda / Kegiatan Baru</h2>
                <a href="{{ route('agenda.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
            </div>

            {{-- ✅ KOTAK ERROR VALIDASI --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4">
                    <h6 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Gagal Mengirim: Periksa kembali data Anda</h6>
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('agenda.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Judul & Deskripsi -->
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Judul Kegiatan / Agenda <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul') }}" required>
                                @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Deskripsi / Topik Kegiatan <span class="text-danger">*</span></label>
                                <textarea name="acara" class="form-control @error('acara') is-invalid @enderror" rows="2" required>{{ old('acara') }}</textarea>
                                @error('acara') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Waktu -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Hari & Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai') }}" required>
                                @error('tanggal_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_mulai" class="form-control @error('jam_mulai') is-invalid @enderror" value="{{ old('jam_mulai') }}" required>
                                @error('jam_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Waktu Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_selesai" class="form-control @error('jam_selesai') is-invalid @enderror" value="{{ old('jam_selesai') }}" required>
                                @error('jam_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Lokasi -->
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Lokasi Kegiatan <span class="text-danger">*</span></label>
                                <div class="d-flex gap-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="jenis_lokasi" id="lokasiRuangan" value="ruangan" checked onchange="toggleLokasi()">
                                        <label class="form-check-label" for="lokasiRuangan">Ruangan Kantor</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="jenis_lokasi" id="lokasiManual" value="manual" onchange="toggleLokasi()">
                                        <label class="form-check-label" for="lokasiManual">Tempat Lainnya (Manual)</label>
                                    </div>
                                </div>

                                <div id="boxRuangan">
                                    <select name="ruangan_id" id="selectRuangan" class="form-select @error('ruangan_id') is-invalid @enderror">
                                        <option value="">-- Pilih Ruangan --</option>
                                        @foreach(\App\Models\Ruangan::where('status', 'aktif')->get() as $ruangan)
                                            <option value="{{ $ruangan->id }}" {{ old('ruangan_id') == $ruangan->id ? 'selected' : '' }}>
                                                {{ $ruangan->nama_ruangan }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('ruangan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div id="boxManual" style="display: none;">
                                    <input type="text" name="tempat_manual" id="inputTempat" class="form-control @error('tempat_manual') is-invalid @enderror" placeholder="Contoh: Lapangan Utama, Gedung Serbaguna" value="{{ old('tempat_manual') }}">
                                    @error('tempat_manual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Penanggung Jawab / Pimpinan</label>
                                <input type="text" name="pimpinan_rapat" class="form-control" value="{{ old('pimpinan_rapat', 'Direktur Teknik') }}">
                            </div>

                            <!-- PIC & Notulen -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Inisiator / Pengaju</label>
                                <input type="text" name="inisiator" class="form-control" value="{{ old('inisiator', auth()->user()->bagian->nama_bagian ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">PIC / Notulen</label>
                                <input type="text" name="notulen" class="form-control" value="{{ old('notulen') }}">
                            </div>

                            <!-- Lampiran -->
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Lampiran Dokumen (Opsional)</label>
                                <input type="file" name="lampiran" class="form-control @error('lampiran') is-invalid @enderror" accept=".pdf,.doc,.docx,.jpg,.png,.jpeg">
                                @error('lampiran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Format: PDF, Word, atau Gambar. Maksimal 5MB.</small>
                            </div>

                            <!-- Peserta -->
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Peserta / Undangan</label>
                                <div class="alert alert-light border mb-3">
                                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Centang Bagian/Sub Bagian dari daftar. Untuk nama staff spesifik, gunakan form "Tambah Peserta Manual" di bawah.</small>
                                </div>

                                @php 
                                    $oldPeserta = old('peserta', []);
                                    if (!is_array($oldPeserta)) {
                                        $oldPeserta = [];
                                    }
                                @endphp

                                <div class="accordion" id="accordionPeserta">
                                    @foreach($bagians as $bagian)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $bagian->id }}">
                                                <i class="bi bi-building me-2"></i> {{ $bagian->nama_bagian }}
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $bagian->id }}" class="accordion-collapse collapse" data-bs-parent="#accordionPeserta">
                                            <div class="accordion-body p-3">
                                                <div class="peserta-item">
                                                    <input class="form-check-input peserta-checkbox" type="checkbox" 
                                                           name="peserta[]" 
                                                           value="{{ $bagian->nama_bagian }}" 
                                                           id="kabag_{{ $bagian->id }}" 
                                                           {{ in_array($bagian->nama_bagian, $oldPeserta) ? 'checked' : '' }}
                                                           data-sumber="accordion">
                                                    <label for="kabag_{{ $bagian->id }}" class="fw-bold text-primary">Kabag. {{ $bagian->nama_bagian }}</label>
                                                </div>
                                                @foreach($bagian->subBagians as $sub)
                                                    <div class="peserta-item">
                                                        <input class="form-check-input peserta-checkbox" type="checkbox" 
                                                               name="peserta[]" 
                                                               value="{{ $sub->nama_sub_bagian }}" 
                                                               id="sub_{{ $sub->id }}" 
                                                               {{ in_array($sub->nama_sub_bagian, $oldPeserta) ? 'checked' : '' }}
                                                               data-sumber="accordion">
                                                        <label for="sub_{{ $sub->id }}" class="sub-bagian-label">Ka. Sub. {{ $sub->nama_sub_bagian }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <!-- ✅ PREVIEW PESERTA REAL-TIME -->
                                <div class="mt-3 p-3 border rounded bg-light">
                                    <label class="form-label fw-semibold mb-2"><i class="bi bi-list-check me-1"></i> Peserta yang Dipilih:</label>
                                    <div id="previewPesertaContainer" class="small text-muted">
                                        <em>Belum ada peserta yang dipilih</em>
                                    </div>
                                </div>

                                <!-- Form Peserta Manual -->
                                <div class="mt-3 p-3 border rounded bg-light">
                                    <label class="form-label fw-semibold mb-2"><i class="bi bi-person-plus me-1"></i> Tambah Peserta Manual (Terikat Bagian)</label>
                                    <div id="manualPesertaContainer"></div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="tambahPesertaManual()">
                                        <i class="bi bi-plus-circle"></i> Tambah Baris Peserta
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('agenda.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-2"></i>Simpan & Ajukan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // 1. Toggle Lokasi
    function toggleLokasi() {
        const jenis = document.querySelector('input[name="jenis_lokasi"]:checked').value;
        const boxRuangan = document.getElementById('boxRuangan');
        const boxManual = document.getElementById('boxManual');
        const selectRuangan = document.getElementById('selectRuangan');
        const inputTempat = document.getElementById('inputTempat');

        if (jenis === 'ruangan') {
            boxRuangan.style.display = 'block';
            boxManual.style.display = 'none';
            selectRuangan.required = true;
            inputTempat.required = false;
            inputTempat.value = ''; 
        } else {
            boxRuangan.style.display = 'none';
            boxManual.style.display = 'block';
            selectRuangan.required = false;
            selectRuangan.value = ''; 
            inputTempat.required = true;
        }
    }

    // 2. Tambah Peserta Manual
    function tambahPesertaManual() {
        const container = document.getElementById('manualPesertaContainer');
        const div = document.createElement('div');
        div.className = 'manual-participant-row';
        
        let optionsHtml = '<option value="">-- Pilih Bagian/Sub Bagian --</option>';
        @foreach($bagians as $bagian)
            optionsHtml += '<option value="{{ $bagian->nama_bagian }}">{{ $bagian->nama_bagian }}</option>';
            @foreach($bagian->subBagians as $sub)
                optionsHtml += '<option value="{{ $sub->nama_sub_bagian }}" style="padding-left: 20px;">↳ {{ $sub->nama_sub_bagian }}</option>';
            @endforeach
        @endforeach

        div.innerHTML = `
            <select name="peserta_manual_bagian[]" class="form-select form-select-sm manual-bagian" style="flex: 1;" required onchange="updatePreviewPeserta()">
                ${optionsHtml}
            </select>
            <input type="text" name="peserta_manual_nama[]" class="form-control form-control-sm manual-nama" placeholder="Nama Pegawai / Jabatan" style="flex: 1.5;" required onkeyup="updatePreviewPeserta()">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove(); updatePreviewPeserta();" title="Hapus">
                <i class="bi bi-trash"></i>
            </button>
        `;
        container.appendChild(div);
        updatePreviewPeserta();
    }

    // 3. Update Preview Peserta Real-Time
    function updatePreviewPeserta() {
        const previewContainer = document.getElementById('previewPesertaContainer');
        let pesertaList = [];

        // Ambil dari checkbox accordion
        document.querySelectorAll('.peserta-checkbox:checked').forEach(cb => {
            pesertaList.push({
                nama: cb.value,
                sumber: 'accordion'
            });
        });

        // Ambil dari input manual
        document.querySelectorAll('.manual-participant-row').forEach(row => {
            const bagian = row.querySelector('.manual-bagian').value;
            const nama = row.querySelector('.manual-nama').value.trim();
            if (bagian && nama) {
                pesertaList.push({
                    nama: `${nama} (${bagian})`,
                    sumber: 'manual'
                });
            }
        });

        // Render HTML Preview
        if (pesertaList.length === 0) {
            previewContainer.innerHTML = '<em>Belum ada peserta yang dipilih</em>';
        } else {
            let html = '<ul class="list-unstyled mb-0">';
            pesertaList.forEach(p => {
                const badge = p.sumber === 'accordion' 
                    ? '<span class="badge bg-primary ms-2">Dari Daftar</span>' 
                    : '<span class="badge bg-success ms-2">Manual</span>';
                html += `<li class="mb-1"><i class="bi bi-person-fill me-1"></i>${p.nama} ${badge}</li>`;
            });
            html += '</ul>';
            previewContainer.innerHTML = html;
        }
    }

    // Inisialisasi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        toggleLokasi();
        
        // Event listener untuk perubahan pada checkbox accordion
        document.querySelectorAll('.peserta-checkbox').forEach(cb => {
            cb.addEventListener('change', updatePreviewPeserta);
        });
        
        // Panggil preview pertama kali (untuk menghandle data old jika ada)
        updatePreviewPeserta();
    });
</script>
@endpush
@endsection