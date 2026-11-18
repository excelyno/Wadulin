<?php

namespace Database\Seeders;

use App\Enums\ComplaintStatus;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\District;
use App\Models\QrLocation;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([DistrictSeeder::class, CategorySeeder::class, FaqSeeder::class]);

        $patrang = District::where('name', 'Patrang')->firstOrFail();

        // Akun demo (Lampiran B.3), password: "password".
        $kabupaten = User::factory()->adminKabupaten()->create([
            'name' => 'Admin Kabupaten Jember',
            'email' => 'kabupaten@wadulin.test',
        ]);

        User::factory()->adminKecamatan($patrang)->create([
            'name' => 'Petugas Kec. Patrang',
            'email' => 'patrang@wadulin.test',
        ]);

        $warga = User::factory()->create([
            'name' => 'Siti Aminah',
            'email' => 'warga@wadulin.test',
        ]);

        $this->seedComplaints($warga, $patrang, $kabupaten);

        QrLocation::create([
            'district_id' => $patrang->id,
            'code' => QrLocation::generateCode(),
            'name' => 'Taman Kota Patrang',
            'address' => 'Jl. Kaliurang, Patrang',
            'latitude' => -8.1436,
            'longitude' => 113.7187,
            'created_by' => $kabupaten->id,
        ]);

        Announcement::create([
            'title' => 'WADULIN resmi diluncurkan',
            'slug' => 'wadulin-resmi-diluncurkan',
            'excerpt' => 'Kini warga Jember bisa melapor masalah layanan publik langsung dari ponsel.',
            'body' => 'Pemerintah Kabupaten Jember meluncurkan WADULIN, wadah aduan digital untuk seluruh warga.

Setiap pengaduan mendapat kode tiket, ditangani petugas kecamatan, dan dipantau kabupaten bila melewati batas waktu.',
            'is_published' => true,
            'published_at' => now(),
            'author_id' => $kabupaten->id,
        ]);
    }

    /**
     * Seed sample complaints with a status history that matches their status.
     */
    private function seedComplaints(User $warga, District $patrang, User $actor): void
    {
        $districts = District::all();
        $categories = Category::all();
        $citizens = User::factory()->count(10)->create()->push($warga);

        $sequence = 0;

        foreach (range(1, 60) as $i) {
            $status = fake()->randomElement([
                ComplaintStatus::Pending, ComplaintStatus::Pending, ComplaintStatus::Diproses,
                ComplaintStatus::Selesai, ComplaintStatus::Selesai, ComplaintStatus::Ditolak,
            ]);

            // Sekitar sepertiga pengaduan berada di Patrang agar panel demo terisi.
            $district = $i % 3 === 0 ? $patrang : $districts->random();

            $complaint = Complaint::factory()
                ->withStatus($status)
                ->create([
                    'ticket_code' => 'WDL-'.now()->year.'-'.str_pad((string) ++$sequence, 6, '0', STR_PAD_LEFT),
                    'user_id' => $citizens->random()->id,
                    'category_id' => $categories->random()->id,
                    'district_id' => $district->id,
                    'rating' => $status === ComplaintStatus::Selesai ? fake()->optional(0.6)->numberBetween(3, 5) : null,
                ]);

            $complaint->statusHistories()->create([
                'changed_by' => $complaint->user_id,
                'from_status' => null,
                'to_status' => ComplaintStatus::Pending,
                'created_at' => $complaint->created_at,
            ]);

            if ($status !== ComplaintStatus::Pending) {
                $complaint->statusHistories()->create([
                    'changed_by' => $actor->id,
                    'from_status' => ComplaintStatus::Pending,
                    'to_status' => $status,
                    'note' => $status === ComplaintStatus::Ditolak ? 'Lokasi bukan merupakan aset pemerintah daerah.' : null,
                    'created_at' => $complaint->created_at->addDay(),
                ]);
            }

            if ($status->isFinal()) {
                $complaint->update(['resolved_at' => $complaint->created_at->addDays(2)]);
            } elseif ($complaint->due_at->isPast()) {
                $complaint->update(['escalated_at' => $complaint->due_at->addDay()]);
            }
        }
    }
}
