<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\District;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminComplaintAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_district_officer_only_sees_own_district_queue(): void
    {
        $district = District::factory()->create();
        $officer = User::factory()->adminKecamatan($district)->create();
        $own = Complaint::factory()->for($district)->create();
        $other = Complaint::factory()->create();

        $this->actingAs($officer)
            ->get(route('kecamatan.pengaduan.index'))
            ->assertOk()
            ->assertSee($own->ticket_code)
            ->assertDontSee($other->ticket_code);

        $this->actingAs($officer)->get(route('kecamatan.pengaduan.show', $own))->assertOk();
        $this->actingAs($officer)->get(route('kecamatan.pengaduan.show', $other))->assertForbidden();
    }

    public function test_regency_officer_sees_every_complaint(): void
    {
        $complaints = Complaint::factory()->count(2)->create();

        $response = $this->actingAs(User::factory()->adminKabupaten()->create())
            ->get(route('kabupaten.pengaduan.index'))
            ->assertOk();

        $complaints->each(fn (Complaint $complaint) => $response->assertSee($complaint->ticket_code));
    }

    public function test_dashboards_render_for_both_panels(): void
    {
        Complaint::factory()->escalated()->create();

        $this->actingAs(User::factory()->adminKabupaten()->create())->get(route('kabupaten.dashboard'))->assertOk();
        $this->actingAs(User::factory()->adminKabupaten()->create())->get(route('kabupaten.eskalasi'))->assertOk();
        $this->actingAs(User::factory()->adminKecamatan()->create())->get(route('kecamatan.dashboard'))->assertOk();
    }

    public function test_district_officer_cannot_open_regency_only_pages(): void
    {
        $officer = User::factory()->adminKecamatan()->create();

        $this->actingAs($officer)->get(route('kabupaten.eskalasi'))->assertForbidden();
        $this->actingAs($officer)->get(route('kabupaten.pengumuman.index'))->assertForbidden();
    }
}
