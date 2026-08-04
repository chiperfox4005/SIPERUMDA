<?php

namespace Database\Seeders;

use App\Models\Ruangan;
use Illuminate\Database\Seeder;

class RuanganSeeder extends Seeder
{
    public function run(): void
    {
        $ruangans = [
            [
                'nama_ruangan' => 'Ruang Rapat Kecil',
                'kode_ruangan' => 'OR.K',
                'kategori' => 'OR.K',
                'kapasitas' => 10,
                'fasilitas' => 'AC, Proyektor, Whiteboard',
                'status' => 'aktif',
                'memerlukan_surat' => false,
            ],
            [
                'nama_ruangan' => 'Ruang Rapat Besar',
                'kode_ruangan' => 'OR.B',
                'kategori' => 'OR.B',
                'kapasitas' => 30,
                'fasilitas' => 'AC, Proyektor, Sound System, Whiteboard',
                'status' => 'aktif',
                'memerlukan_surat' => false,
            ],
            [
                'nama_ruangan' => 'Ruang Rapat Transmisi dan Distribusi',
                'kode_ruangan' => 'Trandis',
                'kategori' => 'Trandis',
                'kapasitas' => 20,
                'fasilitas' => 'AC, Proyektor, Video Conference',
                'status' => 'aktif',
                'memerlukan_surat' => false,
            ],
            [
                'nama_ruangan' => 'Joglo',
                'kode_ruangan' => 'Joglo',
                'kategori' => 'Joglo',
                'kapasitas' => 50,
                'fasilitas' => 'Tradisional, Sound System, Tenda',
                'status' => 'aktif',
                'memerlukan_surat' => true, // Joglo wajib surat
            ],
        ];

        foreach ($ruangans as $ruangan) {
            Ruangan::updateOrCreate(
                ['kode_ruangan' => $ruangan['kode_ruangan']], // Cek berdasarkan kode agar tidak duplikat
                $ruangan
            );
        }

        $this->command->info('Data ruangan berhasil diisi!');
    }
}