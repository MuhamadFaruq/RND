<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\MarketingOrder;
use App\Models\ActivityLog;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Admin\RecycleBin;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $this->superAdmin = User::factory()->create(['role' => UserRole::SUPER_ADMIN->value]);
    }

    /**
     * TEST: Admin dapat mengelola user.
     */
    public function test_admin_can_manage_users(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserManagement::class)
            ->set('name', 'New Staff')
            ->set('email', 'staff@duniatex.com')
            ->set('password', 'password123')
            ->set('role', UserRole::MARKETING->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'staff@duniatex.com',
            'role' => UserRole::MARKETING->value
        ]);
    }

    /**
     * TEST: Super Admin dapat melakukan Impersonation.
     */
    public function test_super_admin_can_impersonate_user(): void
    {
        $targetUser = User::factory()->create(['role' => UserRole::MARKETING->value]);

        $this->actingAs($this->superAdmin)
            ->get(route('admin.impersonate', $targetUser->id))
            ->assertRedirect(route('dashboard'));

        // Cek apakah sekarang login sebagai targetUser
        $this->assertEquals($targetUser->id, auth()->id());
        $this->assertTrue(session()->has('impersonator_id'));
    }

    /**
     * TEST: Admin biasa TIDAK BISA melakukan Impersonation.
     */
    public function test_regular_admin_cannot_impersonate(): void
    {
        $targetUser = User::factory()->create(['role' => UserRole::MARKETING->value]);

        $this->actingAs($this->admin)
            ->get(route('admin.impersonate', $targetUser->id))
            ->assertStatus(403);
    }

    /**
     * TEST: Fitur Recycle Bin (Hapus Permanen oleh Super Admin).
     */
    public function test_super_admin_can_permanently_delete_archived_order(): void
    {
        $archived = \App\Models\ArchivedOrder::create([
            'sap_no' => 'ARC-111',
            'art_no' => 'ART-ARC',
            'original_data' => ['test' => true]
        ]);

        Livewire::actingAs($this->superAdmin)
            ->test(RecycleBin::class)
            ->call('destroyPermanently', $archived->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('archived_orders', ['id' => $archived->id]);
    }

    /**
     * TEST: Admin biasa TIDAK BISA menghapus permanen dari Recycle Bin.
     */
    public function test_regular_admin_cannot_permanently_delete(): void
    {
        $archived = \App\Models\ArchivedOrder::create([
            'sap_no' => 'ARC-222',
            'original_data' => ['test' => true]
        ]);

        Livewire::actingAs($this->admin)
            ->test(RecycleBin::class)
            ->call('destroyPermanently', $archived->id);

        $this->assertDatabaseHas('archived_orders', ['id' => $archived->id]);
    }

    /**
     * TEST: Export Laporan Produksi.
     */
    public function test_admin_can_access_production_export(): void
    {
        // Mocking PDF agar tidak benar-benar men-download file saat tes (opsional, tapi bawaan DomPDF biasanya aman)
        $response = $this->actingAs($this->admin)
            ->get(route('admin.export', [
                'format' => 'pdf',
                'mode' => 'all',
                'start' => now()->subDays(7)->format('Y-m-d'),
                'end' => now()->format('Y-m-d')
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * TEST: Audit Log Monitoring.
     */
    public function test_admin_can_see_activity_logs(): void
    {
        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'TEST_ACTION',
            'description' => 'Admin testing log'
        ]);

        // Karena admin.activity-logs adalah Volt component (anonymous), 
        // kita tes melalui route-nya
        $this->actingAs($this->admin)
            ->get(route('admin.activity-logs'))
            ->assertStatus(200)
            ->assertSee('TEST_ACTION');
    }
}
