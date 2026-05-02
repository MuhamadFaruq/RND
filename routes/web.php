<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SystemHealthController;
use Livewire\Volt\Volt;
use App\Http\Controllers\ProductionReportController;

// Livewire Components (Class-based)
use App\Livewire\Marketing\{MarketingDashboard, OrderList, OrderForm, EditOrder};
use App\Livewire\Operator\{
    KnittingForm, DyeingForm, StenterForm, CompactorForm,
    TumblerForm, FinishingForm, RelaxDryerForm, FleeceForm, PengujianForm, QEForm
};
use App\Livewire\Admin\{UserManagement, DivisionManagement};

/*
|--------------------------------------------------------------------------
| Public & Redirection Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| Public & Guest Routes
|--------------------------------------------------------------------------
*/
// Halaman Welcome HANYA untuk tamu. Middleware 'guest' otomatis menolak user yang sudah login.
Route::get('/', function () {
    return view('welcome');
})->middleware('guest')->name('welcome');

/*
|--------------------------------------------------------------------------
| Authenticated Dashboard Traffic Controller
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'no-back'])->group(function () {
    
    Route::get('/dashboard', function () {
        $role = auth()->user()->role;

        // PERBAIKAN: Gunakan view('dashboard') agar Layout Utama & Tailwind termuat
        if (in_array($role, ['super-admin', 'admin'])) {
            return view('dashboard'); 
        }

        if ($role === 'marketing') {
            return redirect()->route('marketing.dashboard');
        }

        return redirect()->route('operator.logbook'); 

    })->name('dashboard');

});

Route::middleware('auth')->group(function () {
    
    Route::get('/monitoring', [DashboardController::class, 'monitoring'])->name('monitoring.dashboard');

    // PERBAIKAN: Jika Admin Monitoring menggunakan Volt, gunakan Volt::route
    // Jika menggunakan Class, pastikan file App\Livewire\Admin\Monitoring ada.
    // Sementara saya arahkan ke Volt agar tidak error.
    Volt::route('/admin/monitoring', 'admin.monitoring')->name('admin.monitoring');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('api')->group(function () {
        Route::get('/check-sap/{sap_no}', function ($sap_no) {
            $exists = \App\Models\MarketingOrder::where('sap_no', $sap_no)->exists();
            return response()->json(['exists' => $exists]);
        })->name('api.check-sap');
        
        Route::get('/order-detail/{sap_no}', [DashboardController::class, 'getOrderDetailApi'])->name('api.order-detail');
        Route::get('/qrcode/{sap_no}', [DashboardController::class, 'generateQrCode'])->name('api.qrcode');
        Route::get('/label/{sap_no}', [DashboardController::class, 'generateLabel'])->name('api.label');
        
        // Maintenance Heartbeat (Sangat penting untuk Otomatisasi Logout)
        Route::get('/check-maintenance', [DashboardController::class, 'checkMaintenanceStatus'])->name('api.maintenance-check');
    });
});

/*
|--------------------------------------------------------------------------
| Marketing Section (Livewire Class-based)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:marketing'])->prefix('marketing')->group(function () {
    Route::get('/dashboard', MarketingDashboard::class)->name('marketing.dashboard');
    Route::get('/orders', OrderList::class)->name('marketing.orders.index');
    Route::get('/orders/create', OrderForm::class)->name('marketing.orders.create');
    Route::get('/orders/{id}/edit', EditOrder::class)->name('marketing.orders.edit');
    Route::get('/export-excel', [DashboardController::class, 'exportExcel'])->name('marketing.orders.export');
});

/*
|--------------------------------------------------------------------------
| Operator Section (Volt Component)
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| Operator Section (Volt Component)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:operator,knitting,dyeing,relax-dryer,finishing,stenter,tumbler,fleece,pengujian,qe'])
    ->prefix('operator')
    ->group(function () {
    
    // 1. DASHBOARD UTAMA (Hapus duplikasi di sini)
    Volt::route('/logbook', 'operator.logbook')->name('operator.logbook');
    Volt::route('/divisions', 'operator.logbook')->name('operator.divisions');
    
    // 2. HALAMAN EKSEKUSI (Volt - Membawa Parameter SAP)
    Volt::route('/dyeing/exec/{sap}', 'operator.dyeing-form')->name('operator.dyeing.exec');
    Volt::route('/relax-dryer/exec/{sap}', 'operator.relax-dryer-form')->name('operator.relax-dryer.exec');
    Volt::route('/finishing/exec/{sap}', 'operator.finishing-form')->name('operator.finishing.exec');
    Volt::route('/stenter/exec/{sap}', 'operator.stenter-form')->name('operator.stenter.exec');
    Volt::route('/tumbler/exec/{sap}', 'operator.tumbler-form')->name('operator.tumbler.exec');
    Volt::route('/fleece/exec/{sap}', 'operator.fleece-form')->name('operator.fleece.exec');
    Volt::route('/pengujian/exec/{sap}', 'operator.pengujian-form')->name('operator.pengujian.exec');
    Volt::route('/qe/exec/{sap}', 'operator.qe-form')->name('operator.qe.exec');

    // 3. FORM PRODUKSI (Class-based) - TAMBAHKAN {sap} PADA SETIAP ROUTE
    Route::get('/knitting/{sap}', KnittingForm::class)->name('operator.knitting');
    Route::get('/dyeing/{sap}', DyeingForm::class)->name('operator.dyeing');
    Route::get('/stenter/{sap}', StenterForm::class)->name('operator.stenter');
    Route::get('/compactor/{sap}', CompactorForm::class)->name('operator.compactor');
    Route::get('/tumbler/{sap}', TumblerForm::class)->name('operator.tumbler');
    Route::get('/finishing/{sap}', FinishingForm::class)->name('operator.finishing');
    Route::get('/relax-dryer/{sap}', RelaxDryerForm::class)->name('operator.relax-dryer');
    Route::get('/fleece/{sap}', FleeceForm::class)->name('operator.fleece');
    Route::get('/pengujian/{sap}', PengujianForm::class )->name('operator.pengujian');
    Route::get('/qe/{sap}', QEForm::class)->name('operator.qe');
});

/*
|--------------------------------------------------------------------------
| Admin & Super Admin Section
|--------------------------------------------------------------------------
*/
// Grup 1: Akses untuk Admin DAN Super-Admin
Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Monitoring & Audit (Read-only untuk Admin)
    Volt::route('/activity-logs', 'admin.activity-logs')->name('admin.activity-logs');
    Volt::route('/monitoring', 'admin.monitoring')->name('admin.monitoring');
    Volt::route('/unit-monitoring', 'admin.unit-monitoring')
    ->name('admin.unit-monitoring');
    Route::get('/export-report', [ProductionReportController::class, 'export'])
    ->name('admin.export');
    
    // System Health (Hanya memantau)
    Route::get('/system-health', [SystemHealthController::class, 'index'])->name('admin.health');
    
    // Export Data
    Route::get('/export-excel', [DashboardController::class, 'exportExcel'])->name('marketing.orders.export');
});

// Grup 2: KHUSUS Super-Admin (Akses Penuh / CRUD)
Route::middleware(['auth', 'role:super-admin'])->prefix('super-admin')->group(function () {
    Volt::route('/config', 'admin.system-config')->name('admin.config');
    Route::get('/users', UserManagement::class)->name('admin.users');
    Route::get('/divisions', DivisionManagement::class)->name('admin.divisions');
    Route::get('/backup-db', [BackupController::class, 'download'])->name('admin.backup');
    Route::get('/impersonate/{user}', function (\App\Models\User $user) {
        session(['impersonator_id' => auth()->id()]); // Simpan ID Super-Admin di session
        auth()->login($user); // Login sebagai user target
        return redirect()->route('dashboard'); // Arahkan ke dashboard operator
    })->name('admin.impersonate');
});

Route::get('/stop-impersonating', function () {
    $adminId = session('impersonator_id');
    if ($adminId) {
        auth()->loginUsingId($adminId); // Login kembali ke akun asli
        session()->forget('impersonator_id');
    }
    return redirect()->route('admin.users');
})->name('admin.stop-impersonate');

require __DIR__.'/auth.php';