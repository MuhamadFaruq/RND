<?php

namespace Tests\Feature\Admin;

use App\Models\MarketingOrder;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard_and_see_stats()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        
        $this->actingAs($admin);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // Test the Volt component directly
        Volt::test('admin.admin-dashboard')
            ->assertSee('System')
            ->assertSee('Control')
            ->assertSee('Total Personel')
            ->assertSee('Unit Divisi');
    }

    public function test_admin_can_see_monitoring_data()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $order = MarketingOrder::factory()->create(['art_no' => 'MONITOR-001']);
        
        // Create an activity so it shows up in monitoring
        \App\Models\ProductionActivity::factory()->create([
            'marketing_order_id' => $order->id,
            'division_name' => 'KNITTING'
        ]);

        $this->actingAs($admin);

        Volt::test('admin.monitoring')
            ->assertSee('MONITOR-001');
    }

    public function test_non_admin_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create(['role' => UserRole::OPERATOR->value]);
        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }
}
