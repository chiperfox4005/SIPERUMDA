<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Surat - SIPERUMDA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1F3864 0%, #3b82f6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .tracking-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #1F3864;
            font-size: 2rem;
            margin-bottom: 5px;
        }
        .logo p {
            color: #64748b;
            font-size: 0.9rem;
        }
        .form-label {
            font-weight: 600;
            color: #1F3864;
        }
        .btn-track {
            background: linear-gradient(135deg, #1F3864 0%, #3b82f6 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
        }
        .btn-track:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(31, 56, 100, 0.4);
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #1F3864;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="tracking-card">
        <div class="logo">
            <i class="bi bi-search" style="font-size: 3rem; color: #1F3864;"></i>
            <h1 class="fw-bold">TRACKING SURAT</h1>
            <p>SIPERUMDA - Sistem Informasi Pengelolaan Ruangan</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('surat.track.post') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="nip" class="form-label">
                    <i class="bi bi-person-badge me-2"></i>Masukkan NIP/NPP Anda
                </label>
                <input type="text" 
                       name="nip" 
                       id="nip" 
                       class="form-control form-control-lg" 
                       placeholder="Contoh: 123456789" 
                       value="{{ old('nip') }}"
                       required
                       autofocus>
                <small class="text-muted">Masukkan NIP/NPP yang digunakan saat mengajukan surat</small>
            </div>
            
            <button type="submit" class="btn btn-primary btn-track w-100">
                <i class="bi bi-search me-2"></i>Lacak Status Surat
            </button>
        </form>

        <div class="back-link">
            <a href="{{ url('/') }}"><i class="bi bi-house me-1"></i>Kembali ke Beranda</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>