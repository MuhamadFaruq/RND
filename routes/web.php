<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Middleware\CheckDivision;
use App\Http\Controllers\Admin\DivisionController;

use App\Livewire\Marketing\OrderList;
use App\Livewire\Marketing\OrderForm;
use App\Livewire\Marketing\EditOrder;
use App\Livewire\Marketing\MarketingDashboard;
use App\Livewire\Operator\Logbook;
use App\Livewire\Operator\KnittingForm;
use App\Livewire\Operator\DyeingForm;
use App\Livewire\Operator\StenterForm;
use App\Livewire\Operator\CompactorForm;
use App\Livewire\Operator\TumblerForm;
use App\Livewire\Operator\HeatSettingForm;
use App\Livewire\Operator\RelaxDryerForm;
use App\Livewire\Operator\FleeceForm;
use App\Livewire\Operator\PengujianForm;
use App\Livewire\Operator\QEForm;


Route::get('/operator/dyeing', DyeingForm::class)->name('operator.dyeing');
Route::get('/operator/stenter', StenterForm::class)->name('operator.stenter');
Route::get('/operator/compactor', CompactorForm::class)->name('operator.compactor');
Route::get('/operator/tumbler', TumblerForm::class)->name('operator.tumbler');
Route::get('/operator/heat-setting', HeatSettingForm::class)->name('operator.heatsetting');
Route::get('/operator/relax-dryer', RelaxDryerForm::class)->name('operator.relaxdryer');
Route::get('/operator/fleece', FleeceForm::class)->name('operator.fleece');
Route::get('/operator/pengujian', PengujianForm::class)->name('operator.pengujian');
Route::get('/operator/qe', QEForm::class)->name('operator.qe');

