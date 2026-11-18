# 10. Testing

Proyek ini punya **50 tes otomatis** (PHPUnit). Tes membuktikan bahwa fitur bekerja dan aturan hak akses tidak bocor. Setiap kali kamu mengubah kode, jalankan tes supaya tahu kalau ada yang rusak.

## 10.1 Kenapa tes itu penting di proyek ini

Banyak aturan WADULIN tidak terlihat di layar, misalnya "petugas Patrang tidak boleh membuka pengaduan Ambulu" atau "catatan internal tidak boleh masuk notifikasi". Kalau aturan seperti ini rusak, tidak ada yang sadar sampai data bocor. Tes menjaganya secara otomatis.

## 10.2 Menjalankan tes

```bash
php artisan test --compact                                   # semua tes
php artisan test --compact tests/Feature/TrackingTest.php    # satu file
php artisan test --compact --filter=test_rejecting_requires_a_reason   # satu tes
```

Tes memakai database **SQLite di memori** (lihat `phpunit.xml`: `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`). Artinya:

- Tes **tidak menyentuh database development kamu**. Aman dijalankan kapan saja.
- Tidak perlu menyiapkan database apa pun untuk menjalankan tes.

`phpunit.xml` juga mengatur `MAIL_MAILER=array`, `QUEUE_CONNECTION=sync`, `CACHE_STORE=array`, dan `BCRYPT_ROUNDS=4` (hash kata sandi lebih cepat), supaya tes cepat dan tidak mengirim apa pun ke luar.

## 10.3 Daftar file tes

Semua ada di `tests/Feature/`. Nama tes ditulis sebagai kalimat yang menjelaskan perilaku, jadi daftar ini juga berfungsi sebagai **spesifikasi aplikasi**.

### `AuthenticationTest` — login dan daftar

| Tes | Membuktikan |
| --- | --- |
| `test_public_registration_always_creates_a_citizen` | Mengirim `role=admin_kabupaten` saat mendaftar tetap menghasilkan akun warga |
| `test_each_role_lands_on_its_own_home_after_login` | Warga → `/dashboard`, kecamatan → `/kecamatan`, kabupaten → `/kabupaten` |
| `test_inactive_account_cannot_log_in` | Akun nonaktif ditolak |
| `test_citizen_cannot_open_admin_panel` | Warga mendapat 403 di panel admin |

### `ComplaintSubmissionTest` — fitur warga

| Tes | Membuktikan |
| --- | --- |
| `test_citizen_submits_complaint_with_ticket_sla_and_photo` | Kode tiket `WDL-{tahun}-000001`, status Pending, `due_at` di masa depan, 1 riwayat, foto tersimpan |
| `test_complaint_requires_a_photo_and_location_inside_jember` | Tanpa foto dan koordinat Jakarta → error validasi |
| `test_guest_is_sent_to_login_before_reporting` | Tamu diarahkan ke login |
| `test_scanned_qr_prefills_the_form_and_is_linked_to_the_complaint` | Alur QR lengkap: scan → form terisi → pengaduan tersimpan dengan `qr_location_id` |
| `test_inactive_qr_cannot_be_scanned` | QR nonaktif → 404 |
| `test_reporter_can_cancel_pending_complaint` | Pembatalan berhasil, riwayat tercatat |
| `test_complaint_in_progress_or_owned_by_someone_else_cannot_be_cancelled` | Pengaduan Diproses atau milik orang lain → 403 |
| `test_reporter_rates_finished_complaint_only_once` | Penilaian kedua ditolak |
| `test_unfinished_complaint_cannot_be_rated` | Belum Selesai → tidak bisa dinilai |
| `test_citizen_cannot_view_another_citizens_complaint` | Pengaduan orang lain → 403 |

### `ComplaintStatusUpdateTest` — ubah status

| Tes | Membuktikan |
| --- | --- |
| `test_district_officer_processes_complaint_and_reporter_is_notified` | Status berubah, riwayat tercatat, notifikasi terkirim |
| `test_internal_note_is_not_leaked_in_notification` | Catatan `is_public=false` tidak ada di isi notifikasi |
| `test_reporter_sees_notification_on_dashboard_and_can_mark_it_read` | Notifikasi tampil dan bisa ditandai dibaca |
| `test_rejecting_requires_a_reason` | Tolak tanpa catatan → error |
| `test_status_cannot_skip_steps` | Pending → Selesai ditolak |
| `test_officer_of_another_district_cannot_update` | Kecamatan lain → 403 |
| `test_regency_may_only_act_on_escalated_complaints` | Kabupaten ditolak sebelum eskalasi, diizinkan sesudahnya |

