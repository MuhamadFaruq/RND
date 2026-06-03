<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use App\Models\ActivityLog;

class BackupController extends Controller
{
    public function download()
    {
        if (auth()->user()->role !== 'super-admin') {
            abort(403, 'Akses ditolak: Hanya Super Admin yang dapat mengunduh backup.');
        }

        try {
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');
        
        $filename = "backup-duniatex-" . now()->format('Y-m-d-H-i-s') . ".sql";
        $path = storage_path('app/' . $filename);

        // Cari lokasi mysqldump secara otomatis
        $mysqldumpPath = is_executable('/usr/local/bin/mysqldump') 
            ? '/usr/local/bin/mysqldump' 
            : (is_executable('/Applications/MAMP/Library/bin/mysqldump') 
                ? '/Applications/MAMP/Library/bin/mysqldump' 
                : 'mysqldump');

        // Gunakan format --password= agar tidak ada prompt interaktif
        // Perhatikan: Tidak ada spasi antara --password= dan nilai passwordnya
        $command = escapeshellcmd($mysqldumpPath) . ' --user=' . escapeshellarg($dbUser) . ' --password=' . escapeshellarg($dbPass) . ' ' . escapeshellarg($dbName) . ' > ' . escapeshellarg($path) . ' 2>&1';

        exec($command, $output, $resultCode);

        if ($resultCode !== 0) {
            // Jika gagal, log errornya agar Anda tahu penyebab pastinya
            \Log::error("Backup Gagal: " . implode("\n", $output));
            return back()->with('error', 'Gagal backup: ' . (isset($output[0]) ? $output[0] : 'Unknown Error'));
        }

        // Catat ke Activity Log
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DATABASE_BACKUP',
            'model' => 'SYSTEM',
            'description' => "Super Admin mengunduh database: $filename",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        if (file_exists($path)) {
            return response()->download($path)->deleteFileAfterSend(true);
        }

        return back()->with('error', 'File tidak tercipta di server.');

    } catch (\Exception $e) {
        return back()->with('error', 'Error: ' . $e->getMessage());
    }
}
}