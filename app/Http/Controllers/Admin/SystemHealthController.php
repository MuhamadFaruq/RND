<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class SystemHealthController extends Controller
{
    public function index()
    {
        // Mendapatkan sisa ruang penyimpanan dalam Bytes
        $freeSpace = disk_free_space(base_path());
        $totalSpace = disk_total_space(base_path());
        $usedSpace = $totalSpace - $freeSpace;

        return Inertia::render('Admin/SystemHealth', [
            'storage' => [
                'free' => round($freeSpace / (1024 * 1024 * 1024), 2), // GB
                'used' => round($usedSpace / (1024 * 1024 * 1024), 2), // GB
                'total' => round($totalSpace / (1024 * 1024 * 1024), 2), // GB
                'percentage' => round(($usedSpace / $totalSpace) * 100, 1),
            ],
            'php_version' => PHP_VERSION,
            'laravel_version' => \Illuminate\Foundation\Application::VERSION,
            'server_time' => now()->toDateTimeString(),
        ]);
    }
}