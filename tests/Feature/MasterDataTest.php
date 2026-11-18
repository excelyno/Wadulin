<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\District;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    private function regency(): User
    {
        return User::factory()->adminKabupaten()->create();
    }

    public function test_regency_creates_and_deactivates_category(): void
    {
        $admin = $this->regency();

        $this->actingAs($admin)->post(route('kabupaten.kategori.store'), ['name' => 'Jalan Rusak', 'is_active' => '1'])
            ->assertSessionHasNoErrors();

        $category = Category::firstWhere('slug', 'jalan-rusak');
        $this->assertNotNull($category);

        $this->actingAs($admin)->put(route('kabupaten.kategori.update', $category), ['name' => 'Jalan Rusak', 'is_active' => '0'])
            ->assertSessionHasNoErrors();

        $this->assertFalse($category->refresh()->is_active);
        $this->actingAs($admin)->get(route('kabupaten.kategori.index'))->assertOk();
    }

    public function test_duplicate_category_name_is_rejected(): void
    {
        Category::factory()->create(['name' => 'Air Bersih', 'slug' => 'air-bersih']);

        $this->actingAs($this->regency())->post(route('kabupaten.kategori.store'), ['name' => 'Air Bersih'])
            ->assertSessionHasErrors('slug');
    }

    public function test_regency_creates_district_officer(): void
    {
        $district = District::factory()->create();

        $this->actingAs($this->regency())->post(route('kabupaten.petugas.store'), [
            'name' => 'Petugas Baru',
            'email' => 'petugas@example.com',
            'phone' => '081234567890',
            'district_id' => $district->id,
            'password' => 'rahasia123',
        ])->assertSessionHasNoErrors();

        $officer = User::firstWhere('email', 'petugas@example.com');
        $this->assertSame(UserRole::AdminKecamatan, $officer->role);
        $this->assertTrue($officer->district->is($district));
    }

    public function test_officer_can_be_deactivated_but_admin_cannot_deactivate_self(): void
    {
        $admin = $this->regency();
        $officer = User::factory()->adminKecamatan()->create();

        $this->actingAs($admin)->put(route('kabupaten.petugas.update', $officer), [
            'name' => $officer->name, 'phone' => $officer->phone, 'district_id' => $officer->district_id, 'is_active' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertFalse($officer->refresh()->is_active);

        $this->actingAs($admin)->put(route('kabupaten.petugas.update', $admin), [
            'name' => $admin->name, 'phone' => $admin->phone, 'is_active' => '0',
        ])->assertSessionHasErrors('is_active');
        $this->assertTrue($admin->refresh()->is_active);
    }

    public function test_citizen_account_cannot_be_edited_from_officer_page(): void
    {
        $citizen = User::factory()->create();

        $this->actingAs($this->regency())->put(route('kabupaten.petugas.update', $citizen), [
            'name' => 'Diubah', 'phone' => '081234567890',
        ])->assertNotFound();
    }
}
