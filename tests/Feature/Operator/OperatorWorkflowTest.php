<?php

namespace Tests\Feature\Operator;

use App\Models\MarketingOrder;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Operator\KnittingForm;

class OperatorWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_start_and_finish_knitting_job()
    {
        $division = \App\Models\Division::factory()->create(['name' => 'RAJUT']);
        $user = User::factory()->create([
            'role' => UserRole::KNITTING->value,
            'division_id' => $division->id
        ]);

        $order = MarketingOrder::factory()->create([
            'status' => 'knitting',
            'processing_by' => null
        ]);

        $this->actingAs($user);

        // 1. Visit the logbook and start the job
        Livewire::test('operator.logbook')
            ->call('startProcessAndRedirect', $order->id)
            ->assertRedirect(route('operator.knitting', ['artikel' => $order->art_no]));

        $order->refresh();
        $this->assertEquals($user->id, $order->processing_by);

        // 2. Submit production data on the knitting form
        Livewire::test(KnittingForm::class, ['artikel' => $order->art_no])
            ->set('kg', 150.5)
            ->set('roll', 10)
            ->set('no_mesin', 'M-01')
            ->set('type_mesin', 'Circular')
            ->set('gauge_inch', '30/34')
            ->set('jml_feeder', 96)
            ->set('jml_jarum', 2800)
            ->set('lebar', 72)
            ->set('gramasi', 150)
            ->set('operator_name', 'John Operator')
            ->set('rnd_gramasi_greige', '150')
            ->set('rnd_mesin_rajut', 'M-01')
            ->set('rnd_jenis_mesin_rajut', 'Circular')
            ->call('submitForm')
            ->assertHasNoErrors()
            ->assertRedirect(route('operator.logbook'));

        $order->refresh();
        // Status should advance to dyeing (default next status after knitting)
        $this->assertEquals('dyeing', $order->status);
        $this->assertNull($order->processing_by);

        $this->assertDatabaseHas('production_activities', [
            'marketing_order_id' => $order->id,
            'division_name' => 'knitting',
            'kg' => 150.5,
            'roll' => 10
        ]);
    }

    public function test_unauthorized_user_cannot_access_operator_routes()
    {
        $user = User::factory()->create(['role' => UserRole::MARKETING->value]);
        $this->actingAs($user);

        $response = $this->get(route('operator.knitting'));
        $response->assertRedirect('/dashboard');
    }
}
