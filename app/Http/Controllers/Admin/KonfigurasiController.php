<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class KonfigurasiController extends Controller
{
    /**
     * Menampilkan halaman Audit Log
     */
    public function auditLogs()
    {
        $logs = AuditLog::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.audit-logs', compact('logs'));
    }

    /**
     * Melakukan Backup Database
     */
    public function backup(Request $request)
    {
        try {
            // 1. Tentukan nama file dan lokasi backup
            $filename = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
            $backupPath = storage_path('app/backups/' . $filename);

            // 2. Pastikan direktori backup ada
            if (!File::exists(storage_path('app/backups'))) {
                File::makeDirectory(storage_path('app/backups'), 0755, true);
            }

            // 3. Ambil konfigurasi database dari file .env
            $dbHost = env('DB_HOST', '127.0.0.1');
            $dbPort = env('DB_PORT', '3306');
            $dbUser = env('DB_USERNAME', 'root');
            $dbPass = env('DB_PASSWORD', '');
            $dbName = env('DB_DATABASE', 'simaruda');

            // 4. Susun perintah mysqldump (aman untuk Windows/Laragon)
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s',
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbName),
                escapeshellarg($backupPath)
            );

            // 5. Eksekusi perintah
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            // 6. Validasi hasil backup
            if ($returnVar === 0 && File::exists($backupPath) && File::size($backupPath) > 0) {
                // Catat aktivitas ke Audit Log
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'Backup Database',
                    'description' => 'Melakukan backup database manual: ' . $filename,
                    'ip_address' => $request->ip(),
                ]);

                return redirect()->back()->with('success', 'Backup database berhasil dibuat dan disimpan di storage/app/backups/');
            } else {
                return redirect()->back()->with('error', 'Gagal melakukan backup. Pastikan mysqldump sudah tersedia di PATH sistem (Laragon biasanya sudah include).');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}