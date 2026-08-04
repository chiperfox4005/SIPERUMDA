<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Panggil seeder-seeder di sini
        $this->call([
            RoleSeeder::class,
            BagianSeeder::class, // <--- Tambahkan baris ini
        ]);

        // Data User Default
        $admin = User::firstOrCreate(
            ['nip' => '199001012020011001'],
            ['nama_lengkap' => 'Administrator Sistem', 'password' => Hash::make('password'), 'status' => 'aktif']
        );
        $admin->syncRoles(['IT Administrator']);

        $sekretaris = User::firstOrCreate(
            ['nip' => '199002022020012002'],
            ['nama_lengkap' => 'Sekretaris Umum', 'password' => Hash::make('password'), 'status' => 'aktif']
        );
        $sekretaris->syncRoles(['Sekretariat']);

        $kepegawaian = User::firstOrCreate(
            ['nip' => '199003032020013003'],
            ['nama_lengkap' => 'Staf Kepegawaian', 'password' => Hash::make('password'), 'status' => 'aktif']
        );
        $kepegawaian->syncRoles(['Kepegawaian']);

        $pegawai = User::firstOrCreate(
            ['nip' => '199505052020014004'],
            ['nama_lengkap' => 'Pegawai Contoh', 'password' => Hash::make('password'), 'status' => 'aktif']
        );
        $pegawai->syncRoles(['Pegawai']);
    }
}