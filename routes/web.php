<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductionReportController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Livewire\Marketing\MarketingDashboard;
use App\Livewire\Marketing\OrderList;
use App\Livewire\Marketing\OrderForm;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Operator\KnittingForm;
use App\Livewire\Operator\SingleOperatorForm;
use App\Livewire\Operator\PengujianForm;
use App\Livewire\Operator\QEForm;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

// --- MARKETING ROUTES ---
Route::middleware(['auth', 'verified'])->prefix('marketing')->group(function () {
    Route::get('/dashboard', MarketingDashboard::class)->name('marketing.dashboard');
    Route::get('/orders', OrderList::class)->name('marketing.orders.index');
    Route::get('/orders/create', OrderForm::class)->name('marketing.orders.create');
    Route::get('/orders/{id}/edit', OrderForm::class)->name('marketing.orders.edit');
});

// --- ADMIN / MONITORING ROUTES ---
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    Volt::route('/dashboard', 'admin.admin-dashboard')->name('admin.dashboard');
    
    // Monitoring (Global & Unit)
    Volt::route('/monitoring', 'admin.monitoring')->name('admin.monitoring');
    Volt::route('/unit-monitoring', 'admin.unit-monitoring')->name('admin.unit-monitoring');
    
    // User Management
    Route::get('/users', UserManagement::class)->name('admin.users');
    
    // Impersonation (Super-Admin only)
    Route::get('/impersonate/{id}', [DashboardController::class, 'impersonate'])
        ->middleware('can:is-super-admin')
        ->name('admin.impersonate');

    Route::get('/stop-impersonate', [DashboardController::class, 'stopImpersonate'])->name('admin.stop-impersonate');

    // Activity Logs
    Volt::route('/logs', 'admin.activity-logs')->name('admin.logs');
    Volt::route('/activity-logs', 'admin.activity-logs')->name('admin.activity-logs');

    // Report Export (CRITICAL)
    Route::get('/export', [ProductionReportController::class, 'export'])->name('admin.export');
    
    // Administrative Sections (RESTORED)
    Route::get('/divisions', \App\Livewire\Admin\DivisionManagement::class)->name('admin.divisions');
    Volt::route('/config', 'admin.system-config')->name('admin.config');

    // System Utilities (RESTORED)
    Route::get('/backup-db', [BackupController::class, 'download'])
        ->middleware('can:is-super-admin')
        ->name('admin.backup');
    Route::get('/system-health', [SystemHealthController::class, 'index'])->name('admin.health');
});

// --- OPERATOR ROUTES ---
Route::middleware(['auth', 'verified'])->prefix('operator')
    ->group(function () {
    
    // 1. DASHBOARD UTAMA
    Volt::route('/logbook', 'operator.logbook')->name('operator.logbook');
    Volt::route('/divisions', 'operator.logbook')->name('operator.divisions');

    // 2. FORM PRODUKSI (Class-based)
    Route::get('/knitting/{artikel?}', KnittingForm::class)->name('operator.knitting');
    Route::get('/pengujian/{artikel?}', PengujianForm::class )->name('operator.pengujian');
    Route::get('/qe/{artikel?}', QEForm::class)->name('operator.qe');
    Route::get('/single-operator/{artikel?}', SingleOperatorForm::class)->name('operator.single');
});

// --- API / SYSTEM ROUTES ---
Route::middleware(['auth'])->group(function () {
    Route::get('/api/maintenance-check', [DashboardController::class, 'checkMaintenanceStatus'])->name('api.maintenance-check');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';