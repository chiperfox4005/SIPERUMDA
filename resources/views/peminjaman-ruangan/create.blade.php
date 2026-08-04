@extends('layouts.app')

@section('title', 'Peminjaman Ruangan')

@push('styles')
<style>
    :root {
        --primary-color: #1F3864;
    }
    
    .ruangan-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        border: 2px solid #e9ecef;
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .ruangan-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.12);
    }
    
    .ruangan-card.selected {
        border-color: var(--primary-color);
        background-color: #f0f4ff;
    }
    
    .kategori-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .kategori-OR-K { background-color: #dbeafe; color: #1e40af; }
    .kategori-OR-B { background-color: #dcfce7; color: #166534; }
    .kategori-Trandis { background-color: #fef3c7; color: #92400e; }
    .kategori-Joglo { background-color: #fce7f3; color: #9d174d; }
    
    .form-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
        padding: 24px;
        margin-bottom: 24px;
    }

    /* ============================================ */
    /* MODUL UPLOAD DOKUMEN                         */
    /* ============================================ */
    .upload-document-container {
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 30px 20px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }
    
    .upload-document-container:hover {
        border-color: var(--primary-color);
        background: #f0f4ff;
    }
    
    .upload-document-container.dragover {
        border-color: var(--primary-color);
        background: #e0e7ff;
        transform: scale(1.02);
    }
    
    .upload-document-container.has-file {
        border-color: #10b981;
        background: #ecfdf5;
    }
    
    .upload-icon {
        font-size: 3rem;
        color: #94a3b8;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    
    .upload-document-container:hover .upload-icon {
        color: var(--primary-color);
        transform: translateY(-5px);
    }
    
    .upload-document-container.has-file .upload-icon {
        color: #10b981;
    }
    
    .upload-text {
        font-size: 0.95rem;
        color: #475569;
        margin-bottom: 5px;
    }
    
    .upload-subtext {
        font-size: 0.8rem;
        color: #94a3b8;
    }
    
    .upload-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    
    .file-preview {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: white;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        margin-top: 15px;
    }
    
    .file-preview.show {
        display: flex;
    }
    
    .file-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    
    .file-icon.pdf { background: #fee2e2; color: #dc2626; }
    .file-icon.doc { background: #dbeafe; color: #2563eb; }
    .file-icon.img { background: #dcfce7; color: #166534; }
    .file-icon.default { background: #f1f5f9; color: #64748b; }
    
    .file-info {
        flex-grow: 1;
        text-align: left;
        min-width: 0;
    }
    
    .file-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .file-size {
        font-size: 0.75rem;
        color: #64748b;
    }
    
    .file-remove {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: none;
        background: #fee2e2;
        color: #dc2626;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    
    .file-remove:hover {
        background: #dc2626;
        color: white;
    }
    
    .upload-error {
        color: #dc2626;
        font-size: 0.85rem;
        margin-top: 10px;
        display: none;
    }
    
    .upload-error.show {
        display: block;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--primary-color);">Peminjaman Ruangan</h2>
            <p class="text-muted mb-0">Ajukan peminjaman ruangan untuk kegiatan Anda</p>
        </div>
        <a href="{{ route('peminjaman-ruangan.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- PENTING: enctype="multipart/form-data" wajib ada untuk upload file -->
    <form action="{{ route('peminjaman-ruangan.store') }}" method="POST" enctype="multipart/form-data" id="formPeminjaman">
        @csrf
        
        <!-- PILIH RUANGAN -->
        <div class="form-section">
            <h5 class="fw-bold mb-4"><i class="bi bi-door-open me-2"></i>Pilih Ruangan</h5>
            
            <div class="row">
                @foreach($ruangans as $ruangan)
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="ruangan-card" onclick="selectRuangan({{ $ruangan->id }}, '{{ $ruangan->nama_ruangan }}', {{ $ruangan->memerlukan_surat ? 'true' : 'false' }}, this)">
                        <span class="kategori-badge kategori-{{ str_replace('.', '-', $ruangan->kategori) }}">
                            {{ $ruangan->kategori }}
                        </span>
                        <h6 class="fw-bold mb-2">{{ $ruangan->nama_ruangan }}</h6>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-people me-1"></i>Kapasitas: {{ $ruangan->kapasitas }} orang
                        </p>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-star me-1"></i>{{ $ruangan->fasilitas }}
                        </p>
                        @if($ruangan->memerlukan_surat)
                            <div class="alert alert-warning mt-2 mb-0 py-2 small">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <strong>Wajib upload surat/dokumen</strong>
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            
            <input type="hidden" name="ruangan_id" id="ruangan_id" value="{{ old('ruangan_id') }}" required>
            @error('ruangan_id')
                <div class="alert alert-danger mt-2">{{ $message }}</div>
            @enderror
        </div>

        <!-- DETAIL PEMINJAMAN -->
        <div class="form-section">
            <h5 class="fw-bold mb-4"><i class="bi bi-calendar-check me-2"></i>Detail Peminjaman</h5>
            
            <div id="availability-warning" class="mb-3"></div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Pemakaian <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_pemakaian" class="form-control @error('tanggal_pemakaian') is-invalid @enderror" 
                           value="{{ old('tanggal_pemakaian') }}" required min="{{ date('Y-m-d') }}">
                    @error('tanggal_pemakaian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Waktu Mulai <span class="text-danger">*</span></label>
                    <input type="time" name="waktu_mulai" class="form-control @error('waktu_mulai') is-invalid @enderror" 
                           value="{{ old('waktu_mulai') }}" required>
                    @error('waktu_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Waktu Selesai <span class="text-danger">*</span></label>
                    <input type="time" name="waktu_selesai" class="form-control @error('waktu_selesai') is-invalid @enderror" 
                           value="{{ old('waktu_selesai') }}" required>
                    @error('waktu_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Jumlah Peserta <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_peserta" class="form-control @error('jumlah_peserta') is-invalid @enderror" 
                           value="{{ old('jumlah_peserta') }}" min="1" required>
                    @error('jumlah_peserta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Keperluan <span class="text-danger">*</span></label>
                    <textarea name="keperluan" rows="3" class="form-control @error('keperluan') is-invalid @enderror" 
                              required placeholder="Jelaskan tujuan dan keperluan peminjaman ruangan...">{{ old('keperluan') }}</textarea>
                    @error('keperluan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- MODUL UPLOAD DOKUMEN / LAMPIRAN -->
                <div class="col-12 mt-4">
                    <label class="form-label fw-semibold mb-2">
                        <i class="bi bi-paperclip me-2"></i>
                        Upload Dokumen Pendukung (Lampiran)
                        <span id="dokumen-required-badge" class="badge bg-danger ms-2" style="display: none;">Wajib</span>
                    </label>
                    
                    <div class="upload-document-container" id="uploadContainer">
                        <!-- PERHATIKAN: name dan id diubah menjadi 'lampiran' agar cocok dengan Controller -->
                        <input type="file" 
                               name="lampiran" 
                               id="lampiran" 
                               class="upload-input" 
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                               onchange="handleFileSelect(event)">
                        
                        <div id="uploadPlaceholder">
                            <div class="upload-icon">
                                <i class="bi bi-cloud-arrow-up"></i>
                            </div>
                            <div class="upload-text">
                                <strong>Klik untuk memilih file</strong> atau drag & drop di sini
                            </div>
                            <div class="upload-subtext">
                                Format: PDF, Word, Excel, atau Gambar | Maksimal 2MB
                            </div>
                        </div>
                        
                        <!-- File Preview -->
                        <div class="file-preview" id="filePreview">
                            <div class="file-icon" id="fileIcon">
                                <i class="bi bi-file-earmark"></i>
                            </div>
                            <div class="file-info">
                                <div class="file-name" id="fileName">-</div>
                                <div class="file-size" id="fileSize">-</div>
                            </div>
                            <button type="button" class="file-remove" onclick="removeFile(event)" title="Hapus file">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        
                        <!-- Error Message -->
                        <div class="upload-error" id="uploadError"></div>
                    </div>
                    
                    <!-- PERHATIKAN: error directive juga diubah menjadi 'lampiran' -->
                    @error('lampiran')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    
                    <small class="text-muted mt-2 d-block">
                        <i class="bi bi-info-circle me-1"></i>
                        Dokumen ini akan digunakan sebagai bukti resmi peminjaman ruangan
                    </small>
                </div>
            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="d-flex justify-content-end gap-3">
            <a href="{{ route('peminjaman-ruangan.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-2"></i>Batal
            </a>
            <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                <i class="bi bi-send me-2"></i>Ajukan Peminjaman
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// ============================================
// FUNGSI PEMILIHAN RUANGAN
// ============================================
function selectRuangan(id, nama, perluSurat, element) {
    document.querySelectorAll('.ruangan-card').forEach(card => {
        card.classList.remove('selected');
    });
    element.classList.add('selected');
    document.getElementById('ruangan_id').value = id;
    
    const requiredBadge = document.getElementById('dokumen-required-badge');
    if (perluSurat) {
        requiredBadge.style.display = 'inline-block';
    } else {
        requiredBadge.style.display = 'none';
    }
    checkAvailability();
}

document.querySelector('input[name="waktu_mulai"]').addEventListener('change', function() {
    const waktuMulai = this.value;
    const waktuSelesai = document.querySelector('input[name="waktu_selesai"]');
    waktuSelesai.min = waktuMulai;
});

document.querySelectorAll('input[name="tanggal_pemakaian"], input[name="waktu_mulai"], input[name="waktu_selesai"]').forEach(input => {
    input.addEventListener('change', checkAvailability);
});

function checkAvailability() {
    const ruanganId = document.getElementById('ruangan_id').value;
    const tanggal = document.querySelector('input[name="tanggal_pemakaian"]').value;
    const waktuMulai = document.querySelector('input[name="waktu_mulai"]').value;
    const waktuSelesai = document.querySelector('input[name="waktu_selesai"]').value;

    if (ruanganId && tanggal && waktuMulai && waktuSelesai) {
        fetch(`/peminjaman-ruangan/check-availability?ruangan_id=${ruanganId}&tanggal=${tanggal}&waktu_mulai=${waktuMulai}&waktu_selesai=${waktuSelesai}`)
            .then(response => response.json())
            .then(data => {
                const warningDiv = document.getElementById('availability-warning');
                if (!data.available) {
                    warningDiv.innerHTML = '<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>' + data.message + '</div>';
                    document.querySelector('button[type="submit"]').disabled = true;
                } else {
                    warningDiv.innerHTML = '<div class="alert alert-success mb-0"><i class="bi bi-check-circle-fill me-2"></i>' + data.message + '</div>';
                    document.querySelector('button[type="submit"]').disabled = false;
                }
            })
            .catch(error => console.error('Error checking availability:', error));
    }
}

// ============================================
// MODUL UPLOAD DOKUMEN / LAMPIRAN
// ============================================
const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
// Diperluas agar sesuai dengan validasi di Controller
const ALLOWED_TYPES = [
    'application/pdf', 
    'application/msword', 
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'image/jpeg',
    'image/png',
    'image/jpg'
];

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    const uploadContainer = document.getElementById('uploadContainer');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const filePreview = document.getElementById('filePreview');
    const uploadError = document.getElementById('uploadError');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const fileIcon = document.getElementById('fileIcon');

    uploadError.classList.remove('show');
    uploadError.textContent = '';

    // Validasi tipe file
    if (!ALLOWED_TYPES.includes(file.type)) {
        uploadError.textContent = '❌ Format file tidak valid. Hanya PDF, Word, Excel, dan Gambar yang diperbolehkan.';
        uploadError.classList.add('show');
        event.target.value = '';
        return;
    }

    // Validasi ukuran file
    if (file.size > MAX_FILE_SIZE) {
        uploadError.textContent = '❌ Ukuran file melebihi 2MB. Silakan pilih file yang lebih kecil.';
        uploadError.classList.add('show');
        event.target.value = '';
        return;
    }

    // Tampilkan preview file
    uploadPlaceholder.style.display = 'none';
    filePreview.classList.add('show');
    uploadContainer.classList.add('has-file');

    fileName.textContent = file.name;
    fileSize.textContent = formatFileSize(file.size);

    // Set icon berdasarkan tipe file
    fileIcon.className = 'file-icon';
    if (file.type === 'application/pdf') {
        fileIcon.classList.add('pdf');
        fileIcon.innerHTML = '<i class="bi bi-file-earmark-pdf"></i>';
    } else if (file.type.includes('word') || file.type === 'application/msword') {
        fileIcon.classList.add('doc');
        fileIcon.innerHTML = '<i class="bi bi-file-earmark-word"></i>';
    } else if (file.type.includes('image')) {
        fileIcon.classList.add('img');
        fileIcon.innerHTML = '<i class="bi bi-file-earmark-image"></i>';
    } else {
        fileIcon.classList.add('default');
        fileIcon.innerHTML = '<i class="bi bi-file-earmark"></i>';
    }
}

function removeFile(event) {
    event.preventDefault();
    event.stopPropagation();

    // PERHATIKAN: ID diubah menjadi 'lampiran'
    const uploadInput = document.getElementById('lampiran');
    const uploadContainer = document.getElementById('uploadContainer');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const filePreview = document.getElementById('filePreview');
    const uploadError = document.getElementById('uploadError');

    uploadInput.value = '';
    uploadContainer.classList.remove('has-file');
    uploadPlaceholder.style.display = 'block';
    filePreview.classList.remove('show');
    uploadError.classList.remove('show');
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Drag & Drop functionality
const uploadContainer = document.getElementById('uploadContainer');
const uploadInput = document.getElementById('lampiran'); // PERHATIKAN: ID diubah

uploadContainer.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadContainer.classList.add('dragover');
});

uploadContainer.addEventListener('dragleave', () => {
    uploadContainer.classList.remove('dragover');
});

uploadContainer.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadContainer.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        uploadInput.files = files;
        handleFileSelect({ target: { files: files } });
    }
});

// Form validation sebelum submit
document.getElementById('formPeminjaman').addEventListener('submit', function(e) {
    const ruanganId = document.getElementById('ruangan_id').value;
    const dokumenInput = document.getElementById('lampiran'); // PERHATIKAN: ID diubah
    const requiredBadge = document.getElementById('dokumen-required-badge');
    
    if (!ruanganId) {
        e.preventDefault();
        alert('Silakan pilih ruangan terlebih dahulu!');
        return false;
    }

    if (requiredBadge.style.display !== 'none' && !dokumenInput.files.length) {
        e.preventDefault();
        alert('Ruangan yang dipilih memerlukan upload dokumen resmi!');
        return false;
    }
});
</script>
@endpush
@endsection