<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Menggunakan firstOrCreate agar tidak error jika role sudah ada di database
        Role::firstOrCreate(['name' => 'IT Administrator', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Kepegawaian', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Sekretariat', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Pegawai', 'guard_name' => 'web']);
    }
}