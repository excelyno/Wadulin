<?php

namespace Tests\Feature;

use App\Enums\ComplaintStatus;
use App\Models\Announcement;
use App\Models\Complaint;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_real_statistics_and_latest_announcement(): void
    {
        Complaint::factory()->count(3)->create();
        Announcement::factory()->create(['title' => 'Jadwal perbaikan jalan pekan ini']);

        $this->get(route('beranda'))
            ->assertOk()
            ->assertSee('Jadwal perbaikan jalan pekan ini')
            ->assertDontSee('12.480');
    }

    public function test_public_map_lists_complaints_without_reporter_identity(): void
    {
        $visible = Complaint::factory()->for(User::factory()->state(['name' => 'Nama Pelapor Rahasia']))->create();
        $cancelled = Complaint::factory()->withStatus(ComplaintStatus::Dibatalkan)->create();

        $this->get(route('peta'))
            ->assertOk()
            ->assertSee($visible->ticket_code)
            ->assertDontSee($cancelled->ticket_code)
            ->assertDontSee('Nama Pelapor Rahasia');
    }

    public function test_only_published_announcements_are_public(): void
    {
        $published = Announcement::factory()->create();
        $draft = Announcement::factory()->draft()->create();

        $this->get(route('pengumuman.index'))
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee($draft->title);

        $this->get(route('pengumuman.show', $published))->assertOk()->assertSee($published->title);
        $this->get(route('pengumuman.show', $draft))->assertNotFound();
    }

    public function test_faq_page_hides_unpublished_entries(): void
    {
        $shown = Faq::factory()->create();
        $hidden = Faq::factory()->hidden()->create();

        $this->get(route('faq'))
            ->assertOk()
            ->assertSee($shown->question)
            ->assertDontSee($hidden->question);
    }
}
