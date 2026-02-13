<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SystemHealthController;
use Livewire\Volt\Volt;



// Livewire Components
use App\Livewire\Marketing\{MarketingDashboard, OrderList, OrderForm, EditOrder};
use App\Livewire\Operator\{
    Logbook, KnittingForm, DyeingForm, StenterForm, CompactorForm, 
    TumblerForm, HeatSettingForm, RelaxDryerForm, FleeceForm, PengujianForm, QEForm
};
use App\Livewire\Admin\{UserManagement, DivisionManagement, Monitoring};

/*
|--------------------------------------------------------------------------
| Public & Redirection Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    // Menggunakan Blade welcome (Bukan Inertia)
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Semua User)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    
    // Dashboard Utama (Disesuaikan berdasarkan Role di Controller)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/monitoring', [DashboardController::class, 'monitoring'])->name('monitoring.dashboard');
    Route::get('/admin/monitoring', Monitoring::class)->name('admin.monitoring');

    // Profile Management (Blade Version)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // API Helpers (Pencarian SAP & QR System)
    Route::prefix('api')->group(function () {
        Route::get('/check-sap/{sap_no}', function ($sap_no) {
            $exists = \App\Models\MarketingOrder::where('sap_no', $sap_no)->exists();
            return response()->json(['exists' => $exists]);
        })->name('api.check-sap');
        
        Route::get('/order-detail/{sap_no}', [DashboardController::class, 'getOrderDetailApi'])->name('api.order-detail');
        Route::get('/qrcode/{sap_no}', [DashboardController::class, 'generateQrCode'])->name('api.qrcode');
        Route::get('/label/{sap_no}', [DashboardController::class, 'generateLabel'])->name('api.label');
    });
});

/*
|--------------------------------------------------------------------------
| Marketing Section (Livewire)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:marketing'])->prefix('marketing')->group(function () {
    Route::get('/dashboard', \App\Livewire\Marketing\MarketingDashboard::class)
    ->name('dashboard');
    Route::get('/orders', OrderList::class)->name('marketing.orders.index');
    Route::get('/orders/create', OrderForm::class)->name('marketing.orders.create');
    Route::get('/orders/{id}/edit', EditOrder::class)->name('marketing.orders.edit');
    Route::get('/export-excel', [DashboardController::class, 'exportExcel'])->name('marketing.orders.export');
});

/*
|--------------------------------------------------------------------------
| Operator Section (Livewire)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:operator'])->prefix('operator')->group(function () {
    Route::get('/logbook', Logbook::class)->name('operator.logbook');
    
    // Form Produksi Spesifik
    Route::get('/knitting', KnittingForm::class)->name('operator.knitting');
    Route::get('/dyeing', DyeingForm::class)->name('operator.dyeing');
    Route::get('/stenter', StenterForm::class)->name('operator.stenter');
    Route::get('/compactor', CompactorForm::class)->name('operator.compactor');
    Route::get('/tumbler', TumblerForm::class)->name('operator.tumbler');
    Route::get('/heat-setting', HeatSettingForm::class)->name('operator.heatsetting');
    Route::get('/relax-dryer', RelaxDryerForm::class)->name('operator.relaxdryer');
    Route::get('/fleece', FleeceForm::class)->name('operator.fleece');
    Route::get('/pengujian', PengujianForm::class)->name('operator.pengujian');
    Route::get('/qe', QEForm::class)->name('operator.qe');

    // Pilihan Divisi (Jika masih diperlukan)
    Route::get('/divisions', [ProductionController::class, 'index'])->name('operator.divisions');
});

/*
|--------------------------------------------------------------------------
| Admin Section (Master Data & System)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    // Master Data (Livewire)
    Route::get('/users', UserManagement::class)->name('admin.users');
    Route::get('/divisions', DivisionManagement::class)->name('admin.divisions');
    
    // System Utilities (Controller)
    Route::get('/backup-db', [BackupController::class, 'download'])->name('admin.backup');
    Route::get('/system-health', [SystemHealthController::class, 'index'])->name('admin.health');
});


// Route::get('/admin/users', UserManagement::class)
//     ->middleware(['auth', 'role:super_admin'])
//     ->name('admin.users');

require __DIR__.'/auth.php';