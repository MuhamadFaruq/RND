<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use App\Enums\UserRole;
use App\Services\ProductionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrandTourTest extends TestCase
{
    use RefreshDatabase;

    protected $marketing;
    protected $operator;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->marketing = User::factory()->create(['role' => UserRole::MARKETING->value]);
        $this->operator = User::factory()->create(['role' => UserRole::KNITTING->value]);
        $this->service = app(ProductionService::class);
    }

    /**
     * TEST: Grand Tour Lifecycle (Hulu ke Hilir)
     * Menguji satu order melewati seluruh tahapan produksi hingga Finished.
     */
    public function test_full_lifecycle_from_marketing_to_finished(): void
    {
        // 1. HULU: Marketing membuat order dengan alur lengkap (Full Pipeline)
        $order = MarketingOrder::factory()->create([
            'art_no' => 'GRAND-TOUR-001',
            'status' => 'knitting',
            'req_stenter' => true,
            'req_pengujian' => true,
            'req_qe' => true,
        ]);

        $this->assertEquals('knitting', $order->status);

        // 2. PRODUKSI: Knitting -> Dyeing
        $this->service->processKnitting($order->id, $this->operator->id, 100, 5, ['mesin' => 'K01']);
        $order->refresh();
        $this->assertEquals('dyeing', $order->status);

        // 3. PRODUKSI: Dyeing -> Relax Dryer
        $this->service->processDyeing($order->id, $this->operator->id, ['resep' => 'R01']);
        $order->refresh();
        $this->assertEquals('relax-dryer', $order->status);

        // 4. PRODUKSI: Relax Dryer -> Stenter (Karena req_stenter = true)
        $this->service->processRelaxDryer($order->id, $this->operator->id, 98, 5, 1, ['temp' => '180C']);
        $order->refresh();
        $this->assertEquals('stenter', $order->status);

        // 5. PRODUKSI: Stenter -> Pengujian (Karena req_pengujian = true)
        $this->service->processStenter($order->id, $this->operator->id, 1, ['speed' => '20m/m'], true);
        $order->refresh();
        $this->assertEquals('pengujian', $order->status);

        // 6. HILIR: Pengujian -> QE (Karena req_qe = true)
        $this->service->processPengujian($order->id, $this->operator->id, 1, ['shrinkage' => '-2%']);
        $order->refresh();
        $this->assertEquals('qe', $order->status);

        // 7. HILIR: QE -> Finished (Puncak alur)
        $this->service->processQE($order->id, $this->operator->id, 1, ['grade' => 'A']);
        $order->refresh();
        $this->assertEquals('finished', $order->status);

        // VERIFIKASI AKHIR: Pastikan seluruh aktivitas tercatat (Knitting, Dyeing, Relax, Stenter, Pengujian, QE = 6 tahapan)
        $this->assertEquals(6, ProductionActivity::where('marketing_order_id', $order->id)->count());
        
        // VERIFIKASI AKHIR: Pastikan log audit mencatat pembuatan hingga akhir (secara logika)
        $this->assertDatabaseHas('activity_logs', [
            'art_no' => 'GRAND-TOUR-001',
            'action' => 'CREATE_PRODUCTION_LOG' // Minimal satu log produksi ada
        ]);
    }

    /**
     * TEST: Dynamic Pipeline Skip
     * Menguji sistem melompati tahap yang tidak dipilih Marketing.
     */
    public function test_order_skips_optional_stages_correctly(): void
    {
        // Order tanpa stenter, tanpa pengujian, langsung ke QE
        $order = MarketingOrder::factory()->create([
            'art_no' => 'SKIP-TEST-001',
            'status' => 'relax-dryer', // Kita mulai dari relax-dryer untuk efisiensi tes
            'req_stenter' => false,
            'req_pengujian' => false,
            'req_qe' => true,
        ]);

        // Dari Relax Dryer, harusnya langsung ke QE melompati Stenter & Pengujian
        $this->service->processRelaxDryer($order->id, $this->operator->id, 50, 2, 1, []);
        $order->refresh();
        
        $this->assertEquals('qe', $order->status);
    }
}
