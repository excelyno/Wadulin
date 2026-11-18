<?php

namespace Tests\Feature;

use App\Enums\ComplaintStatus;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\District;
use App\Models\QrLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComplaintSubmissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'category_id' => Category::factory()->create()->id,
            'district_id' => District::factory()->create()->id,
            'title' => 'Jalan berlubang di depan sekolah',
            'description' => 'Lubang besar sudah dua minggu dan membahayakan pengendara motor.',
            'latitude' => -8.17,
            'longitude' => 113.70,
            'photos' => [UploadedFile::fake()->create('bukti.jpg', 120, 'image/jpeg')],
            ...$overrides,
        ];
    }

    public function test_citizen_submits_complaint_with_ticket_sla_and_photo(): void
    {
        Storage::fake('public');
        $citizen = User::factory()->create();

        $response = $this->actingAs($citizen)->post(route('pengaduan.store'), $this->validPayload());

        $complaint = Complaint::sole();
        $response->assertRedirect(route('pengaduan.show', $complaint));
        $this->assertSame('WDL-'.now()->year.'-000001', $complaint->ticket_code);
        $this->assertSame(ComplaintStatus::Pending, $complaint->status);
        $this->assertTrue($complaint->due_at->isFuture());
        $this->assertCount(1, $complaint->statusHistories);
        Storage::disk('public')->assertExists($complaint->attachments->sole()->file_path);
    }

    public function test_complaint_requires_a_photo_and_location_inside_jember(): void
    {
        $response = $this->actingAs(User::factory()->create())->post(route('pengaduan.store'), $this->validPayload([
            'photos' => [],
            'latitude' => -6.2,
        ]));

        $response->assertSessionHasErrors(['photos', 'latitude']);
        $this->assertDatabaseCount('complaints', 0);
    }

    public function test_guest_is_sent_to_login_before_reporting(): void
    {
        $this->get(route('pengaduan.create'))->assertRedirect(route('login'));
    }

    public function test_scanned_qr_prefills_the_form_and_is_linked_to_the_complaint(): void
    {
        Storage::fake('public');
        $citizen = User::factory()->create();
        $qrLocation = QrLocation::factory()->create(['name' => 'Taman Kota Patrang']);

        $this->get(route('qr.scan', $qrLocation))
            ->assertRedirect(route('pengaduan.create', ['qr' => $qrLocation->code]));

        $this->actingAs($citizen)
            ->get(route('pengaduan.create', ['qr' => $qrLocation->code]))
            ->assertOk()
            ->assertSee('Taman Kota Patrang')
            ->assertSee('name="qr_location_id" value="'.$qrLocation->id.'"', false);

        $this->actingAs($citizen)->post(route('pengaduan.store'), $this->validPayload([
            'district_id' => $qrLocation->district_id,
            'qr_location_id' => $qrLocation->id,
        ]));

        $this->assertTrue(Complaint::sole()->qrLocation->is($qrLocation));
    }

    public function test_inactive_qr_cannot_be_scanned(): void
    {
        $this->get(route('qr.scan', QrLocation::factory()->inactive()->create()))->assertNotFound();
    }

    public function test_reporter_can_cancel_pending_complaint(): void
    {
        $complaint = Complaint::factory()->create();

        $this->actingAs($complaint->user)
            ->patch(route('pengaduan.cancel', $complaint), ['reason' => 'Sudah diperbaiki warga sendiri.'])
            ->assertRedirect(route('pengaduan.show', $complaint));

        $complaint->refresh();
        $this->assertSame(ComplaintStatus::Dibatalkan, $complaint->status);
        $this->assertNotNull($complaint->resolved_at);
        $this->assertSame(ComplaintStatus::Dibatalkan, $complaint->statusHistories()->sole()->to_status);
    }

    public function test_complaint_in_progress_or_owned_by_someone_else_cannot_be_cancelled(): void
    {
        $inProgress = Complaint::factory()->withStatus(ComplaintStatus::Diproses)->create();
        $pending = Complaint::factory()->create();

        $this->actingAs($inProgress->user)->patch(route('pengaduan.cancel', $inProgress))->assertForbidden();
        $this->actingAs($inProgress->user)->patch(route('pengaduan.cancel', $pending))->assertForbidden();
    }

    public function test_reporter_rates_finished_complaint_only_once(): void
    {
        $complaint = Complaint::factory()->withStatus(ComplaintStatus::Selesai)->create();

        $this->actingAs($complaint->user)
            ->post(route('pengaduan.rating', $complaint), ['rating' => 4, 'feedback' => 'Cepat ditangani.'])
            ->assertRedirect(route('pengaduan.show', $complaint));

        $this->assertSame(4, $complaint->refresh()->rating);

        $this->actingAs($complaint->user)
            ->post(route('pengaduan.rating', $complaint), ['rating' => 1])
            ->assertForbidden();
    }

    public function test_unfinished_complaint_cannot_be_rated(): void
    {
        $complaint = Complaint::factory()->create();

        $this->actingAs($complaint->user)
            ->post(route('pengaduan.rating', $complaint), ['rating' => 5])
            ->assertForbidden();
    }

    public function test_citizen_cannot_view_another_citizens_complaint(): void
    {
        $complaint = Complaint::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('pengaduan.show', $complaint))
            ->assertForbidden();
    }
}
