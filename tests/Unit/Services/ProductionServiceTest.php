<?php

namespace Tests\Unit\Services;

use App\Models\MarketingOrder;
use App\Models\User;
use App\Services\ProductionService;
use App\Repositories\OrderRepository;
use App\Repositories\ActivityRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductionService $service;
    private OrderRepository $orderRepo;
    private ActivityRepository $activityRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderRepo = new OrderRepository();
        $this->activityRepo = new ActivityRepository();
        $this->service = new ProductionService($this->orderRepo, $this->activityRepo);
    }

    public function test_get_next_required_status_logic()
    {
        $order = MarketingOrder::factory()->create([
            'status' => 'knitting',
            'req_stenter' => true,
            'req_compactor' => false,
            'req_heat_setting' => false,
            'req_tumbler' => false,
            'req_fleece' => false,
            'req_pengujian' => true,
            'req_qe' => true,
        ]);

        // After knitting, should be dyeing
        $next = $this->service->getNextRequiredStatus($order, 'knitting');
        $this->assertEquals('dyeing', $next);

        // After dyeing, should be relax-dryer
        $next = $this->service->getNextRequiredStatus($order, 'dyeing');
        $this->assertEquals('relax-dryer', $next);

        // After relax-dryer, skip compactor/heat-setting and go to stenter (since req_stenter is true)
        $next = $this->service->getNextRequiredStatus($order, 'relax-dryer');
        $this->assertEquals('stenter', $next);

        // After stenter, skip tumbler/fleece and go to pengujian
        $next = $this->service->getNextRequiredStatus($order, 'stenter');
        $this->assertEquals('pengujian', $next);
    }

    public function test_start_job_updates_status_and_processing_by()
    {
        $user = User::factory()->create();
        $order = MarketingOrder::factory()->create(['status' => 'knitting', 'processing_by' => null]);

        $this->service->startJob($order->id, $user->id);

        $order->refresh();
        $this->assertEquals($user->id, $order->processing_by);
        $this->assertNotNull($order->processing_at);
    }

    public function test_cancel_job_clears_processing_by()
    {
        $user = User::factory()->create();
        $order = MarketingOrder::factory()->create([
            'status' => 'knitting',
            'processing_by' => $user->id,
            'processing_at' => now()
        ]);

        $this->service->cancelJob($order->id, $user->id);

        $order->refresh();
        $this->assertNull($order->processing_by);
        $this->assertNull($order->processing_at);
    }

    public function test_submit_operator_activity_advances_status()
    {
        $user = User::factory()->create();
        $order = MarketingOrder::factory()->create([
            'status' => 'knitting',
            'req_stenter' => true,
            'req_pengujian' => false,
            'req_qe' => false,
        ]);

        $this->service->submitOperatorActivity(
            $order->id,
            $user->id,
            'knitting',
            100.5,
            5,
            1,
            'John Doe'
        );

        $order->refresh();
        // After knitting, next status should be dyeing
        $this->assertEquals('dyeing', $order->status);
        $this->assertNull($order->processing_by); // Should be cleared after submit

        $this->assertDatabaseHas('production_activities', [
            'marketing_order_id' => $order->id,
            'division_name' => 'knitting',
            'kg' => 100.5,
            'roll' => 5
        ]);
    }
}
