<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\ActivityLog;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ROBOT PEMBERSIH OTOMATIS (Malam hari jam 01:00)
// Menghapus log yang sudah berumur lebih dari 1 tahun agar server tetap ringan
Schedule::call(function () {
    ActivityLog::where('created_at', '<', now()->subYear())->delete();
})->dailyAt('01:00');
