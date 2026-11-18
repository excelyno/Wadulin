<?php

namespace Tests\Feature;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EscalateComplaintsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_open_overdue_complaints_are_escalated(): void
    {
        $overdue = Complaint::factory()->create(['due_at' => now()->subHour()]);
        $onTime = Complaint::factory()->create(['due_at' => now()->addDay()]);
        $finished = Complaint::factory()->withStatus(ComplaintStatus::Selesai)->create(['due_at' => now()->subDay()]);

        $this->artisan('complaints:escalate')
            ->expectsOutput('1 pengaduan dieskalasi ke kabupaten.')
            ->assertSuccessful();

        $this->assertNotNull($overdue->refresh()->escalated_at);
        $this->assertNull($onTime->refresh()->escalated_at);
        $this->assertNull($finished->refresh()->escalated_at);
    }

    public function test_already_escalated_complaints_keep_their_timestamp(): void
    {
        $complaint = Complaint::factory()->escalated()->create();
        $originalEscalation = $complaint->escalated_at;

        $this->artisan('complaints:escalate')->expectsOutput('0 pengaduan dieskalasi ke kabupaten.');

        $this->assertTrue($complaint->refresh()->escalated_at->equalTo($originalEscalation));
    }
}
