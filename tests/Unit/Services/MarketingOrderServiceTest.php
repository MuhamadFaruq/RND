<?php

namespace Tests\Unit\Services;

use App\Models\MarketingOrder;
use App\Services\MarketingOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private MarketingOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MarketingOrderService();
    }

    public function test_get_filtered_query_by_search()
    {
        MarketingOrder::factory()->create(['art_no' => 'ART-123', 'pelanggan' => 'Customer A']);
        MarketingOrder::factory()->create(['art_no' => 'ART-456', 'pelanggan' => 'Customer B']);

        $query = $this->service->getFilteredQuery(['search' => '123']);
        $this->assertEquals(1, $query->count());
        $this->assertEquals('ART-123', $query->first()->art_no);

        $query = $this->service->getFilteredQuery(['search' => 'Customer B']);
        $this->assertEquals(1, $query->count());
        $this->assertEquals('ART-456', $query->first()->art_no);
    }

    public function test_get_filtered_query_by_status()
    {
        MarketingOrder::factory()->create(['status' => 'knitting']);
        MarketingOrder::factory()->create(['status' => 'finished']);

        $query = $this->service->getFilteredQuery(['statusFilter' => 'knitting']);
        $this->assertEquals(1, $query->count());
        $this->assertEquals('knitting', $query->first()->status);
    }

    public function test_get_status_summary()
    {
        MarketingOrder::factory()->count(2)->create(['status' => 'knitting']);
        MarketingOrder::factory()->count(3)->create(['status' => 'dyeing']);
        MarketingOrder::factory()->count(1)->create(['status' => 'finished']);

        $summary = $this->service->getStatusSummary();

        $this->assertEquals(2, $summary['knitting']);
        $this->assertEquals(3, $summary['active']); // dyeing is active
        $this->assertEquals(1, $summary['completed']);
        $this->assertEquals(6, $summary['total']);
    }
}
