<?php

namespace Tests\Feature\Operator;

use App\Models\User;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Operator\KnittingForm;

class ProductionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $operatorUser;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup User Operator Knitting
        $this->operatorUser = User::factory()->create([
            'role' => UserRole::KNITTING->value,
            'name' => 'Operator Knitting A'
        ]);

        // Setup Order yang siap di-Knitting
        $this->order = MarketingOrder::factory()->create([
            'art_no' => 'KNIT-001',
            'status' => 'knitting',
            'kg_target' => 100,
            'roll_target' => 5,
            'req_stenter' => true, // Menandakan alur butuh stenter nanti
        ]);
    }

    /**
     * TEST: Operator dapat memuat detail artikel di form knitting.
     */
    public function test_operator_can_load_order_detail_by_art_no(): void
    {
        Livewire::actingAs($this->operatorUser)
            ->test(KnittingForm::class)
            ->set('artikelNo', 'KNIT-001')
            ->assertSet('sap_no', $this->order->sap_no)
            ->assertSee($this->order->pelanggan);
    }

    /**
     * TEST: Operator mengirim hasil produksi Knitting dan status berpindah ke Dyeing.
     */
    public function test_submitting_knitting_production_advances_status_to_dyeing(): void
    {
        Livewire::actingAs($this->operatorUser)
            ->test(KnittingForm::class)
            ->set('artikelNo', 'KNIT-001')
            ->set('kg', 105)
            ->set('roll', 5)
            ->set('operator_name', 'Operator Knitting A')
            ->set('tanggal', now()->format('Y-m-d'))
            ->set('no_mesin', 'K-01')
            ->set('type_mesin', 'Single')
            ->set('gauge_inch', '30')
            ->set('jml_feeder', 96)
            ->set('jml_jarum', 2880)
            ->set('lebar', 72)
            ->set('gramasi', 150)
            ->set('rnd_gramasi_greige', '140')
            ->set('rnd_mesin_rajut', 'M-01')
            ->set('rnd_jenis_mesin_rajut', 'SJ')
            ->call('save') // Menggunakan save() agar melewati validasi
            ->assertHasNoErrors()
            ->assertRedirect(route('operator.logbook'));

        // Verifikasi Database: Status Order harus berubah menjadi 'dyeing'
        $this->order->refresh();
        $this->assertEquals('dyeing', $this->order->status);

        // Verifikasi Database: Data Produksi tersimpan di production_activities
        $this->assertDatabaseHas('production_activities', [
            'marketing_order_id' => $this->order->id,
            'division_name' => 'knitting',
            'kg' => 105,
            'roll' => 5
        ]);
    }

    /**
     * TEST: Operator mulai mengerjakan order (Start Job).
     */
    public function test_operator_can_start_job(): void
    {
        $service = app(\App\Services\ProductionService::class);
        $service->startJob($this->order->id, $this->operatorUser->id);

        // Verifikasi Database: Audit Log 'START_PROCESS' tercatat
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->operatorUser->id,
            'action' => 'START_PROCESS',
            'art_no' => 'KNIT-001'
        ]);
    }

    /**
     * TEST: Estafet Produksi Berurutan (Dyeing -> Relax-Dryer).
     */
    public function test_production_relay_dyeing_to_relax_dryer(): void
    {
        // 1. Siapkan order di posisi Dyeing
        $this->order->update(['status' => 'dyeing']);
        
        // 2. Gunakan ProductionService secara langsung untuk simulasi cepat (karena kita menguji sistem)
        $service = app(\App\Services\ProductionService::class);
        
        $service->processDyeing($this->order->id, $this->operatorUser->id, [
            'no_resep' => 'R-999',
            'suhu' => '100C'
        ]);

        $this->order->refresh();
        
        // Setelah Dyeing, status berikutnya dalam pipeline default adalah 'relax-dryer'
        $this->assertEquals('relax-dryer', $this->order->status);
    }

    /**
     * TEST: Penghapusan log produksi mengembalikan status order ke divisi log tersebut dan mereset status pengerjaan (untuk knitting).
     */
    public function test_log_deletion_reverts_status_to_division_name(): void
    {
        // 1. Buat Super Admin
        $superAdmin = User::factory()->create([
            'role' => 'super-admin',
            'name' => 'Super Admin Test'
        ]);

        // 2. Buat log Knitting untuk order
        $log = ProductionActivity::create([
            'marketing_order_id' => $this->order->id,
            'operator_id' => $this->operatorUser->id,
            'operator_name' => 'Operator Knitting A',
            'division_name' => 'knitting',
            'kg' => 100,
            'roll' => 5,
            'technical_data' => []
        ]);

        // 3. Set order status ke 'dyeing' dan is Processing by someone
        $this->order->update([
            'status' => 'dyeing',
            'processing_by' => $this->operatorUser->id,
            'processing_at' => now(),
        ]);

        // 4. Hapus log sebagai Super Admin
        Livewire::actingAs($superAdmin)
            ->test('operator.logbook')
            ->call('deleteEntry', $log->id)
            ->assertHasNoErrors();

        // 5. Verifikasi order dan log
        $this->order->refresh();
        $this->assertEquals('knitting', $this->order->status);
        $this->assertNull($this->order->processing_by);
        $this->assertNull($this->order->processing_at);

        // Verifikasi log terhapus (soft deleted)
        $this->assertSoftDeleted('production_activities', [
            'id' => $log->id
        ]);
    }

    /**
     * TEST: Penghapusan massal alur Dyeing & Finishing ketika salah satu log dalam alurnya dihapus.
     */
    public function test_dyeing_finishing_mass_deletion_reverts_to_dyeing(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super-admin',
            'name' => 'Super Admin Test'
        ]);

        // Buat log Dyeing
        $logDyeing = ProductionActivity::create([
            'marketing_order_id' => $this->order->id,
            'operator_id' => $this->operatorUser->id,
            'operator_name' => 'Op Dyeing',
            'division_name' => 'dyeing',
            'kg' => 100, 'roll' => 5, 'technical_data' => []
        ]);

        // Buat log Relax Dryer
        $logRelax = ProductionActivity::create([
            'marketing_order_id' => $this->order->id,
            'operator_id' => $this->operatorUser->id,
            'operator_name' => 'Op Relax',
            'division_name' => 'relax-dryer',
            'kg' => 100, 'roll' => 5, 'technical_data' => []
        ]);

        // Set status order ke compactor (tahap berikutnya)
        $this->order->update([
            'status' => 'compactor',
            'processing_by' => $this->operatorUser->id,
            'processing_at' => now(),
        ]);

        // Hapus log relax-dryer
        Livewire::actingAs($superAdmin)
            ->test('operator.logbook')
            ->call('deleteEntry', $logRelax->id)
            ->assertHasNoErrors();

        // Verifikasi status pesanan kembali ke 'dyeing' dan di-reset
        $this->order->refresh();
        $this->assertEquals('dyeing', $this->order->status);
        $this->assertNull($this->order->processing_by);

        // Verifikasi log Dyeing DAN Relax Dryer terhapus sekaligus
        $this->assertSoftDeleted('production_activities', ['id' => $logDyeing->id]);
        $this->assertSoftDeleted('production_activities', ['id' => $logRelax->id]);
    }

    /**
     * TEST: Pemisahan antrean permintaan agar tidak tumpang tindih untuk masing-masing operator.
     */
    public function test_queue_filtering_by_exact_operator_role(): void
    {
        $dyeingOperator = User::factory()->create([
            'role' => 'dyeing',
            'name' => 'Dyeing Operator'
        ]);

        $relaxOperator = User::factory()->create([
            'role' => 'relax-dryer',
            'name' => 'Relax Operator'
        ]);

        // Order 1: berstatus 'dyeing'
        $order1 = MarketingOrder::factory()->create([
            'art_no' => 'ART-D1',
            'status' => 'dyeing'
        ]);

        // Order 2: berstatus 'relax-dryer'
        $order2 = MarketingOrder::factory()->create([
            'art_no' => 'ART-R1',
            'status' => 'relax-dryer'
        ]);

        // Operator Dyeing melihat antrean
        Livewire::actingAs($dyeingOperator)
            ->test('operator.logbook')
            ->set('currentMenu', 'orders')
            ->assertSee('ART-D1')
            ->assertDontSee('ART-R1'); // Tidak boleh melihat order relax-dryer

        // Operator Relax Dryer melihat antrean
        Livewire::actingAs($relaxOperator)
            ->test('operator.logbook')
            ->set('currentMenu', 'orders')
            ->assertSee('ART-R1')
            ->assertDontSee('ART-D1'); // Tidak boleh melihat order dyeing
    }

    /**
     * TEST: Data log Dyeing & Finishing mewarisi nilai KG & Roll (fallback) dari Knitting atau target MarketingOrder jika kosong.
     */
    public function test_dyeing_log_inherits_kg_and_roll_fallback(): void
    {
        // 1. Kasus A: Ada log Knitting sebelumnya, harus mewarisi dari Knitting
        $orderWithKnit = MarketingOrder::factory()->create([
            'art_no' => 'ART-WITH-KNIT',
            'status' => 'dyeing',
            'kg_target' => 500,
            'roll_target' => 20
        ]);

        ProductionActivity::create([
            'marketing_order_id' => $orderWithKnit->id,
            'operator_id' => $this->operatorUser->id,
            'operator_name' => 'Operator Knitting A',
            'division_name' => 'knitting',
            'kg' => 120, // Berbeda dari target order
            'roll' => 6,
            'technical_data' => []
        ]);

        $service = app(\App\Services\ProductionService::class);
        $service->processDyeing($orderWithKnit->id, $this->operatorUser->id, [
            'operator_manual_name' => 'Op Dyeing'
        ]);

        $dyeingLog = ProductionActivity::where('marketing_order_id', $orderWithKnit->id)
            ->where('division_name', 'dyeing')
            ->first();
        
        $this->assertNotNull($dyeingLog);
        $this->assertEquals(120, $dyeingLog->kg); // Mewarisi dari Knitting
        $this->assertEquals(6, $dyeingLog->roll);

        // 2. Kasus B: Tidak ada log sebelumnya, harus menggunakan target order
        $orderNoKnit = MarketingOrder::factory()->create([
            'art_no' => 'ART-NO-KNIT',
            'status' => 'dyeing',
            'kg_target' => 450,
            'roll_target' => 15
        ]);

        $service->processDyeing($orderNoKnit->id, $this->operatorUser->id, [
            'operator_manual_name' => 'Op Dyeing'
        ]);

        $dyeingLog2 = ProductionActivity::where('marketing_order_id', $orderNoKnit->id)
            ->where('division_name', 'dyeing')
            ->first();

        $this->assertNotNull($dyeingLog2);
        $this->assertEquals(450, $dyeingLog2->kg); // Menggunakan target order
        $this->assertEquals(15, $dyeingLog2->roll);
    }

    /**
     * TEST: Tombol KEMBALI pada SingleOperatorForm mengarahkan kembali ke logbook operator.
     */
    public function test_single_operator_form_back_button_redirects_to_logbook(): void
    {
        $dyeingOperator = User::factory()->create([
            'role' => 'dyeing',
            'name' => 'Dyeing Operator'
        ]);

        Livewire::actingAs($dyeingOperator)
            ->test(\App\Livewire\Operator\SingleOperatorForm::class, ['artikel' => 'KNIT-001'])
            ->call('goBack')
            ->assertRedirect(route('operator.logbook'));
    }

    /**
     * TEST: Dashboard Admin Monitoring mengelompokkan output per order (no double-counting).
     */
    public function test_admin_monitoring_prevents_double_counting_for_same_order(): void
    {
        // 1. Buat order baru
        $order = MarketingOrder::factory()->create([
            'art_no' => 'DOUBLE-001',
            'status' => 'relax-dryer'
        ]);

        // 2. Buat log Dyeing untuk order hari ini
        ProductionActivity::create([
            'marketing_order_id' => $order->id,
            'operator_id' => $this->operatorUser->id,
            'operator_name' => 'Op Dyeing',
            'division_name' => 'dyeing',
            'kg' => 9,
            'roll' => 1,
            'created_at' => now(),
        ]);

        // 3. Buat log Relax Dryer untuk order yang sama hari ini
        ProductionActivity::create([
            'marketing_order_id' => $order->id,
            'operator_id' => $this->operatorUser->id,
            'operator_name' => 'Op Relax',
            'division_name' => 'relax-dryer',
            'kg' => 9,
            'roll' => 1,
            'created_at' => now()->addMinutes(5),
        ]);

        // 4. Buat order kedua untuk Knitting hari ini
        $order2 = MarketingOrder::factory()->create([
            'art_no' => 'DOUBLE-002',
            'status' => 'completed'
        ]);

        ProductionActivity::create([
            'marketing_order_id' => $order2->id,
            'operator_id' => $this->operatorUser->id,
            'operator_name' => 'Op Knitting',
            'division_name' => 'knitting',
            'kg' => 10,
            'roll' => 1,
            'created_at' => now(),
        ]);

        // 5. Test Livewire admin.monitoring
        Livewire::test('admin.monitoring')
            ->assertViewHas('todayProduction', 19.0) // 9 KG Warna + 10 KG Knitting
            ->assertViewHas('summary', function ($summary) {
                return $summary['warna_kg'] == 9 && $summary['rajut_kg'] == 10;
            });
    }

    /**
     * TEST: Dashboard Admin Monitoring membagi hitungan order ke masing-masing operator dan menampilkan kotak Finished.
     */
    public function test_admin_monitoring_shows_order_distribution_and_finished_card(): void
    {
        // 1. Bersihkan tabel marketing_orders dan production_activities untuk test ini agar tidak polusi data
        MarketingOrder::query()->delete();
        ProductionActivity::query()->delete();

        // 2. Buat 3 order dengan status berbeda-beda hari ini
        MarketingOrder::factory()->create([
            'art_no' => 'ORDER-KNT',
            'status' => 'knitting',
            'created_at' => now(),
        ]);
        MarketingOrder::factory()->create([
            'art_no' => 'ORDER-DYE',
            'status' => 'stenter', // Bagian dari alur Warna/Dyeing
            'created_at' => now(),
        ]);
        MarketingOrder::factory()->create([
            'art_no' => 'ORDER-FIN',
            'status' => 'finished', // Selesai Produksi
            'created_at' => now(),
        ]);

        // 3. Test Component Livewire admin.monitoring
        Livewire::test('admin.monitoring')
            ->assertViewHas('summary', function ($summary) {
                return $summary['marketing_mo'] == 3 && isset($summary['finished_kg']);
            })
            ->assertViewHas('divisionStats', function ($stats) {
                $knitting = $stats->firstWhere('name', 'KNITTING');
                $dyeing = $stats->firstWhere('name', 'DYEING');
                $finished = $stats->firstWhere('name', 'FINISHED');
                $pengujian = $stats->firstWhere('name', 'PENGUJIAN');
                $qe = $stats->firstWhere('name', 'QE');

                return $knitting->count == 1
                    && $dyeing->count == 1
                    && $finished === null
                    && $pengujian->count == 0
                    && $qe->count == 0;
            });
    }

    /**
     * TEST: Component ProductionChart mengelompokkan output per order per jam (no double-counting).
     */
    public function test_production_chart_prevents_double_counting(): void
    {
        // 1. Bersihkan data
        MarketingOrder::query()->delete();
        ProductionActivity::query()->delete();

        // 2. Buat order baru
        $order = MarketingOrder::factory()->create([
            'art_no' => 'CHART-001',
            'status' => 'relax-dryer'
        ]);

        // 3. Buat log Dyeing jam 13:05
        ProductionActivity::create([
            'marketing_order_id' => $order->id,
            'operator_id' => $this->operatorUser->id,
            'operator_name' => 'Op Dyeing',
            'division_name' => 'dyeing',
            'kg' => 9.0,
            'roll' => 1,
            'created_at' => \Carbon\Carbon::today()->setHour(13)->setMinute(5),
        ]);

        // 4. Buat log Relax Dryer jam 13:10
        ProductionActivity::create([
            'marketing_order_id' => $order->id,
            'operator_id' => $this->operatorUser->id,
            'operator_name' => 'Op Relax',
            'division_name' => 'relax-dryer',
            'kg' => 9.0,
            'roll' => 1,
            'created_at' => \Carbon\Carbon::today()->setHour(13)->setMinute(10),
        ]);

        // 5. Test Livewire admin.production-chart
        Livewire::test('admin.production-chart')
            ->assertViewHas('chartData', function ($chartData) {
                // Hour 13 (index 13) should have exactly 9.0 KG, not 18.0
                return $chartData[13] == 9.0;
            });
    }

    /**
     * TEST: Dashboard Marketing mengelompokkan 7 departemen finishing ke dalam Dyeing,
     * serta menampilkan kartu Finished, sehingga totalnya hanya ada 5 kartu tahap produksi.
     */
    public function test_marketing_dashboard_shows_five_unified_stages_and_finished_card(): void
    {
        // 1. Bersihkan data
        MarketingOrder::query()->delete();

        // 2. Buat order dengan status-status berbeda
        // Knitting
        MarketingOrder::factory()->create([
            'art_no' => 'ORDER-K',
            'status' => 'knitting',
            'kg_target' => 150
        ]);

        // Dyeing (proses stenter - dihitung masuk Dyeing & Finishing)
        MarketingOrder::factory()->create([
            'art_no' => 'ORDER-D1',
            'status' => 'stenter',
            'kg_target' => 200
        ]);

        // Dyeing (proses dyeing - dihitung masuk Dyeing & Finishing)
        MarketingOrder::factory()->create([
            'art_no' => 'ORDER-D2',
            'status' => 'dyeing',
            'kg_target' => 250
        ]);

        // Finished
        MarketingOrder::factory()->create([
            'art_no' => 'ORDER-F',
            'status' => 'finished',
            'kg_target' => 300
        ]);

        // 3. Buat user marketing
        $marketingUser = User::factory()->create([
            'role' => 'marketing',
            'name' => 'Marketing User'
        ]);

        // 4. Test Livewire marketing.marketing-dashboard
        Livewire::actingAs($marketingUser)
            ->test(\App\Livewire\Marketing\MarketingDashboard::class)
            ->assertViewHas('stages', function ($stages) {
                // Total stages harus ada 5
                if (count($stages) !== 5) {
                    return false;
                }

                $knitting = collect($stages)->firstWhere('name', 'Knitting');
                $dyeing = collect($stages)->firstWhere('name', 'Dyeing');
                $finished = collect($stages)->firstWhere('name', 'Finished');

                // Verifikasi load count dan load KG
                $knitOk = ($knitting['load_count'] === 1 && $knitting['load_kg'] == 150);
                // Dyeing & Finishing menggabungkan stenter dan dyeing (total 2 order, 450 KG)
                $dyeOk = ($dyeing['load_count'] === 2 && $dyeing['load_kg'] == 450);
                // Finished (1 order, 300 KG)
                $finOk = ($finished['load_count'] === 1 && $finished['load_kg'] == 300);

                return $knitOk && $dyeOk && $finOk;
            });
    }
}
