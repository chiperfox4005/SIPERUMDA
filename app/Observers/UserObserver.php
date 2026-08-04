<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        $this->syncRoleBasedOnDepartment($user);
    }

    public function updated(User $user): void
    {
        // Trigger update role hanya jika bagian atau sub bagian berubah
        if ($user->isDirty('bagian_id') || $user->isDirty('sub_bagian_id')) {
            $this->syncRoleBasedOnDepartment($user);
        }
    }

    private function syncRoleBasedOnDepartment(User $user): void
    {
        // Load relasi dengan fresh data
        $user->load(['bagian', 'subBagian']);
        
        if (!$user->bagian) {
            return;
        }

        // Gunakan trim() dan strtolower() agar aman dari spasi tersembunyi
        $namaBagian = strtolower(trim($user->bagian->nama_bagian));
        $namaSubBagian = $user->subBagian ? strtolower(trim($user->subBagian->nama_sub_bagian)) : '';

        // 1. CEK: Bagian Kepegawaian (Berikan Dual Role: Kepegawaian & Pegawai)
        if (str_contains($namaBagian, 'kepegawaian')) {
            $user->syncRoles(['Kepegawaian', 'Pegawai']);
            return;
        }

        // 2. CEK: Bagian Sekretariat
        if (str_contains($namaBagian, 'sekretariat')) {
            $user->syncRoles(['Sekretariat', 'Pegawai']);
            return;
        }

        // 3. CEK: PTI (Litbang + Pengembangan Teknologi Informatika)
        if (str_contains($namaBagian, 'litbang') && str_contains($namaSubBagian, 'pengembangan teknologi informatika')) {
            // Sesuaikan 'IT Administrator' dengan nama role asli di database Anda (bisa jadi 'PTI')
            $user->syncRoles(['IT Administrator', 'Pegawai']); 
            return;
        }

        // 4. DEFAULT: Selain kondisi di atas, hanya role 'Pegawai'
        $user->syncRoles(['Pegawai']);
    }
}