### `ComplaintDispositionTest` — disposisi

| Tes | Membuktikan |
| --- | --- |
| `test_district_officer_hands_complaint_to_another_district` | Kecamatan berubah, SLA diulang, catatan disposisi ada, notifikasi terkirim |
| `test_disposition_needs_a_reason_and_a_different_district` | Alasan kosong / kecamatan sama → error |
| `test_complaint_already_in_progress_cannot_be_handed_over` | Pengaduan Diproses tidak bisa didisposisi kecamatan |

### `AdminComplaintAccessTest` — batas data petugas

| Tes | Membuktikan |
| --- | --- |
| `test_district_officer_only_sees_own_district_queue` | Antrian dan detail hanya kecamatan sendiri |
| `test_regency_officer_sees_every_complaint` | Kabupaten melihat semua |
| `test_dashboards_render_for_both_panels` | Dashboard kedua panel tampil tanpa error |
| `test_district_officer_cannot_open_regency_only_pages` | Kecamatan tidak bisa membuka eskalasi, kategori, petugas |

### `EscalateComplaintsCommandTest` — perintah eskalasi

| Tes | Membuktikan |
| --- | --- |
| `test_only_open_overdue_complaints_are_escalated` | Hanya yang terbuka dan terlambat yang dieskalasi |
| `test_already_escalated_complaints_keep_their_timestamp` | Menjalankan ulang tidak mengubah waktu eskalasi lama |

### `MasterDataTest` — kategori dan petugas

| Tes | Membuktikan |
| --- | --- |
| `test_regency_creates_and_deactivates_category` | Tambah dan nonaktifkan kategori |
| `test_duplicate_category_name_is_rejected` | Nama kembar ditolak |
| `test_regency_creates_district_officer` | Akun petugas kecamatan dibuat |
| `test_officer_can_be_deactivated_but_admin_cannot_deactivate_self` | Aturan nonaktifkan |
| `test_citizen_account_cannot_be_edited_from_officer_page` | Akun warga → 404 |

### `ContentManagementTest` — QR, pengumuman, FAQ

| Tes | Membuktikan |
| --- | --- |
| `test_district_officer_creates_qr_point_in_own_district` | Titik QR otomatis masuk kecamatan petugas |
| `test_district_officer_cannot_edit_another_districts_qr_point` | 403 untuk QR kecamatan lain |
| `test_regency_must_pick_district_for_qr_point` | Kabupaten wajib memilih kecamatan |
| `test_regency_publishes_announcement_with_cover` | Pengumuman + sampul tersimpan dan tampil publik |
| `test_unpublishing_announcement_hides_it` | Draf → 404 di publik |
| `test_regency_manages_faq` | Tambah, ubah, hapus FAQ |

### `PublicPagesTest` dan `TrackingTest` — halaman publik

| Tes | Membuktikan |
| --- | --- |
| `test_home_page_shows_real_statistics_and_latest_announcement` | Angka beranda berasal dari database |
| `test_public_map_lists_complaints_without_reporter_identity` | Nama pelapor tidak ada di halaman peta |
| `test_only_published_announcements_are_public` | Draf tidak muncul |
| `test_faq_page_hides_unpublished_entries` | FAQ tersembunyi tidak muncul |
| `test_public_tracking_shows_progress_without_reporter_identity` | Lacak menampilkan status, tidak menampilkan nama pelapor dan catatan internal |
| `test_unknown_ticket_shows_not_found_message` | Kode salah → pesan tidak ditemukan |

### `ExampleTest`

`test_demo_seeder_builds_a_usable_dataset` menjalankan `DatabaseSeeder` lalu memeriksa ada 31 kecamatan, akun `kabupaten@wadulin.test`, 1 titik QR, 1 pengumuman, dan beranda menampilkan pengumuman demo. Kalau seeder rusak, tes ini yang pertama gagal.

