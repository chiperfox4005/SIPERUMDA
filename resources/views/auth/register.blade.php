<x-guest-layout>
    <!-- Background Full Layar (Fixed menutupi seluruh viewport termasuk layout bawaan breeze) -->
    <div class="auth-fullscreen-bg">
        <div class="auth-bg-overlay"></div>

        <!-- Box Container Tengah (Register Card) -->
        <div class="auth-card-wrapper">
            <div class="form-container">
                <div class="form-header">
                    <div class="logo-circle">
                        <img src="{{ asset('images/icon.png') }}" alt="Logo SIPERUMDA" class="logo-img">
                    </div>
                    <h2>Buat Akun Baru</h2>
                    <p>Lengkapi data diri Anda untuk memulai.</p>
                </div>

                <div class="info-box mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <span>Hanya pegawai aktif yang dapat mendaftar.</span>
                </div>

                <form method="POST" action="{{ route('register') }}" class="modern-form">
                    @csrf

                    <!-- Row 1: Nama & NIP -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" placeholder="Sesuai kartu pegawai" required>
                            @error('nama_lengkap') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="nip" class="form-label">NIP</label>
                            <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip" name="nip" value="{{ old('nip') }}" placeholder="Nomor induk pegawai" required>
                            @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Row 2: Bagian & Sub Bagian -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="bagian_id" class="form-label">Bagian</label>
                            <select class="form-select form-control @error('bagian_id') is-invalid @enderror" id="bagian_id" name="bagian_id" required>
                                <option value="">Pilih Bagian...</option>
                                @foreach($bagians as $bagian)
                                    <option value="{{ $bagian->id }}" {{ old('bagian_id') == $bagian->id ? 'selected' : '' }}>
                                        {{ $bagian->nama_bagian }}
                                    </option>
                                @endforeach
                            </select>
                            @error('bagian_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="sub_bagian_id" class="form-label">Sub Bagian</label>
                            <select class="form-select form-control @error('sub_bagian_id') is-invalid @enderror" id="sub_bagian_id" name="sub_bagian_id" required disabled>
                                <option value="">Pilih Bagian terlebih dahulu</option>
                            </select>
                            @error('sub_bagian_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Row 3: Password & Confirm -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label">Kata Sandi</label>
                            <div class="input-icon-wrapper">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Min. 8 karakter" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password', this)" tabindex="-1">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
                            <div class="input-icon-wrapper">
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', this)" tabindex="-1">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Terms Checkbox -->
                    <div class="form-group form-check terms-check">
                        <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            Saya menyetujui <a href="#" class="link-primary">Syarat dan Ketentuan</a> penggunaan layanan SIPERUMDA
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit">
                        <span>Daftar Sekarang</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>

                    <!-- Login Link -->
                    <div class="form-footer">
                        <p>Sudah memiliki akun? <a href="{{ route('login') }}" class="link-primary">Masuk di sini</a></p>
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

        /* Sembunyikan elemen sisa layout bawaan luar */
        body > div > div > div:first-child:not(.auth-fullscreen-bg) {
            display: none !important;
        }

        /* Container utama melapis seluruh layar monitor secara mutlak dengan background foto kantor */
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

        /* Pembungkus kartu form di tengah layar dengan lebar yang pas untuk register dua kolom */
        .auth-card-wrapper {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 640px;
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
            margin-bottom: 1.5rem;
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

        .info-box {
            background: #eef2f7;
            border: 1px solid #d1d9e6;
            border-radius: 8px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            color: #1F3864;
            font-size: 0.85rem;
            margin-bottom: 1.25rem !important;
        }

        .modern-form .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .modern-form .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #333333;
            margin-bottom: 0.3rem;
            font-size: 0.85rem;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background-color: #fff;
            font-family: inherit;
            color: #333;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #1F3864;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(31, 56, 100, 0.15);
        }

        .form-control.is-invalid, .form-select.is-invalid {
            border-color: #dc3545;
            background-color: #fff8f8;
        }

        .invalid-feedback {
            font-size: 0.8rem;
            color: #dc3545;
            margin-top: 0.3rem;
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon-wrapper .form-control {
            padding-right: 40px;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
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

        .terms-check {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-top: 0.5rem;
            margin-bottom: 1.25rem;
        }

        .form-check-input {
            width: 16px;
            height: 16px;
            margin-top: 2px;
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
            line-height: 1.3;
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

        // Script untuk Sub Bagian Dinamis
        document.getElementById('bagian_id').addEventListener('change', function() {
            const bagianId = this.value;
            const subBagianSelect = document.getElementById('sub_bagian_id');
            
            subBagianSelect.innerHTML = '<option value="">Memuat data...</option>';
            subBagianSelect.disabled = true;

            if (bagianId) {
                fetch(`/api/v1/sub-bagians?bagian_id=${bagianId}`)
                    .then(response => response.json())
                    .then(data => {
                        subBagianSelect.innerHTML = '<option value="">Pilih Sub Bagian...</option>';
                        data.forEach(sub => {
                            subBagianSelect.innerHTML += `<option value="${sub.id}">${sub.nama_sub_bagian}</option>`;
                        });
                        subBagianSelect.disabled = false;
                    })
                    .catch(() => {
                        subBagianSelect.innerHTML = '<option value="">Gagal memuat data</option>';
                    });
            } else {
                subBagianSelect.innerHTML = '<option value="">Pilih Bagian terlebih dahulu</option>';
            }
        });
    </script>
</x-guest-layout>