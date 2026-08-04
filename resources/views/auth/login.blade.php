<x-guest-layout>
    <!-- Background Full Layar (Fixed menutupi seluruh viewport termasuk layout bawaan breeze) -->
    <div class="auth-fullscreen-bg">
        <div class="auth-bg-overlay"></div>

        <!-- Box Container Tengah (Login Card) -->
        <div class="auth-card-wrapper">
            <div class="form-container">
                <div class="form-header">
                    <div class="logo-circle">
                        <img src="{{ asset('images/icon.png') }}" alt="Logo SIPERUMDA" class="logo-img">
                    </div>
                    <h2>SIPERUMDA</h2>
                    <p>Silakan masukkan NIP dan kata sandi Anda untuk melanjutkan.</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="modern-form">
                    @csrf

                    <!-- NIP Input -->
                    <div class="form-group">
                        <label for="nip" class="form-label">NIP (Nomor Induk Pegawai)</label>
                        <div class="input-icon-wrapper">
                            <i class="bi bi-person-badge input-icon"></i>
                            <input id="nip" type="text" 
                                   class="form-control @error('nip') is-invalid @enderror" 
                                   name="nip" 
                                   value="{{ old('nip') }}" 
                                   required 
                                   autofocus 
                                   autocomplete="username" 
                                   placeholder="Contoh: 199001012020011001">
                        </div>
                        @error('nip')
                            <div class="invalid-feedback d-block">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div class="form-group">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <div class="input-icon-wrapper">
                            <i class="bi bi-lock input-icon"></i>
                            <input id="password" type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   name="password" 
                                   required 
                                   autocomplete="current-password" 
                                   placeholder="Masukkan kata sandi Anda">
                            <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="form-options">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Ingat saya
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit">
                        <span>Masuk ke Sistem</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>

                    <!-- Register Link -->
                    <div class="form-footer">
                        <p>Belum memiliki akun? <a href="{{ route('register') }}" class="link-primary">Daftar sebagai pegawai baru</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        * { box-sizing: border-box; }
        
        /* Memaksa elemen bawaan Laravel guest layout agar transparan & tidak membatasi ukuran */
        body, html, main {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background: transparent !important;
        }

        /* Sembunyikan logo/elemen bawaan luar dari guest layout jika ada */
        body > div > div > div:first-child:not(.auth-fullscreen-bg) {
            display: none !important;
        }

        /* Container utama melapis seluruh layar monitor secara mutlak */
        .auth-fullscreen-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url("{{ asset('images/foto_kantor2.jpg') }}") no-repeat center center;
            background-size: cover;
            z-index: 9999;
            overflow-y: auto;
            padding: 2rem 1rem;
        }

        /* Lapisan gelap transparan di atas background foto kantor */
        .auth-bg-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(31, 56, 100, 0.75) 0%, rgba(15, 23, 42, 0.82) 100%);
            z-index: 1;
        }

        /* Pembungkus kartu form di tengah layar */
        .auth-card-wrapper {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 440px;
            display: flex;
            justify-content: center;
        }

        .form-container {
            width: 100%;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .form-header {
            margin-bottom: 1.75rem;
            text-align: center;
        }

        /* Styling Lingkaran Logo dengan Gambar icon.png */
        .logo-circle {
            width: 75px;
            height: 75px;
            background: rgba(31, 56, 100, 0.08);
            border: 1px solid rgba(31, 56, 100, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            overflow: hidden;
        }

        .logo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .form-header h2 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1F3864;
            margin-bottom: 0.4rem;
        }

        .form-header p {
            color: #6c757d;
            font-size: 0.85rem;
            line-height: 1.4;
        }

        .modern-form .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #333333;
            margin-bottom: 0.4rem;
            font-size: 0.85rem;
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            font-size: 1rem;
            transition: color 0.3s;
        }

        .form-control {
            width: 100%;
            padding: 11px 14px 11px 44px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background-color: #fff;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #1F3864;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(31, 56, 100, 0.15);
        }

        .form-control:focus + .input-icon, 
        .input-icon-wrapper:focus-within .input-icon {
            color: #1F3864;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
            background-color: #fff8f8;
        }

        .invalid-feedback {
            font-size: 0.8rem;
            color: #dc3545;
            margin-top: 0.4rem;
            display: flex;
            align-items: center;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #adb5bd;
            cursor: pointer;
            padding: 4px;
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: #333333;
        }

        .form-options {
            display: flex;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .form-check-input {
            width: 16px;
            height: 16px;
            margin-right: 0.5rem;
            cursor: pointer;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .form-check-input:checked {
            background-color: #1F3864;
            border-color: #1F3864;
        }

        .form-check-label {
            font-size: 0.85rem;
            color: #495057;
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            padding: 11px;
            background-color: #1F3864;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            background-color: #16294a;
        }

        .form-footer {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 0.85rem;
            color: #6c757d;
        }

        .link-primary {
            color: #1F3864;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s;
        }

        .link-primary:hover {
            color: #16294a;
            text-decoration: underline;
        }
    </style>

    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
</x-guest-layout>