// --- REDIRECTION LOGIC ---
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->role === 'marketing') {
            return redirect()->route('monitoring.dashboard');
        }
        return redirect()->route('operator.divisions');
    }
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// --- AUTHENTICATED ROUTES ---
Route::middleware('auth')->group(function () {

    Route::get('/marketing/orders', OrderList::class)->name('marketing.orders.index');
    Route::get('/marketing/orders/create', OrderForm::class)->name('marketing.orders.create');
    Route::get('/marketing/orders/{id}/edit', EditOrder::class)->name('marketing.orders.edit');

    Route::get('/api/monitoring/realtime-stats', [DashboardController::class, 'getRealTimeStats'])
    ->name('api.monitoring.stats');
    
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- MONITORING & DASHBOARD ---
    // Dashboard diarahkan ke index (Summary)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard'); 

    // Monitoring diarahkan ke monitoring (Tampilan tabel SAP yang lengkap)
    Route::get('/monitoring', [DashboardController::class, 'monitoring'])->name('monitoring.dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'exportExcel'])->name('dashboard.export');

    // --- QR CODE & LABEL SYSTEM ---
    Route::get('/api/qrcode/{sap_no}', [DashboardController::class, 'generateQrCode'])->name('api.qrcode');
    Route::get('/api/label/{sap_no}', [DashboardController::class, 'generateLabel'])->name('api.label');
    Route::get('/api/order-detail/{sap_no}', [DashboardController::class, 'getOrderDetailApi'])->name('api.order-detail');

    // --- QUALITY EVALUATION ACTIONS (Approval/Reject) ---
    Route::post('/dashboard/qe-action/{id}', [DashboardController::class, 'handleQEAction'])->name('dashboard.qe-action');

    // --- OPERATOR SECTION ---
    // 1. Halaman Pilihan Divisi (Diarahkan ke ProductionController)
    Route::get('/operator/divisions', [ProductionController::class, 'index'])
        ->name('operator.divisions');
    // 2. Halaman Logbook (Diproteksi Middleware CheckDivision)
    Route::get('/operator/log/{division}', [ProductionController::class, 'create'])
        ->middleware(CheckDivision::class)
        ->name('log.create');
    // 3. Proses Simpan Logbook
    Route::post('/operator/log/store', [ProductionController::class, 'storeLog'])
        ->name('log.store');
    // API & Scanner (Tetap di DashboardController jika logikanya memang di sana)
    Route::get('/operator/scanner', [DashboardController::class, 'scannerPage'])->name('operator.scanner');

    Route::post('/production/logs', [ProductionController::class, 'storeLog'])->name('production.logs.store');

    // API Helpers for Production
    Route::get('/api/check-sap/{sap_no}', function ($sap_no) {
        $exists = \App\Models\MarketingOrder::where('sap_no', $sap_no)->exists();
        return response()->json(['exists' => $exists]);
    })->name('api.check-sap');

    Route::get('/api/marketing-orders/{sapNo}', [ProductionController::class, 'marketingOrderBySap'])
        ->whereNumber('sapNo')
        ->name('api.marketing-orders.by-sap');

    Route::get('/api/order-details/{sap}', [ProductionController::class, 'marketingOrderBySap'])
        ->whereNumber('sap')
        ->name('api.order-details');
});

// --- MARKETING SECTION (Role Restricted) ---
Route::middleware(['auth', 'role:marketing'])->group(function () {
    Route::get('/marketing/dashboard', MarketingDashboard::class)->name('marketing.dashboard');
});

Route::middleware(['auth', 'role:operator'])->group(function () {
    Route::get('/operator/logbook', Logbook::class)->name('operator.logbook');
    Route::get('/operator/knitting', KnittingForm::class)->name('operator.knitting');
});

Route::middleware(['auth', 'marketing'])->group(function () {
    Route::get('/marketing/home', [DashboardController::class, 'monitoring'])->name('marketing.home');
    Route::get('/marketing/orders', [DashboardController::class, 'marketingOrderList'])->name('marketing.orders.index');
    Route::get('/marketing/orders/create', function () { return Inertia::render('Marketing/CreateOrder'); })->name('marketing.orders.create');
    Route::get('/marketing/orders/{id}/edit', [DashboardController::class, 'editOrder'])->name('marketing.orders.edit');
    Route::put('/marketing/orders/{id}', [DashboardController::class, 'updateOrder'])->name('marketing.orders.update');
    Route::delete('/marketing/orders/{id}', [DashboardController::class, 'destroyOrder'])->name('marketing.orders.destroy');
    Route::post('/marketing/orders', [ProductionController::class, 'storeOrder'])->name('marketing.orders.store');
    Route::get('/marketing/export-excel', [DashboardController::class, 'exportExcel'])->name('marketing.orders.export');
});

// --- ADMIN / SUPERADMIN SECTION ---
Route::middleware(['auth', 'can:access-superadmin'])->prefix('admin')->group(function () {
    
    // Route Manajemen User menggunakan Resource atau Manual
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    
    // Backup & System Health
    Route::get('/backup-db', [BackupController::class, 'download'])->name('admin.backup');
    Route::get('/system-health', [SystemHealthController::class, 'index'])->name('admin.health');

    // Manajemen Divisi
    Route::post('/divisions', [DivisionController::class, 'store'])->name('divisions.store');
    Route::get('/divisions', [DivisionController::class, 'index'])->name('admin.divisions.index');
    Route::get('/admin/divisions', [DivisionController::class, 'index'])->name('admin.divisions.index');
    Route::delete('/divisions/{id}', [DivisionController::class, 'destroy'])->name('divisions.destroy');
     Route::put('/divisions/{id}', [DivisionController::class, 'update'])->name('admin.divisions.update'); // Tambahkan ini
    Route::delete('/divisions/{id}', [DivisionController::class, 'destroy'])->name('admin.divisions.destroy'); // Tambahkan ini
});

// Route::middleware(['auth', 'check.division'])->group(function () {
    // Route::get('/operator/log/{division}', [ProductionController::class, 'create'])->name('log.create');
    // Route::post('/production/logs', [ProductionController::class, 'store'])->name('log.store');
// Ubah grup route Anda menjadi seperti ini:
Route::middleware(['auth'])->group(function () {
    // Halaman Pilihan Divisi (Tanpa Middleware CheckDivision agar tidak loop)
    Route::get('/operator/divisions', [ProductionController::class, 'index'])->name('operator.divisions');

    // Halaman Logbook (Gunakan Middleware untuk mengunci akses)
    Route::get('/operator/log/{division}', [ProductionController::class, 'create'])
        ->middleware(CheckDivision::class)
        ->name('log.create');
});



require __DIR__.'/auth.php';