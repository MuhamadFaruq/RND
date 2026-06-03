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
}
