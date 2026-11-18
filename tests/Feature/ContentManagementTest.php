<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\District;
use App\Models\Faq;
use App\Models\QrLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_district_officer_creates_qr_point_in_own_district(): void
    {
        $district = District::factory()->create();
        $officer = User::factory()->adminKecamatan($district)->create();

        $this->actingAs($officer)->post(route('kecamatan.qr.store'), [
            'name' => 'Pasar Tanjung',
            'latitude' => -8.17,
            'longitude' => 113.70,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $qrLocation = QrLocation::sole();
        $this->assertTrue($qrLocation->district->is($district));
        $this->assertSame(10, strlen($qrLocation->code));

        $this->actingAs($officer)->get(route('kecamatan.qr.index'))->assertOk()->assertSee('Pasar Tanjung');
    }

    public function test_district_officer_cannot_edit_another_districts_qr_point(): void
    {
        $qrLocation = QrLocation::factory()->create();

        $this->actingAs(User::factory()->adminKecamatan()->create())
            ->put(route('kecamatan.qr.update', $qrLocation), ['name' => 'Diubah', 'latitude' => -8.17, 'longitude' => 113.70])
            ->assertForbidden();
    }

    public function test_regency_must_pick_district_for_qr_point(): void
    {
        $this->actingAs(User::factory()->adminKabupaten()->create())
            ->post(route('kabupaten.qr.store'), ['name' => 'Alun-alun', 'latitude' => -8.17, 'longitude' => 113.70])
            ->assertSessionHasErrors('district_id');
    }

    public function test_regency_publishes_announcement_with_cover(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->adminKabupaten()->create())->post(route('kabupaten.pengumuman.store'), [
            'title' => 'Libur layanan Idul Fitri',
            'body' => 'Layanan verifikasi pengaduan libur selama cuti bersama Idul Fitri.',
            'cover' => UploadedFile::fake()->create('sampul.jpg', 120, 'image/jpeg'),
            'is_published' => '1',
        ])->assertSessionHasNoErrors();

        $announcement = Announcement::sole();
        $this->assertSame('libur-layanan-idul-fitri', $announcement->slug);
        $this->assertNotNull($announcement->published_at);
        Storage::disk('public')->assertExists($announcement->cover_path);

        $this->get(route('pengumuman.show', $announcement))->assertOk();
    }

    public function test_unpublishing_announcement_hides_it(): void
    {
        $admin = User::factory()->adminKabupaten()->create();
        $announcement = Announcement::factory()->create(['author_id' => $admin->id]);

        $this->actingAs($admin)->put(route('kabupaten.pengumuman.update', $announcement), [
            'title' => $announcement->title,
            'body' => $announcement->body,
            'is_published' => '0',
        ])->assertSessionHasNoErrors();

        $this->get(route('pengumuman.show', $announcement))->assertNotFound();
        $this->actingAs($admin)->get(route('kabupaten.pengumuman.index'))->assertOk();
    }

    public function test_regency_manages_faq(): void
    {
        $admin = User::factory()->adminKabupaten()->create();

        $this->actingAs($admin)->post(route('kabupaten.faq.store'), [
            'question' => 'Apakah melapor dipungut biaya?',
            'answer' => 'Tidak, layanan ini gratis.',
            'is_published' => '1',
        ])->assertSessionHasNoErrors();

        $faq = Faq::sole();
        $this->assertSame(0, $faq->sort_order);

        $this->actingAs($admin)->put(route('kabupaten.faq.update', $faq), [
            'question' => $faq->question, 'answer' => $faq->answer, 'sort_order' => 3, 'is_published' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertFalse($faq->refresh()->is_published);

        $this->actingAs($admin)->get(route('kabupaten.faq.index'))->assertOk();
        $this->actingAs($admin)->delete(route('kabupaten.faq.destroy', $faq));
        $this->assertModelMissing($faq);
    }
}
