<?php

namespace Database\Seeders;

use App\Models\Bagian;
use App\Models\SubBagian;
use Illuminate\Database\Seeder;

class BagianSeeder extends Seeder
{
    public function run(): void
    {
        // Data Struktur Organisasi
        $strukturOrganisasi = [
            'Bidang Litbang' => [
                'Sub Bidang Pengembangan Teknologi Informatika',
                'Sub Bidang Litbang Umum dan Keuangan',
                'Sub Bidang Litbang Teknik',
            ],
            'Bagian Sekretariat' => [
                'Sub Bagian Tata Usaha, Rumah Tangga dan Hukum',
                'Sub Bagian Humas dan Protokol',
                'Sub Bagian Keamanan dan Ketertiban',
            ],
            'Bagian Kepegawaian' => [
                'Sub Bagian Administrasi Kepegawaian',
                'Sub Bagian Kesejahteraan Pegawai',
                'Sub Bagian Pengembangan Karier',
            ],
            'Bagian Keuangan' => [
                'Sub Bagian Anggaran',
                'Sub Bagian Kas',
                'Sub Bagian Akuntansi',
            ],
            'Bagian Perlengkapan' => [
                'Sub Bagian Pengadaan',
                'Sub Bagian Persediaan',
                'Sub Bagian Pengelolaan Aset',
            ],
            'Bagian Perencanaan dan Evaluasi' => [
                'Sub Bagian Perencanaan Teknik',
                'Sub Bagian Pengendalian Konstruksi',
                'Sub Bagian Evaluasi Program',
            ],
            'Bagian Produksi I' => [
                'Sub Bagian IPA Air Permukaan I',
                'Sub Bagian Mata Air dan Air Bawah Tanah',
                'Sub Bagian Pengendalian Mutu Produksi I',
            ],
            'Bagian Produksi II' => [
                'Sub Bagian IPA Air Permukaan II',
                'Sub Bagian Air Baku dan Limbah',
                'Sub Bagian Pengendalian Mutu Produksi II',
            ],
            'Bagian Transmisi dan Distribusi' => [
                'Sub Bagian Transmisi dan Distribusi I',
                'Sub Bagian Transmisi dan Distribusi II',
                'Sub Bagian Pengaturan Aliran',
            ],
            'Bagian Peralatan dan Pemeliharaan' => [
                'Sub Bagian Pemeliharaan Bengkel dan Kendaraan',
                'Sub Bagian Meter Air, Mesin dan Elektrikal',
                'Sub Bagian Gedung dan Taman',
            ],
        ];

        // Looping untuk menyimpan data ke database
        foreach ($strukturOrganisasi as $namaBagian => $daftarSubBagian) {
            
            // Simpan Bagian (gunakan firstOrCreate agar aman jika dijalankan berulang)
            $bagian = Bagian::firstOrCreate(
                ['nama_bagian' => $namaBagian],
                ['kode_bagian' => strtoupper(str_replace(' ', '_', $namaBagian))]
            );

            // Simpan Sub Bagian terkait
            foreach ($daftarSubBagian as $namaSubBagian) {
                SubBagian::firstOrCreate(
                    [
                        'bagian_id' => $bagian->id,
                        'nama_sub_bagian' => $namaSubBagian
                    ]
                );
            }
        }
    }
}