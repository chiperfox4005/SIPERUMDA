<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'nip' => '199001012020011001',
            'nama_lengkap' => 'Administrator Sistem',
            'password' => Hash::make('password'),
            'status' => 'aktif',
        ]);
        $admin->assignRole('IT Administrator');

        $sekretaris = User::create([
            'nip' => '199002022020012002',
            'nama_lengkap' => 'Sekretaris Umum',
            'password' => Hash::make('password'),
            'status' => 'aktif',
        ]);
        $sekretaris->assignRole('Sekretariat');
    }
}