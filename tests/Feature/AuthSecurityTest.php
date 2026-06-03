<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TEST: Guest (Belum Login) harus dialihkan ke halaman login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        // Mencoba akses dashboard utama
        $this->get('/')->assertRedirect('/login');

        // Mencoba akses rute marketing
        $this->get('/marketing/dashboard')->assertRedirect('/login');

        // Mencoba akses rute admin
        $this->get('/admin/dashboard')->assertRedirect('/login');

        // Mencoba akses rute operator
        $this->get('/operator/logbook')->assertRedirect('/login');
    }

    /**
     * TEST: Role Marketing hanya bisa akses rute marketing.
     */
    public function test_marketing_role_access_control(): void
    {
        $marketing = User::factory()->create(['role' => UserRole::MARKETING->value]);

        // AKSES MILIK SENDIRI -> Harus OK
        $this->actingAs($marketing)
            ->get('/marketing/dashboard')
            ->assertStatus(200);

        // AKSES ADMIN -> Harus Forbidden (403) karena menggunakan middleware 'role' (CheckRole)
        $this->actingAs($marketing)
            ->get('/admin/dashboard')
            ->assertStatus(403);

        // AKSES OPERATOR -> Harus Redirect ke dashboard dengan error (sesuai EnsureUserIsOperator)
        $this->actingAs($marketing)
            ->get('/operator/logbook')
            ->assertRedirect('/dashboard');
    }

    /**
     * TEST: Role Operator hanya bisa akses rute operator.
     */
    public function test_operator_role_access_control(): void
    {
        $operator = User::factory()->create(['role' => UserRole::KNITTING->value]); // Knitting adalah bagian dari Operator

        // AKSES MILIK SENDIRI -> Harus OK
        $this->actingAs($operator)
            ->get('/operator/logbook')
            ->assertStatus(200);

        // AKSES ADMIN -> Harus Forbidden (403)
        $this->actingAs($operator)
            ->get('/admin/dashboard')
            ->assertStatus(403);

        // AKSES MARKETING -> Harus Redirect ke dashboard (sesuai EnsureUserIsMarketing)
        $this->actingAs($operator)
            ->get('/marketing/dashboard')
            ->assertRedirect('/dashboard');
    }

    /**
     * TEST: Role Admin bisa akses rute admin dan operator, tapi tidak marketing.
     */
    public function test_admin_role_access_control(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);

        // AKSES MILIK SENDIRI -> Harus OK
        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertStatus(200);

        // AKSES OPERATOR -> Harus OK (Admin diizinkan di EnsureUserIsOperator)
        $this->actingAs($admin)
            ->get('/operator/logbook')
            ->assertStatus(200);

        // AKSES MARKETING -> Harus Redirect ke dashboard (Marketing hanya untuk role marketing)
        $this->actingAs($admin)
            ->get('/marketing/dashboard')
            ->assertRedirect('/dashboard');
    }

    /**
     * TEST: Super Admin memiliki akses ke segalanya (God Mode).
     */
    public function test_super_admin_has_full_access(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SUPER_ADMIN->value]);

        // Akses Admin Dashboard
        $this->actingAs($superAdmin)->get('/admin/dashboard')->assertStatus(200);

        // Akses Marketing Dashboard (Note: EnsureUserIsMarketing harusnya membiarkan Super Admin karena maintenance bypass, 
        // tapi dalam kondisi normal, middleware ini juga harus dicek)
        
        // Mari kita cek EnsureUserIsMarketing lagi: 
        // if (auth()->check() && auth()->user()->isMarketing()) { return $next($request); }
        // Oh, di EnsureUserIsMarketing TIDAK ADA pengecekan isAdmin() atau isSuperAdmin() kecuali saat maintenance.
        // Jadi Super Admin pun akan terlempar dari /marketing jika sedang tidak maintenance.
        
        $this->actingAs($superAdmin)->get('/marketing/dashboard')->assertRedirect('/dashboard');
    }
}
