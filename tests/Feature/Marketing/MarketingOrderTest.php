<?php

namespace Tests\Feature\Marketing;

use App\Models\User;
use App\Models\MarketingOrder;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Marketing\OrderForm;

class MarketingOrderTest extends TestCase
{
    use RefreshDatabase;

    protected $marketingUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->marketingUser = User::factory()->create([
            'role' => UserRole::MARKETING->value,
            'name' => 'Marketing User'
        ]);
    }

    /**
     * TEST: Berhasil membuat order baru dengan data valid.
     */
    public function test_can_create_marketing_order_with_valid_data(): void
    {
        Livewire::actingAs($this->marketingUser)
            ->test(OrderForm::class)
            ->set('art_no', 'ART-100')
            ->set('sap_no', '123456')
            ->set('tanggal', now()->format('Y-m-d'))
            ->set('pelanggan', 'PT Berdikari')
            ->set('mkt', 'Marketing User')
            ->set('keperluan', 'New Order')
            ->set('konstruksi_greige', 'Single Jersey')
            ->set('kelompok_kain', 'UNIT 1')
            ->set('belah_bulat', 'Bulat')
            ->set('handfeel', 'Soft')
            ->set('warna', 'Merah Cabai')
            ->set('kg_target', 500)
            ->set('target_lebar', '72')
            ->set('target_gramasi', '150')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('marketing.orders.index'));

        $this->assertDatabaseHas('marketing_orders', [
            'art_no' => 'ART-100',
            'sap_no' => '123456',
            'status' => 'knitting'
        ]);
    }

    /**
     * TEST: Validasi field wajib.
     */
    public function test_validation_errors_for_required_fields(): void
    {
        Livewire::actingAs($this->marketingUser)
            ->test(OrderForm::class)
            // Kosongkan mkt agar auto-fill tidak mengisi (opsional, tapi mkt diisi di mount)
            ->set('art_no', '')
            ->set('pelanggan', '')
            ->set('warna', '')
            ->call('submit')
            ->assertHasErrors([
                'art_no' => 'required',
                'pelanggan' => 'required',
                'warna' => 'required'
            ]);
    }

    /**
     * TEST: SAP No harus unik.
     */
    public function test_sap_no_must_be_unique(): void
    {
        // Buat order pertama
        MarketingOrder::factory()->create(['sap_no' => '999999']);

        // Coba buat order kedua dengan SAP No yang sama
        Livewire::actingAs($this->marketingUser)
            ->test(OrderForm::class)
            ->set('art_no', 'ART-200')
            ->set('sap_no', '999999') // Duplikat
            ->set('tanggal', now()->format('Y-m-d'))
            ->set('pelanggan', 'Buyer B')
            ->set('warna', 'Biru')
            ->set('kg_target', 100)
            ->set('target_lebar', '70')
            ->set('target_gramasi', '140')
            ->call('submit')
            ->assertHasErrors(['sap_no' => 'unique']);
    }

    /**
     * TEST: Memuat template dari artikel lama (Repeat Order).
     */
    public function test_can_load_article_template_from_history(): void
    {
        $pastOrder = MarketingOrder::factory()->create([
            'art_no' => 'HISTORY-01',
            'pelanggan' => 'Old Buyer',
            'warna' => 'Hijau Tua',
            'target_lebar' => '60',
            'target_gramasi' => '180'
        ]);

        Livewire::actingAs($this->marketingUser)
            ->test(OrderForm::class)
            ->call('loadArticleTemplate', $pastOrder->id)
            ->assertSet('art_no', 'HISTORY-01')
            ->assertSet('pelanggan', 'Old Buyer')
            ->assertSet('warna', 'Hijau Tua');
    }

    /**
     * TEST: Update order yang sudah ada.
     */
    public function test_can_update_existing_order(): void
    {
        $order = MarketingOrder::factory()->create([
            'art_no' => 'OLD-ART',
            'pelanggan' => 'Original Buyer',
            'mkt' => 'Old Marketing'
        ]);

        Livewire::actingAs($this->marketingUser)
            ->test(OrderForm::class, ['id' => $order->id])
            ->set('pelanggan', 'Updated Buyer')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('marketing.orders.index'));

        $this->assertDatabaseHas('marketing_orders', [
            'id' => $order->id,
            'pelanggan' => 'Updated Buyer'
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'UPDATE_ORDER',
            'art_no' => 'OLD-ART'
        ]);
    }
}
