@extends('layouts.app')

@section('title', 'Buat Surat Baru')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Buat Surat Baru</h2>
            <p class="text-muted mb-0">Pilih jenis surat dan lengkapi data</p>
        </div>
        <a href="{{ route('surat.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <form action="{{ route('surat.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Pilih Template -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <label class="form-label fw-semibold">Jenis Surat <span class="text-danger">*</span></label>
                <select name="template_id" class="form-select @error('template_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Jenis Surat --</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                            {{ $template->name }} ({{ $template->code }})
                        </option>
                    @endforeach
                </select>
                @error('template_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <!-- Dynamic Form Fields (akan di-load via JavaScript) -->
        <div id="dynamicForm" class="card border-0 shadow-sm" style="display: none;">
            <div class="card-body">
                <h5 class="card-title mb-4">Data Surat</h5>
                <div id="formFields"></div>
                
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                        <i class="bi bi-x-lg me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-send me-2"></i>Ajukan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
let formSchema = null;

// Load form schema saat template dipilih
document.querySelector('select[name="template_id"]').addEventListener('change', function() {
    const templateId = this.value;
    if (!templateId) {
        document.getElementById('dynamicForm').style.display = 'none';
        return;
    }
    
    // Fetch form schema dari backend (atau bisa di-embed di blade)
    fetch(`/api/templates/${templateId}/schema`)
        .then(response => response.json())
        .then(schema => {
            formSchema = schema;
            renderFormFields(schema);
            document.getElementById('dynamicForm').style.display = 'block';
        });
});

function renderFormFields(schema) {
    const container = document.getElementById('formFields');
    container.innerHTML = '';
    
    schema.fields.forEach(field => {
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-3';
        
        const label = document.createElement('label');
        label.className = 'form-label fw-semibold';
        label.textContent = field.label + (field.required ? ' *' : '');
        wrapper.appendChild(label);
        
        let input;
        const required = field.required ? 'required' : '';
        
        switch (field.type) {
            case 'text':
            case 'email':
            case 'number':
                input = document.createElement('input');
                input.type = field.type;
                input.name = field.name;
                input.className = 'form-control';
                input.required = field.required;
                break;
                
            case 'textarea':
                input = document.createElement('textarea');
                input.name = field.name;
                input.className = 'form-control';
                input.rows = 3;
                input.required = field.required;
                break;
                
            case 'date':
                input = document.createElement('input');
                input.type = 'date';
                input.name = field.name;
                input.className = 'form-control';
                input.required = field.required;
                break;
                
            case 'time':
                input = document.createElement('input');
                input.type = 'time';
                input.name = field.name;
                input.className = 'form-control';
                input.required = field.required;
                break;
                
            case 'select':
                input = document.createElement('select');
                input.name = field.name;
                input.className = 'form-select';
                input.required = field.required;
                
                // Load options dari source (ruangans, users, etc)
                fetch(`/api/${field.source}/options`)
                    .then(res => res.json())
                    .then(options => {
                        options.forEach(opt => {
                            const option = document.createElement('option');
                            option.value = opt.id;
                            option.textContent = opt.name;
                            input.appendChild(option);
                        });
                    });
                break;
        }
        
        wrapper.appendChild(input);
        container.appendChild(wrapper);
    });
}

function resetForm() {
    document.getElementById('dynamicForm').style.display = 'none';
    document.getElementById('formFields').innerHTML = '';
    document.querySelector('select[name="template_id"]').value = '';
}
</script>
@endpush
@endsection