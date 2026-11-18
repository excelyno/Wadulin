<?php

namespace Tests\Feature;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\District;
use App\Models\User;
use App\Notifications\ComplaintUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ComplaintDispositionTest extends TestCase
{
    use RefreshDatabase;

    public function test_district_officer_hands_complaint_to_another_district(): void
    {
        Notification::fake();
        $complaint = Complaint::factory()->create(['due_at' => now()->addHour()]);
        $target = District::factory()->create();

        $this->actingAs(User::factory()->adminKecamatan($complaint->district)->create())
            ->post(route('kecamatan.pengaduan.disposisi', $complaint), ['district_id' => $target->id, 'reason' => 'Lokasi masuk wilayah kecamatan sebelah.'])
            ->assertRedirect(route('kecamatan.pengaduan.index'));

        $complaint->refresh();
        $this->assertTrue($complaint->district->is($target));
        $this->assertTrue($complaint->due_at->gt(now()->addDay()));
        $this->assertSame(1, $complaint->dispositions()->count());
        Notification::assertSentTo($complaint->user, ComplaintUpdated::class);
    }

    public function test_disposition_needs_a_reason_and_a_different_district(): void
    {
        $complaint = Complaint::factory()->create();

        $this->actingAs(User::factory()->adminKecamatan($complaint->district)->create())
            ->post(route('kecamatan.pengaduan.disposisi', $complaint), ['district_id' => $complaint->district_id, 'reason' => 'pendek'])
            ->assertSessionHasErrors(['district_id', 'reason']);
    }

    public function test_complaint_already_in_progress_cannot_be_handed_over(): void
    {
        $complaint = Complaint::factory()->withStatus(ComplaintStatus::Diproses)->create();

        $this->actingAs(User::factory()->adminKecamatan($complaint->district)->create())
            ->post(route('kecamatan.pengaduan.disposisi', $complaint), ['district_id' => District::factory()->create()->id, 'reason' => 'Lokasi masuk wilayah lain.'])
            ->assertForbidden();
    }
}
