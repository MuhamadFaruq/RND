<?php

namespace Tests\Unit\Repositories;

use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use App\Models\User;
use App\Repositories\ActivityRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ActivityRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ActivityRepository();
    }

    public function test_log_activity()
    {
        $order = MarketingOrder::factory()->create();
        $user = User::factory()->create();

        $activity = $this->repository->log([
            'marketing_order_id' => $order->id,
            'operator_id' => $user->id,
            'division_name' => 'knitting',
            'kg' => 50,
            'roll' => 2,
            'technical_data' => []
        ]);

        $this->assertDatabaseHas('production_activities', [
            'id' => $activity->id,
            'marketing_order_id' => $order->id,
            'kg' => 50
        ]);
    }

    public function test_find_for_division()
    {
        $order = MarketingOrder::factory()->create();
        ProductionActivity::factory()->create([
            'marketing_order_id' => $order->id,
            'division_name' => 'knitting'
        ]);

        $found = $this->repository->findForDivision($order->id, 'knitting');
        $this->assertNotNull($found);
        $this->assertEquals('knitting', $found->division_name);

        $notFound = $this->repository->findForDivision($order->id, 'dyeing');
        $this->assertNull($notFound);
    }

    public function test_get_operator_history()
    {
        $user = User::factory()->create();
        $order1 = MarketingOrder::factory()->create();
        $order2 = MarketingOrder::factory()->create();

        ProductionActivity::factory()->create(['operator_id' => $user->id, 'marketing_order_id' => $order1->id]);
        ProductionActivity::factory()->create(['operator_id' => $user->id, 'marketing_order_id' => $order2->id]);

        $history = $this->repository->getOperatorHistory($user->id);
        $this->assertEquals(2, $history->total());
    }

    public function test_get_operator_history_by_role_includes_imported_logs()
    {
        $user = User::factory()->create(['role' => 'knitting']);
        $adminUser = User::factory()->create(['role' => 'super-admin']);
        $order1 = MarketingOrder::factory()->create();
        $order2 = MarketingOrder::factory()->create();

        // 1. Log manual milik user
        ProductionActivity::factory()->create([
            'operator_id' => $user->id,
            'marketing_order_id' => $order1->id,
            'division_name' => 'knitting'
        ]);

        // 2. Log impor milik admin untuk divisi knitting
        ProductionActivity::factory()->create([
            'operator_id' => $adminUser->id,
            'marketing_order_id' => $order2->id,
            'division_name' => 'knitting'
        ]);

        // 3. Log impor milik admin untuk divisi lain (tidak boleh muncul)
        ProductionActivity::factory()->create([
            'operator_id' => $adminUser->id,
            'marketing_order_id' => $order2->id,
            'division_name' => 'dyeing'
        ]);

        $history = $this->repository->getOperatorHistory($user->id, 'knitting');
        
        // Seharusnya mengembalikan 2 log (1 manual knitting + 1 impor knitting)
        $this->assertEquals(2, $history->total());
    }
}
