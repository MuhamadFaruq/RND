<?php

namespace Tests\Unit\Repositories;

use App\Models\MarketingOrder;
use App\Models\User;
use App\Repositories\OrderRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new OrderRepository();
    }

    public function test_find_by_identifier()
    {
        $order = MarketingOrder::factory()->create([
            'art_no' => 'ART-111',
            'sap_no' => 222333
        ]);

        $foundByArt = $this->repository->findByIdentifier('ART-111');
        $this->assertEquals($order->id, $foundByArt->id);

        $foundBySap = $this->repository->findByIdentifier('222333');
        $this->assertEquals($order->id, $foundBySap->id);
    }

    public function test_update_status()
    {
        $order = MarketingOrder::factory()->create(['status' => 'knitting']);
        $user = User::factory()->create();

        $this->repository->updateStatus($order->id, 'dyeing', $user->id);

        $order->refresh();
        $this->assertEquals('dyeing', $order->status);
        $this->assertEquals($user->id, $order->processing_by);
        $this->assertNotNull($order->processing_at);
    }

    public function test_get_queue_for_knitting()
    {
        MarketingOrder::factory()->count(3)->create(['status' => 'knitting']);
        MarketingOrder::factory()->count(2)->create(['status' => 'dyeing']);

        $queue = $this->repository->getQueue('knitting');
        $this->assertEquals(3, $queue->total());
    }

    public function test_get_wip_orders()
    {
        $user = User::factory()->create();
        MarketingOrder::factory()->count(2)->create([
            'status' => 'knitting',
            'processing_by' => $user->id
        ]);
        MarketingOrder::factory()->count(3)->create([
            'status' => 'knitting',
            'processing_by' => null
        ]);

        $wip = $this->repository->getWipOrders('RAJUT');
        $this->assertEquals(2, $wip->count());
    }
}
