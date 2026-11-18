<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\District;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_always_creates_a_citizen(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'admin_kabupaten',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertSame(UserRole::Masyarakat, User::firstWhere('email', 'budi@example.com')->role);
    }

    public function test_each_role_lands_on_its_own_home_after_login(): void
    {
        $citizen = User::factory()->create();
        $districtOfficer = User::factory()->adminKecamatan(District::factory()->create())->create();
        $regencyOfficer = User::factory()->adminKabupaten()->create();

        foreach ([[$citizen, 'dashboard'], [$districtOfficer, 'kecamatan.dashboard'], [$regencyOfficer, 'kabupaten.dashboard']] as [$user, $home]) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
                ->assertRedirect(route($home));

            $this->post(route('logout'));
        }
    }

    public function test_inactive_account_cannot_log_in(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_citizen_cannot_open_admin_panel(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('kecamatan.dashboard'))
            ->assertForbidden();
    }
}
