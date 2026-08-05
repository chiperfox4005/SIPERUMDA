<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Undangan Rapat - {{ $submission->nomor_surat ?? 'Draft' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.4; color: #000; }
        @page { margin: 2cm 2cm 2cm 2cm; size: A4 portrait; }
        
        .kop-surat { text-align: left; margin-bottom: 15px; border-bottom: 3px double #000; padding-bottom: 10px; }
        .kop-surat h1 { font-size: 14pt; font-weight: bold; text-transform: uppercase; margin: 0; }
        .kop-surat h2 { font-size: 12pt; font-weight: bold; text-transform: uppercase; margin: 2px 0 0 0; }
        
        .judul { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; margin: 15px 0 5px 0; }
        .nomor-surat { text-align: center; font-size: 11pt; margin-bottom: 20px; }
        
        .main-table { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 15px; }
        .main-table td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
        .label-col { width: 160px; font-weight: bold; }
        .separator { width: 20px; text-align: center; }
        
        /* List standar HTML yang stabil di DOMPDF */
        .peserta-list { margin: 0; padding-left: 20px; list-style-type: decimal; }
        .peserta-list li { margin-bottom: 4px; }
        
        /* Wrapper TTD dipaksa ke kanan */
        .ttd-wrapper { width: 100%; text-align: right; margin-top: 30px; }
        .ttd-box { display: inline-block; text-align: center; width: 250px; }
        .kota-tanggal { margin-bottom: 60px; }
        .jabatan { line-height: 1.6; margin-bottom: 70px; }
        .ttd-img { max-width: 150px; height: auto; margin-bottom: 5px; }
        .nama { font-weight: bold; text-decoration: underline; margin-top: 5px; }
        .nip { font-size: 11pt; }
        
        .tembusan { margin-top: 40px; clear: both; }
        .tembusan h4 { font-size: 11pt; margin-bottom: 5px; font-weight: bold; text-decoration: underline; }
        .tembusan ol { margin: 0; padding-left: 20px; list-style-type: decimal; }
        .tembusan li { margin: 3px 0; }
    </style>
</head>
<body>

    <div class="kop-surat">
        <h1>PERUSAHAAN UMUM DAERAH AIR MINUM</h1>
        <h2>TIRTA MOEDAL KOTA SEMARANG</h2>
    </div>

    <div class="judul">UNDANGAN RAPAT</div>
    <div class="nomor-surat">Nomor : {{ $submission->nomor_surat ?? '..... /' }}</div>

    <table class="main-table">
        <tr>
            <td class="label-col">Hari / Tanggal</td>
            <td class="separator">:</td>
            <td>
                @php
                    // translatedFormat lebih stabil di DOMPDF daripada isoFormat
                    $tglRapat = isset($data['tanggal']) ? \Carbon\Carbon::parse($data['tanggal'])->locale('id')->translatedFormat('l, d F Y') : '-';
                @endphp
                {{ $tglRapat }}
            </td>
        </tr>
        <tr>
            <td class="label-col">Tempat</td>
            <td class="separator">:</td>
            <td>{{ $data['tempat'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Jam</td>
            <td class="separator">:</td>
            <td>{{ $data['jam_mulai'] ?? '-' }} - {{ $data['jam_selesai'] ?? '-' }} WIB</td>
        </tr>
        <tr>
            <td class="label-col">Acara</td>
            <td class="separator">:</td>
            <td>{{ $data['acara'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Pimpinan Rapat</td>
            <td class="separator">:</td>
            <td>{{ $data['pimpinan'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Dimohon hadir</td>
            <td class="separator">:</td>
            <td>
                @if(!empty($data['peserta']))
                    <ol class="peserta-list">
                        @foreach(explode("\n", trim($data['peserta'])) as $peserta)
                            @if(trim($peserta))
                                <li>{{ trim($peserta) }}</li>
                            @endif
                        @endforeach
                    </ol>
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td class="label-col">Inisiator</td>
            <td class="separator">:</td>
            <td>{{ $data['inisiator'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Notulen</td>
            <td class="separator">:</td>
            <td>{{ $data['notulen'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Catatan</td>
            <td class="separator">:</td>
            <td>
                @if(!empty($data['catatan']))
                    @foreach(explode("\n", trim($data['catatan'])) as $index => $catatan)
                        @if(trim($catatan))
                            {{ $index + 1 }}. {{ trim($catatan) }}<br>
                        @endif
                    @endforeach
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="3" style="border-left: none; border-right: none; border-bottom: none; padding: 15px 5px 5px 5px; font-style: italic;">
                Demikian atas perhatian dan kehadirannya diucapkan terima kasih.
            </td>
        </tr>
    </table>

    <!-- TTD dipaksa ke kanan menggunakan text-align: right pada wrapper -->
    <div class="ttd-wrapper">
        <div class="ttd-box">
            <div class="kota-tanggal">
                Semarang, {{ $tanggalCetak }}
            </div>
            <div class="jabatan">
                An. Direksi Perusahaan Umum Daerah Air Minum<br>
                Tirta Moedal Kota Semarang<br>
                Direktur Umum<br>
                u.b<br>
                <strong>Kepala Bagian Sekretariat</strong>
            </div>
            
            @if($signatory && !empty($signatory->signature_image))
                @php
                    $ttdPath = public_path('storage/' . $signatory->signature_image);
                @endphp
                @if(file_exists($ttdPath))
                    <img src="{{ $ttdPath }}" class="ttd-img" alt="TTD">
                @else
                    <div style="height: 60px; margin-bottom: 5px; color: #999; font-size: 9pt;">[File TTD tidak ditemukan]</div>
                @endif
            @else
                <div style="height: 60px; margin-bottom: 5px;"></div>
            @endif
            
            <div class="nama">{{ $signatory->name ?? 'HENDRAWAN DJATMIKO, SH' }}</div>
            <div class="nip">
                {{ $signatory->position ?? 'Staf Madya' }}<br>
                NPP. {{ $signatory->nip ?? '690830401' }}
            </div>
        </div>
    </div>

    <div class="tembusan">
        <h4>Tembusan:</h4>
        <ol>
            <li>Direktur Utama (Sebagai Laporan)</li>
            <li>Pertinggal.</li>
        </ol>
    </div>

</body>
</html>