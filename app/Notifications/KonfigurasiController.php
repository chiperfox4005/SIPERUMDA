<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class KonfigurasiController extends Controller
{
    public function __construct()
    {
        // Pastikan hanya Administrator yang bisa akses
        $this->middleware(function ($request, $next) {
            abort_unless(auth()->user()->hasRole('Administrator'), 403, 'Akses ditolak. Hanya Administrator.');
            return $next($request);
        });
    }

    /**
     * Menampilkan halaman Audit Logs
     */
    public function auditLogs()
    {
        $logs = AuditLog::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.audit-logs', compact('logs'));
    }

    /**
     * Menampilkan halaman Backup
     */
    public function backupPage()
    {
        return view('admin.backup');
    }

    /**
     * Proses Backup Database dan Download
     */
    public function backup(Request $request)
    {
        try {
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $fileName = "backup_db_{$timestamp}.sql";
            $backupPath = storage_path("app/public/backups/{$fileName}");

            // Pastikan folder backup ada
            if (!File::exists(storage_path('app/public/backups'))) {
                File::makeDirectory(storage_path('app/public/backups'), 0755, true);
            }

            // Konfigurasi Database dari .env
            $dbHost = env('DB_HOST', '127.0.0.1');
            $dbPort = env('DB_PORT', '3306');
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');

            // Perintah mysqldump (Kompatibel dengan Laragon/Windows)
            // Jika menggunakan XAMPP/Laragon, path mysqldump biasanya sudah di environment variable
            $command = "mysqldump --user={$dbUser} --password={$dbPass} --host={$dbHost} --port={$dbPort} {$dbName} > {$backupPath}";
            
            // Eksekusi perintah
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            if ($returnVar !== 0 || !File::exists($backupPath)) {
                // Fallback: Jika exec disabled, gunakan Artisan (memerlukan package spatie/laravel-backup)
                // Atau beri tahu user untuk mengaktifkan exec di php.ini
                return back()->with('error', 'Gagal membuat backup. Pastikan fungsi exec() aktif di php.ini atau gunakan package spatie/laravel-backup.');
            }

            // Catat aktivitas backup
            AuditLog::create([
                'user_id' => auth()->user()->nip ?? auth()->user()->id,
                'user_name' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                'action' => 'backup_database',
                'model_type' => 'System',
                'model_id' => 0,
                'ip_address' => request()->ip(),
            ]);

            // Download file
            return response()->download($backupPath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat backup: ' . $e->getMessage());
        }
    }
}