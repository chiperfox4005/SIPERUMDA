@extends('layouts.app')

@section('title', 'Buat Pengumuman')

@push('styles')
<style>
    :root { --primary-color: #1F3864; }
    .form-card {
        background: white; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef; padding: 24px; margin-bottom: 24px;
    }
    .form-label { font-weight: 600; color: #495057; margin-bottom: 8px; }
    .scrollable-list {
        max-height: 500px; overflow-y: auto; border: 1px solid #dee2e6;
        border-radius: 8px; padding: 16px; background-color: #f8f9fa;
    }
    .sub-item { margin-left: 28px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--primary-color);">Buat Pengumuman</h2>
            <p class="text-muted mb-0">Isi form di bawah untuk membuat pengumuman baru.</p>
        </div>
        <a href="{{ route('pengumuman.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <form action="{{ route('pengumuman.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- 1. INFORMASI DASAR -->
        <div class="form-card">
            <h5 class="fw-bold mb-4">Informasi Dasar</h5>
            <div class="mb-3">
                <label class="form-label">Judul Pengumuman <span class="text-danger">*</span></label>
                <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul') }}" required placeholder="Contoh: Jadwal Libur Nasional">
                @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Pengumuman <span class="text-danger">*</span></label>
                    <select name="jenis" class="form-select @error('jenis') is-invalid @enderror" required>
                        <option value="">Pilih Jenis</option>
                        @foreach($jenisOptions as $opt)
                            <!-- PERBAIKAN: Gunakan == bukan === -->
                            <option value="{{ $opt['id'] }}" {{ old('jenis') == $opt['id'] ? 'selected' : '' }}>{{ $opt['nama'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Prioritas <span class="text-danger">*</span></label>
                    <select name="prioritas" class="form-select @error('prioritas') is-invalid @enderror" required>
                        <option value="">Pilih Prioritas</option>
                        @foreach($prioritasOptions as $opt)
                            <!-- PERBAIKAN: Gunakan == bukan === -->
                            <option value="{{ $opt['id'] }}" {{ old('prioritas') == $opt['id'] ? 'selected' : '' }}>{{ $opt['nama'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai') }}">
                </div>
            </div>
        </div>

        <!-- 2. TARGET PENERIMA (DISEDERHANAKAN) -->
        <div class="form-card">
            <h5 class="fw-bold mb-4">Target Penerima</h5>
            
            <div class="mb-3">
                <label class="form-label">Ditujukan Untuk <span class="text-danger">*</span></label>
                <select name="target_audience" id="target_audience" class="form-select @error('target_audience') is-invalid @enderror" required>
                    <option value="">-- Pilih Target --</option>
                    <option value="semua_pegawai" {{ old('target_audience') == 'semua_pegawai' ? 'selected' : '' }}>Semua Pegawai (Seluruh Organisasi)</option>
                    <option value="bagian_tertentu" {{ old('target_audience') == 'bagian_tertentu' ? 'selected' : '' }}>Bagian / Sub Bagian Tertentu (Custom)</option>
                </select>
                @error('target_audience') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- CONTAINER CUSTOM HIERARKIS -->
            <div id="container-bagian" class="d-none">
                <div class="alert alert-info d-flex align-items-center mb-3">
                    <i class="bi bi-info-circle me-2 fs-5"></i>
                    <div>
                        <strong>Panduan:</strong><br>
                        • Centang <u>"Pilih SELURUH Bagian Ini"</u> untuk mencakup semua pegawai di bagian tersebut.<br>
                        • Atau, centang <u>Sub Bagian</u> tertentu saja di bawahnya jika tidak ingin memilih semuanya.
                    </div>
                </div>

                <div class="scrollable-list">
                    @foreach($bagians as $bagian)
                    <div class="card mb-3 border-0 shadow-sm">
                        <!-- Header Bagian (Master Checkbox) -->
                        <div class="card-header bg-light d-flex align-items-center py-2">
                            <div class="form-check mb-0 w-100">
                                <input class="form-check-input check-master-bagian" type="checkbox" 
                                       id="master_bagian_{{ $bagian->id }}" data-bagian-id="{{ $bagian->id }}">
                                <label class="form-check-label fw-bold ms-2" for="master_bagian_{{ $bagian->id }}">
                                    {{ $bagian->nama_bagian }}
                                    <span class="badge bg-secondary ms-1">{{ $bagian->users_count ?? 0 }} Pegawai</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Body: Opsi Seluruh Bagian & Sub Bagian -->
                        <div class="card-body py-2">
                            <!-- Opsi 1: Pilih Seluruh Bagian -->
                            <div class="form-check mb-2 pb-2 border-bottom">
                                <input class="form-check-input bagian-checkbox" type="checkbox" 
                                       name="target_ids[]" value="bagian_{{ $bagian->id }}" 
                                       id="bagian_{{ $bagian->id }}" data-parent="{{ $bagian->id }}">
                                <label class="form-check-label fw-semibold text-primary" for="bagian_{{ $bagian->id }}">
                                    <i class="bi bi-building me-1"></i> Pilih SELURUH Bagian Ini
                                </label>
                            </div>

                            <!-- Opsi 2: Pilih Sub Bagian Tertentu -->
                            @if($bagian->subBagians->count() > 0)
                                <div class="ms-3 mt-2">
                                    <small class="text-muted d-block mb-2 fst-italic">Atau pilih Sub Bagian tertentu:</small>
                                    @foreach($bagian->subBagians as $sub)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input sub-checkbox" type="checkbox" 
                                               name="target_ids[]" value="sub_{{ $sub->id }}" 
                                               id="sub_{{ $sub->id }}" data-parent="{{ $bagian->id }}">
                                        <label class="form-check-label" for="sub_{{ $sub->id }}">
                                            {{ $sub->nama_sub_bagian }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <small class="text-muted fst-italic ms-3">Bagian ini tidak memiliki sub bagian.</small>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 3. KONTEN -->
        <div class="form-card">
            <h5 class="fw-bold mb-4">Konten</h5>
            <div class="mb-3">
                <label class="form-label">Isi Pengumuman <span class="text-danger">*</span></label>
                <textarea name="isi" rows="6" class="form-control @error('isi') is-invalid @enderror" required placeholder="Tulis isi pengumuman di sini...">{{ old('isi') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Upload Lampiran (Opsional)</label>
                <input type="file" name="lampiran" class="form-control @error('lampiran') is-invalid @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                <small class="text-muted">Format: PDF, DOC, XLS, PNG, JPG. Maksimal 2MB.</small>
            </div>
        </div>

        <!-- 4. STATUS -->
        <div class="form-card">
            <h5 class="fw-bold mb-4">Status</h5>
            <div class="mb-3">
                <label class="form-label">Status Publikasi <span class="text-danger">*</span></label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="">Pilih Status</option>
                    @foreach($statusOptions as $opt)
                        <!-- PERBAIKAN: Gunakan == bukan === -->
                        <option value="{{ $opt['id'] }}" {{ old('status') == $opt['id'] ? 'selected' : '' }}>{{ $opt['nama'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('pengumuman.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-2"></i>Batal</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan Pengumuman</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetSelect = document.getElementById('target_audience');
    const containerBagian = document.getElementById('container-bagian');
    
    // 1. Tampilkan/Sembunyikan Container
    function toggleContainer() {
        if (targetSelect.value === 'bagian_tertentu') {
            containerBagian.classList.remove('d-none');
        } else {
            containerBagian.classList.add('d-none');
            // Reset semua checkbox saat disembunyikan
            document.querySelectorAll('.form-check-input').forEach(cb => cb.checked = false);
        }
    }

    targetSelect.addEventListener('change', toggleContainer);
    
    // Jalankan saat load untuk old input
    if (targetSelect.value === 'bagian_tertentu') {
        toggleContainer();
        @if(old('target_ids'))
            @foreach((array)old('target_ids') as $oldId)
                const oldCb = document.querySelector(`input[value="{{ $oldId }}"]`);
                if(oldCb) {
                    oldCb.checked = true;
                    updateMasterCheckbox(oldCb.getAttribute('data-parent'));
                }
            @endforeach
        @endif
    }

    // 2. Logika Master Checkbox per Bagian
    document.querySelectorAll('.check-master-bagian').forEach(master => {
        master.addEventListener('change', function() {
            const bagianId = this.getAttribute('data-bagian-id');
            const isChecked = this.checked;
            
            // Centang/uncentang "Pilih SELURUH Bagian"
            const bagianCheckbox = document.querySelector(`#bagian_${bagianId}`);
            if(bagianCheckbox) bagianCheckbox.checked = isChecked;
            
            // Centang/uncentang semua Sub Bagian di dalam bagian ini
            document.querySelectorAll(`.sub-checkbox[data-parent="${bagianId}"]`).forEach(sub => {
                sub.checked = isChecked;
            });
        });
    });

    // 3. Update Master Checkbox jika anak-anaknya diubah
    function updateMasterCheckbox(bagianId) {
        const master = document.querySelector(`#master_bagian_${bagianId}`);
        const bagianCheckbox = document.querySelector(`#bagian_${bagianId}`);
        const subCheckboxes = document.querySelectorAll(`.sub-checkbox[data-parent="${bagianId}"]`);
        
        let allChecked = true;
        let someChecked = false;

        if (bagianCheckbox && bagianCheckbox.checked) allChecked = true;
        
        subCheckboxes.forEach(sub => {
            if (sub.checked) someChecked = true;
            else allChecked = false;
        });

        if (allChecked && (bagianCheckbox ? bagianCheckbox.checked : true)) {
            master.checked = true;
            master.indeterminate = false;
        } else if (someChecked) {
            master.checked = false;
            master.indeterminate = true; // Tanda garis tengah
        } else {
            master.checked = false;
            master.indeterminate = false;
        }
    }

    // Pasang event listener ke semua checkbox anak
    document.querySelectorAll('.bagian-checkbox, .sub-checkbox').forEach(child => {
        child.addEventListener('change', function() {
            updateMasterCheckbox(this.getAttribute('data-parent'));
        });
    });
});
</script>
@endpush
@endsection