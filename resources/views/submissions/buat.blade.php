@extends('layouts.app')

@section('title', 'Buat ' . $template->name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1F3864;">Buat {{ $template->name }}</h2>
            <p class="text-muted mb-0">Lengkapi data di bawah ini untuk mengajukan surat</p>
        </div>
        <a href="{{ route('surat.pilih-template') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Ganti Jenis Surat
        </a>
    </div>

    {{-- ✅ Tampilkan error validasi agar user tahu apa yang salah --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <h6 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Gagal Mengirim Surat:</h6>
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('surat.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="template_id" value="{{ $template->id }}">

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row g-3">
                    @foreach($template->form_schema['fields'] ?? [] as $field)
                        <div class="{{ in_array($field['type'], ['textarea', 'participant_selector']) ? 'col-12' : 'col-md-6' }}">
                            <label class="form-label fw-semibold">
                                {{ $field['label'] }} 
                                @if(!empty($field['required'])) <span class="text-danger">*</span> @endif
                            </label>

                            @php
                                $requiredAttr = !empty($field['required']) ? 'required' : '';
                                $oldValue = old($field['name']);
                            @endphp

                            @if(in_array($field['type'], ['text', 'number', 'email']))
                                <input type="{{ $field['type'] }}" name="{{ $field['name'] }}" 
                                       class="form-control @error($field['name']) is-invalid @enderror" 
                                       value="{{ $oldValue }}" {{ $requiredAttr }}>
                            
                            @elseif($field['type'] === 'textarea')
                                <textarea name="{{ $field['name'] }}" rows="4" 
                                          class="form-control @error($field['name']) is-invalid @enderror" 
                                          {{ $requiredAttr }}>{{ $oldValue }}</textarea>
                            
                            @elseif($field['type'] === 'date')
                                <input type="date" name="{{ $field['name'] }}" 
                                       class="form-control @error($field['name']) is-invalid @enderror" 
                                       value="{{ $oldValue }}" {{ $requiredAttr }}>
                            
                            @elseif($field['type'] === 'time')
                                <input type="time" name="{{ $field['name'] }}" 
                                       class="form-control @error($field['name']) is-invalid @enderror" 
                                       value="{{ $oldValue }}" {{ $requiredAttr }}>
                            
                            @elseif($field['type'] === 'select')
                                <select name="{{ $field['name'] }}" class="form-select @error($field['name']) is-invalid @enderror" {{ $requiredAttr }}>
                                    <option value="">-- Pilih {{ $field['label'] }} --</option>
                                    @if(isset($options[$field['source']]))
                                        @foreach($options[$field['source']] as $opt)
                                            @php
                                                $optValue = $opt->nip ?? $opt->id;
                                                $optLabel = $opt->nama_ruangan ?? $opt->nama_lengkap ?? $opt->name ?? 'Opsi';
                                            @endphp
                                            <option value="{{ $optValue }}" {{ $oldValue == $optValue ? 'selected' : '' }}>
                                                {{ $optLabel }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>

                            @elseif($field['type'] === 'participant_selector')
                                <div class="alert alert-light border mb-3 py-2">
                                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Centang Jabatan (Kabag/Kasie). Gunakan "Tambah Manual" untuk nama staf spesifik.</small>
                                </div>

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
                                                    <input class="form-check-input peserta-checkbox" type="checkbox" 
                                                           value="Kabag. {{ $bagian->nama_bagian }}" 
                                                           id="kabag_{{ $bagian->id }}">
                                                    <label class="form-check-label fw-bold text-primary" for="kabag_{{ $bagian->id }}">
                                                        Kabag. {{ $bagian->nama_bagian }}
                                                    </label>
                                                </div>
                                                @foreach($bagian->subBagians as $sub)
                                                <div class="form-check mb-2 ms-3">
                                                    <input class="form-check-input peserta-checkbox" type="checkbox" 
                                                           value="Kasie. {{ $sub->nama_sub_bagian }}" 
                                                           id="sub_{{ $sub->id }}">
                                                    <label class="form-check-label" for="sub_{{ $sub->id }}">
                                                        Kasie. {{ $sub->nama_sub_bagian }}
                                                    </label>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <div class="p-3 border rounded bg-light">
                                    <label class="form-label fw-semibold mb-2 small"><i class="bi bi-person-plus me-1"></i> Tambah Peserta Manual (Staf)</label>
                                    <div id="manualPesertaContainer"></div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="tambahPesertaManual()">
                                        <i class="bi bi-plus-circle"></i> Tambah Nama
                                    </button>
                                </div>

                                <div class="mt-2 p-2 bg-white border rounded small text-muted" style="white-space: pre-line; min-height: 40px; max-height: 100px; overflow-y: auto;" id="pesertaPreview">
                                    Belum ada peserta yang dipilih...
                                </div>

                                <textarea name="{{ $field['name'] }}" id="finalPesertaInput" class="d-none" {{ $requiredAttr }}>{{ $oldValue }}</textarea>
                            @endif

                            @error($field['name'])
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>

                {{-- ✅ KEMBALI KE PILIH TEMPLATE / PILIH JENIS SURAT LAIN --}}
                <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                    <a href="{{ route('surat.pilih-template') }}" class="btn btn-light border">
                        <i class="bi bi-arrow-left me-2"></i>Pilih Jenis Surat Lain
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-send me-2"></i>Ajukan Surat
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function updatePesertaList() {
        let pesertaList = [];
        document.querySelectorAll('.peserta-checkbox:checked').forEach(cb => {
            pesertaList.push(cb.value);
        });
        document.querySelectorAll('.manual-peserta-row').forEach(row => {
            let bagian = row.querySelector('.manual-peserta-bagian').value;
            let nama = row.querySelector('.manual-peserta-nama').value.trim();
            if (bagian && nama) {
                pesertaList.push(`${nama} (${bagian})`);
            }
        });
        let finalString = pesertaList.join('\n');
        document.getElementById('finalPesertaInput').value = finalString;
        document.getElementById('pesertaPreview').innerText = finalString || 'Belum ada peserta yang dipilih...';
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
            <select class="form-select form-select-sm manual-peserta-bagian" style="max-width: 40%;" required onchange="updatePesertaList()">
                ${optionsHtml}
            </select>
            <input type="text" class="form-control form-control-sm manual-peserta-nama" placeholder="Nama Staf / Jabatan" required onkeyup="updatePesertaList()">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove(); updatePesertaList();" title="Hapus">
                <i class="bi bi-trash"></i>
            </button>
        `;
        container.appendChild(div);
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.peserta-checkbox').forEach(cb => {
            cb.addEventListener('change', updatePesertaList);
        });
        updatePesertaList();
    });
</script>
@endpush
@endsection