## 10.4 Anatomi satu tes

Contoh dari `tests/Feature/ComplaintSubmissionTest.php`:

```php
class ComplaintSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_submits_complaint_with_ticket_sla_and_photo(): void
    {
        // 1. SIAPKAN (arrange)
        Storage::fake('public');
        $citizen = User::factory()->create();

        // 2. LAKUKAN (act)
        $response = $this->actingAs($citizen)->post(route('pengaduan.store'), $this->validPayload());

        // 3. PERIKSA (assert)
        $complaint = Complaint::sole();
        $response->assertRedirect(route('pengaduan.show', $complaint));
        $this->assertSame('WDL-'.now()->year.'-000001', $complaint->ticket_code);
        $this->assertSame(ComplaintStatus::Pending, $complaint->status);
        $this->assertTrue($complaint->due_at->isFuture());
        $this->assertCount(1, $complaint->statusHistories);
        Storage::disk('public')->assertExists($complaint->attachments->sole()->file_path);
    }
}
```

Pola **Siapkan → Lakukan → Periksa** dipakai di semua tes.

| Alat | Fungsi |
| --- | --- |
| `use RefreshDatabase` | Setiap tes mulai dengan database kosong yang baru di-*migrate*. Tes tidak saling memengaruhi. |
| `User::factory()->create()` | Membuat data palsu lewat factory ([bab 3.7](03-database-dan-model.md#37-seeder-dan-factory)) |
| `$this->actingAs($user)` | Berpura-pura login sebagai user itu |
| `$this->post(route(...), [...])` | Mengirim request seperti browser, melewati middleware, validasi, dan controller asli |
| `Complaint::sole()` | Ambil satu-satunya baris. Gagal kalau ada 0 atau lebih dari 1. Sekaligus memastikan tidak ada data ganda. |
| `Storage::fake('public')` | Disk palsu. File tidak benar-benar ditulis ke folder `storage/`. |
| `Notification::fake()` + `Notification::assertSentTo(...)` | Menangkap notifikasi tanpa menyimpannya, lalu memeriksa apakah terkirim |
| `$this->artisan('complaints:escalate')->expectsOutput(...)` | Menjalankan perintah artisan dan memeriksa outputnya |
| `assertForbidden()` / `assertNotFound()` / `assertSessionHasErrors([...])` | Memeriksa 403 / 404 / error validasi |
| `assertSee('teks')` / `assertDontSee('teks')` | Memeriksa isi HTML. `assertDontSee` dipakai untuk membuktikan data rahasia tidak bocor. |

`validPayload()` di atas adalah method bantu yang mengembalikan data form yang valid. Setiap tes cukup menimpa field yang ingin diuji, misalnya `$this->validPayload(['photos' => []])`.

## 10.5 Catatan teknis

- **Foto palsu tanpa GD.** Tes memakai `UploadedFile::fake()->create('bukti.jpg', 120, 'image/jpeg')`, bukan `->image(...)`. Method `image()` butuh ekstensi PHP GD yang tidak selalu terpasang di komputer developer. `create()` dengan MIME `image/jpeg` cukup untuk lolos validasi.
- **Factory state** mempersingkat persiapan: `User::factory()->adminKecamatan($district)`, `Complaint::factory()->withStatus(ComplaintStatus::Diproses)`, `Complaint::factory()->escalated()`, `QrLocation::factory()->inactive()`.
- **Tes HTTP lebih berharga daripada tes unit di proyek ini**, karena kebanyakan aturan ada di gabungan middleware, Form Request, policy, dan controller. Tes HTTP menguji semuanya sekaligus.

## 10.6 Menulis tes baru

1. Buat file: `php artisan make:test --phpunit NamaFiturTest` (tanpa `Feature/` di nama).
2. Tambahkan `use RefreshDatabase;`.
3. Beri nama tes berupa kalimat perilaku: `test_officer_cannot_...`, `test_citizen_can_...`.
4. Untuk setiap aturan hak akses, tulis **dua** tes: yang berhak berhasil, dan yang tidak berhak ditolak.
5. Jalankan file itu saja dulu, lalu semua tes sebelum selesai.

Lanjut ke [bab 11: Panduan kerja](11-panduan-kerja.md).
