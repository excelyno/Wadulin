<?php

namespace Tests\Feature;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_tracking_shows_progress_without_reporter_identity(): void
    {
        $reporter = User::factory()->create(['name' => 'Rahasia Pelapor']);
        $complaint = Complaint::factory()->for($reporter)->create(['title' => 'Lampu jalan mati di gang']);
        $complaint->statusHistories()->create(['changed_by' => $reporter->id, 'to_status' => ComplaintStatus::Pending, 'note' => 'Catatan publik']);
        $complaint->statusHistories()->create(['changed_by' => $reporter->id, 'to_status' => ComplaintStatus::Pending, 'note' => 'Catatan internal', 'is_public' => false]);

        $this->get(route('lacak', ['kode' => strtolower($complaint->ticket_code)]))
            ->assertOk()
            ->assertSee('Lampu jalan mati di gang')
            ->assertSee('Catatan publik')
            ->assertDontSee('Catatan internal')
            ->assertDontSee('Rahasia Pelapor');
    }

    public function test_unknown_ticket_shows_not_found_message(): void
    {
        $this->get(route('lacak', ['kode' => 'WDL-2026-999999']))
            ->assertOk()
            ->assertSee('Kode tiket tidak ditemukan');
    }
}
