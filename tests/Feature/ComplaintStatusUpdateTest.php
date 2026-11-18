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

class ComplaintStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function districtOfficerFor(Complaint $complaint): User
    {
        return User::factory()->adminKecamatan($complaint->district)->create();
    }

    public function test_district_officer_processes_complaint_and_reporter_is_notified(): void
    {
        Notification::fake();
        $complaint = Complaint::factory()->create();

        $this->actingAs($this->districtOfficerFor($complaint))
            ->patch(route('kecamatan.pengaduan.status', $complaint), ['status' => 'diproses', 'note' => 'Tim sudah dijadwalkan ke lokasi.'])
            ->assertSessionHasNoErrors();

        $this->assertSame(ComplaintStatus::Diproses, $complaint->refresh()->status);
        $this->assertSame(ComplaintStatus::Pending, $complaint->statusHistories()->sole()->from_status);
        Notification::assertSentTo($complaint->user, ComplaintUpdated::class);
    }

    public function test_internal_note_is_not_leaked_in_notification(): void
    {
        $complaint = Complaint::factory()->create();

        $this->actingAs($this->districtOfficerFor($complaint))
            ->patch(route('kecamatan.pengaduan.status', $complaint), ['status' => 'diproses', 'note' => 'Catatan rahasia internal', 'is_public' => '0']);

        $this->assertStringNotContainsString('rahasia', $complaint->user->notifications()->sole()->data['message']);
    }

    public function test_reporter_sees_notification_on_dashboard_and_can_mark_it_read(): void
    {
        $complaint = Complaint::factory()->create();
        $this->actingAs($this->districtOfficerFor($complaint))
            ->patch(route('kecamatan.pengaduan.status', $complaint), ['status' => 'diproses']);

        $this->actingAs($complaint->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Status pengaduan kamu kini Diproses.');

        $this->actingAs($complaint->user)->post(route('notifikasi.read'));

        $this->assertSame(0, $complaint->user->unreadNotifications()->count());
    }

    public function test_rejecting_requires_a_reason(): void
    {
        $complaint = Complaint::factory()->create();

        $this->actingAs($this->districtOfficerFor($complaint))
            ->patch(route('kecamatan.pengaduan.status', $complaint), ['status' => 'ditolak'])
            ->assertSessionHasErrors('note');

        $this->assertSame(ComplaintStatus::Pending, $complaint->refresh()->status);
    }

    public function test_status_cannot_skip_steps(): void
    {
        $complaint = Complaint::factory()->create();

        $this->actingAs($this->districtOfficerFor($complaint))
            ->patch(route('kecamatan.pengaduan.status', $complaint), ['status' => 'selesai'])
            ->assertSessionHasErrors('status');
    }

    public function test_officer_of_another_district_cannot_update(): void
    {
        $complaint = Complaint::factory()->create();

        $this->actingAs(User::factory()->adminKecamatan(District::factory()->create())->create())
            ->patch(route('kecamatan.pengaduan.status', $complaint), ['status' => 'diproses'])
            ->assertForbidden();
    }

    public function test_regency_may_only_act_on_escalated_complaints(): void
    {
        $regency = User::factory()->adminKabupaten()->create();
        $normal = Complaint::factory()->create();
        $escalated = Complaint::factory()->escalated()->create();

        $this->actingAs($regency)->patch(route('kabupaten.pengaduan.status', $normal), ['status' => 'diproses'])->assertForbidden();
        $this->actingAs($regency)->patch(route('kabupaten.pengaduan.status', $escalated), ['status' => 'diproses'])->assertSessionHasNoErrors();

        $this->assertSame(ComplaintStatus::Diproses, $escalated->refresh()->status);
    }
}
