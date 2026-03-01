<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Setting;
use App\Models\User;
use App\Models\Division;
use App\Models\ActivityLog;

class Dashboard extends Component
{
    public function render()
    {
        // 1. Ambil Kapasitas Mesin dari Config (Default 1000 jika belum diisi)
        $maxCapacity = Setting::where('key', 'max_capacity')->value('value') ?? 1000;
        
        // 2. Data Dummy untuk Output (Nanti diganti dengan query asli setelah Logbook siap)
        $currentOutput = 0; // Set ke 0 dulu agar aman

        // 3. Menghitung persentase untuk Progress Bar
        $percentage = ($maxCapacity > 0) ? ($currentOutput / $maxCapacity) * 100 : 0;

        // 4. Data nyata lainnya yang sudah ada di sistem Anda
        return view('livewire.admin.dashboard', [
            'maxCapacity'   => $maxCapacity,
            'currentOutput' => $currentOutput,
            'percentage'    => min($percentage, 100),
            'totalUser'     => User::count(),
            'totalDivision' => Division::count(),
            'totalLogs'     => ActivityLog::whereDate('created_at', now())->count(),
            'recentFeeds'   => ActivityLog::latest()->take(5)->get(),
        ]);
    }
}