<?php

namespace Tests\Feature\Marketing;

use App\Models\User;
use App\Enums\UserRole;
use App\Models\MarketingOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Marketing\OrderForm;

class MarketingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketing_can_create_new_order()
    {
        $user = User::factory()->create(['role' => UserRole::MARKETING->value]);
        $this->actingAs($user);

        $test = Livewire::test(OrderForm::class)
            ->set('sap_no', 123456)
            ->set('art_no', 'TEST-ART-001')
            ->set('tanggal', now()->format('Y-m-d'))
            ->set('pelanggan', 'Test Customer')
            ->set('mkt', $user->name)
            ->set('warna', 'Red')
            ->set('kg_target', 500)
            ->set('roll_target', 20)
            ->set('target_lebar', '72"')
            ->set('target_gramasi', '150')
            ->set('kelompok_kain', 'UNIT 1')
            ->set('keperluan', 'R&D')
            ->set('konstruksi_greige', 'Single Jersey')
            ->set('material', 'Cotton')
            ->set('benang', '30s')
            ->set('belah_bulat', 'Bulat')
            ->set('handfeel', 'Soft')
            ->set('req_stenter', true)
            ->call('submit');

        $test->assertHasNoErrors();
        
        $this->assertDatabaseHas('marketing_orders', [
            'art_no' => 'TEST-ART-001',
            'pelanggan' => 'Test Customer',
            'sap_no' => 123456
        ]);

        $test->assertRedirect(route('marketing.orders.index'));

        $this->assertDatabaseHas('marketing_orders', [
            'art_no' => 'TEST-ART-001',
            'pelanggan' => 'Test Customer',
            'sap_no' => 123456
        ]);
    }

    public function test_order_validation()
    {
        $user = User::factory()->create(['role' => UserRole::MARKETING->value]);
        $this->actingAs($user);

        Livewire::test(OrderForm::class)
            ->set('art_no', '') // Required field
            ->call('submit')
            ->assertHasErrors(['art_no' => 'required']);
    }
}
