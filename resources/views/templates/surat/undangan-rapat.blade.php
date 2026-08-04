<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Undangan Rapat - {{ $nomorSurat ?? 'Draft' }}</title>
    <style>
        /* Reset & Base */
        * { box-sizing: border-box; }
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 12pt; 
            line-height: 1.4; 
            color: #000; 
            margin: 0; 
            padding: 0; 
        }
        
        /* Page Setup (A4) */
        @page { 
            margin: 2.5cm 2.5cm 2.5cm 3cm; 
            size: A4 portrait; 
        }

        /* Kop Surat */
        .kop-surat { 
            text-align: center; 
            border-bottom: 3px double #000; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }
        .kop-surat h1 { font-size: 16pt; font-weight: bold; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        .kop-surat h2 { font-size: 14pt; font-weight: bold; margin: 5px 0; text-transform: uppercase; }
        .kop-surat p { font-size: 10pt; margin: 2px 0; }

        /* Judul Surat */
        .judul-surat { text-align: center; margin: 20px 0 10px 0; }
        .judul-surat h3 { font-size: 14pt; font-weight: bold; text-decoration: underline; margin: 0; }
        .nomor-surat { text-align: center; margin-bottom: 20px; font-size: 12pt; }

        /* Tabel Informasi */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 4px 0; vertical-align: top; }
        .info-table td.label { width: 140px; font-weight: normal; }
        .info-table td.titik { width: 15px; text-align: center; }
        .info-table td.isi { width: auto; }

        /* Isi Surat */
        .isi-surat { text-align: justify; margin-bottom: 20px; }
        
        /* Tanda Tangan */
        .ttd-block { 
            float: right; 
            width: 250px; 
            text-align: center; 
            margin-top: 20px; 
        }
        .ttd-block .jabatan { margin-bottom: 60px; font-weight: bold; }
        .ttd-block .nama { font-weight: bold; text-decoration: underline; margin-top: 5px; }
        .ttd-img { max-width: 120px; height: auto; margin-bottom: 5px; }
        
        /* QR Code */
        .qr-block { clear: both; text-align: right; margin-top: 10px; font-size: 9pt; color: #555; }
        .qr-img { max-width: 70px; height: auto; }

        /* Tembusan */
        .tembusan { clear: both; margin-top: 40px; font-size: 11pt; }
        .tembusan h4 { font-size: 11pt; font-weight: bold; margin: 0 0 5px 0; text-decoration: underline; }
        .tembusan ol { margin: 0; padding-left: 20px; }
    </style>
</head>
<body>

    <!-- 1. KOP SURAT -->
    <div class="kop-surat">
        <h1>PERUSAHAAN UMUM DAERAH AIR MINUM</h1>
        <h2>TIRTA MOEDAL KOTA SEMARANG</h2>
        <p>Jl. Siliwangi No. 131, Semarang 50131</p>
        <p>Telp. (024) 8441001 | Email: info@tirtamoedal.co.id</p>
    </div>

    <!-- 2. JUDUL & NOMOR SURAT -->
    <div class="judul-surat">
        <h3>UNDANGAN RAPAT</h3>
    </div>
    <div class="nomor-surat">
        Nomor: {{ $nomorSurat ?? '.................' }}
    </div>

    <!-- 3. PENERIMA -->
    <p style="margin-bottom: 10px;">Kepada Yth.<br><strong>{{ $data['penerima'] ?? 'Peserta Rapat' }}</strong><br>di Tempat</p>

    <!-- 4. ISI SURAT (Menggunakan Tabel untuk alignment presisi) -->
    <p>Dengan hormat,<br>Sehubungan dengan akan dilaksanakannya kegiatan perusahaan, maka kami mengundang Bapak/Ibu untuk menghadiri:</p>

    <table class="info-table">
        <tr>
            <td class="label">Hari / Tanggal</td>
            <td class="titik">:</td>
            <td class="isi">{{ \Carbon\Carbon::parse($data['tanggal'])->locale('id')->isoFormat('dddd, D MMMM Y') }}</td>
        </tr>
        <tr>
            <td class="label">Waktu</td>
            <td class="titik">:</td>
            <td class="isi">{{ $data['jam_mulai'] }} - {{ $data['jam_selesai'] }} WIB</td>
        </tr>
        <tr>
            <td class="label">Tempat</td>
            <td class="titik">:</td>
            <td class="isi">{{ $data['tempat'] ?? 'Ruang Rapat' }}</td>
        </tr>
        <tr>
            <td class="label">Acara</td>
            <td class="titik">:</td>
            <td class="isi">{{ $data['acara'] }}</td>
        </tr>
        <!-- BARIS BARU: DAFTAR PESERTA (Dengan white-space: pre-line agar enter tetap terbaca) -->
        <tr>
            <td class="label" style="vertical-align: top;">Peserta</td>
            <td class="titik" style="vertical-align: top;">:</td>
            <td class="isi" style="white-space: pre-line;">{{ $data['peserta'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Pimpinan Rapat</td>
            <td class="titik">:</td>
            <td class="isi">{{ $data['pimpinan'] ?? '-' }}</td>
        </tr>
    </table>

    <p class="isi-surat">Mengingat pentingnya acara tersebut, kami mohon kehadiran Bapak/Ibu tepat pada waktunya. Atas perhatian dan kehadirannya, kami ucapkan terima kasih.</p>

    <!-- 5. TANDA TANGAN DIGITAL -->
    <div class="ttd-block">
        <div>Semarang, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</div>
        <div class="jabatan">KEPALA BAGIAN SEKRETARIAT</div>
        
        @if(isset($submission) && $submission->approver)
            {{-- Jika ada gambar TTD di tabel signatories, tampilkan --}}
            @if($submission->approver->signature_image && \Storage::disk('public')->exists($submission->approver->signature_image))
                <img src="{{ public_path('storage/' . $submission->approver->signature_image) }}" class="ttd-img" alt="TTD">
            @endif
            <div class="nama">{{ $submission->approver->nama_lengkap ?? 'Nama Pejabat' }}</div>
            <div>NIP. {{ $submission->approver->nip ?? '-' }}</div> {{-- PERBAIKAN: NIP, bukan NPP --}}
        @else
            <div class="nama">( Nama Pejabat )</div>
            <div>NIP. -</div>
        @endif
    </div>

    <!-- 6. QR VERIFICATION -->
    @if(isset($qrCodeUrl) && $qrCodeUrl)
        <div class="qr-block">
            <img src="{{ public_path(str_replace('/storage/', 'storage/', $qrCodeUrl)) }}" class="qr-img" alt="QR Code">
            <br>Scan untuk verifikasi keaslian dokumen
        </div>
    @endif

    <!-- 7. TEMBUSAN -->
    <div class="tembusan">
        <h4>Tembusan:</h4>
        <ol>
            <li>Direktur Utama (Sebagai Laporan)</li>
            <li>Peserta Undangan</li>
            <li>Arsip.</li>
        </ol>
    </div>

</body>
</html>