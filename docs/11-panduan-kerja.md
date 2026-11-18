# 11. Panduan Kerja Sehari-hari

Bab terakhir ini berisi hal praktis: perintah yang sering dipakai, langkah menambah fitur, kesalahan yang sering terjadi, dan daftar keterbatasan yang sudah diketahui.

## 11.1 Menyiapkan proyek pertama kali

Langkah lengkap ada di [README utama](../README.md). Ringkasnya:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed     # buat tabel + data demo
php artisan storage:link             # supaya foto bisa dibuka di browser
```

## 11.2 Perintah harian

| Perintah | Kapan dipakai |
| --- | --- |
| `composer run dev` | Menjalankan server, Vite, dan lainnya sekaligus saat development |
| `npm run dev` / `npm run build` | Hanya frontend (Tailwind/Vite) |
| `php artisan schedule:work` | Menjalankan scheduler (eskalasi tiap jam) saat development |
| `php artisan complaints:escalate` | Menjalankan eskalasi sekali secara manual |
| `php artisan migrate:fresh --seed` | **Hapus semua data** lalu buat ulang dengan data demo |
| `php artisan route:list --except-vendor` | Melihat semua route aplikasi |
| `php artisan test --compact` | Menjalankan semua tes |
| `vendor/bin/pint --dirty --format agent` | Merapikan format file PHP yang kamu ubah |
| `php artisan tinker` | Mencoba kode PHP dengan akses ke model |

> **Hati-hati dengan `migrate:fresh`.** Perintah ini menghapus **semua** tabel. Hanya untuk database development, jangan pernah di server produksi.

## 11.3 Di mana saya harus menulis kode ini?

| Kode tentang... | Tempatnya | Contoh di proyek |
| --- | --- | --- |
| URL baru | `routes/web.php` | — |
| Menerima request dan memilih view | Controller (`app/Http/Controllers/`) | `ComplaintController` |
| Aturan isian form | Form Request (`app/Http/Requests/`) | `StoreComplaintRequest` |
| "Siapa boleh melakukan apa pada data ini" | Policy (`app/Policies/`) | `ComplaintPolicy` |
| Aturan bisnis yang mengubah data | Method di model | `Complaint::transitionTo()`, `disposeTo()` |
| Filter query yang dipakai berulang | Scope di model | `visibleTo()`, `overdue()`, `published()` |
| Daftar nilai tetap + perilakunya | Enum (`app/Enums/`) | `ComplaintStatus::officerTransitions()` |
| Angka yang bisa diatur | `config/wadulin.php` + `.env` | `sla_working_days` |
| Tugas terjadwal | `routes/console.php` | `complaints:escalate` |
| Pesan ke pengguna | Notification (`app/Notifications/`) | `ComplaintUpdated` |
| Tampilan | View (`resources/views/`) | — |
| Potongan tampilan yang dipakai di banyak halaman | Komponen atau partial | `x-status-badge`, `_card` |

**Aturan umum:** controller sebaiknya tipis. Tugasnya hanya menghubungkan request → aturan bisnis → response. Kalau logikanya mulai panjang atau dipakai di lebih dari satu tempat, pindahkan ke model, enum, atau scope.

## 11.4 Contoh: menambah fitur baru langkah demi langkah

Misalnya diminta: **"Petugas bisa menandai pengaduan sebagai prioritas."**

1. **Baca aturan proyek.** Cek `.ai/rules/` (kalau ada) dan file sejenis yang sudah ada. Ikuti pola yang sudah dipakai, jangan membuat cara baru.
2. **Database.** `php artisan make:migration add_is_priority_to_complaints_table --no-interaction`, isi `$table->boolean('is_priority')->default(false);`. Jalankan `php artisan migrate`.
3. **Model.** Tambahkan `is_priority` ke `#[Fillable]` dan `'is_priority' => 'boolean'` ke `casts()` di `Complaint`.
4. **Hak akses.** Siapa yang boleh? Tambahkan method `markPriority()` di `ComplaintPolicy`. Bisa memakai ulang aturan `updateStatus` kalau sama.
5. **Form Request.** `php artisan make:request MarkComplaintPriorityRequest --no-interaction`. `authorize()` memanggil policy, `rules()` memvalidasi `is_priority` boolean.
6. **Controller.** `php artisan make:controller Admin/ComplaintPriorityController --invokable --no-interaction`. Isinya cukup `update` lalu `back()->with('success', ...)`.
7. **Route.** Tambahkan ke `$adminComplaintRoutes` di `routes/web.php`, supaya tersedia di kedua panel.
8. **Tampilan.** Tambahkan form di `admin/complaints/show.blade.php`, dibungkus `@can('markPriority', $complaint)`. Tampilkan label "Prioritas" di daftar antrian.
9. **Tes.** `php artisan make:test --phpunit ComplaintPriorityTest`. Minimal: petugas yang berhak berhasil, petugas kecamatan lain ditolak, warga ditolak.
10. **Rapikan dan jalankan.** `vendor/bin/pint --dirty --format agent`, lalu `php artisan test --compact`.
11. **Dokumentasi.** Perbarui bab yang relevan di folder `docs/` ini.

## 11.5 Kesalahan yang sering terjadi

| Kesalahan | Akibat | Yang benar |
| --- | --- | --- |
| Query daftar pengaduan admin tanpa `visibleTo($user)` | Data kecamatan lain bocor | Selalu mulai dengan `Complaint::query()->visibleTo($user)` |
| `->where(...)->orWhere(...)` tanpa dibungkus | Filter kecamatan terlewati | Bungkus dengan `->where(fn ($q) => $q->where(...)->orWhere(...))` |
| Mengubah `status` langsung: `$complaint->update(['status' => ...])` | Riwayat tidak tercatat, aturan transisi terlewati | Pakai `transitionTo()` atau `cancelBy()` |
| Menyimpan `$request->all()` | Field berbahaya ikut tersimpan | `$request->validated()` atau `$request->safe()->only([...])` |
| Hanya menyembunyikan tombol di view | Aksi tetap bisa dikirim lewat request langsung | Periksa izin di Form Request/controller juga |
| Memanggil `env()` di luar folder `config/` | Bernilai `null` setelah `config:cache` | Pakai `config('...')` |
| Lupa `with([...])` saat menampilkan daftar | Ratusan query (N+1) | Eager load relasi yang dipakai di view |
| Mencetak data pengguna dengan `{!! !!}` | XSS | Pakai `{{ }}` |
| Menambah class Tailwind baru tapi tampilan tidak berubah | Class belum di-*build* | `npm run dev` atau `npm run build` |
| Menulis route `/pengaduan/{complaint}` sebelum `/pengaduan/buat` | `/pengaduan/buat` dianggap kode tiket → 404 | Route statis di atas route berparameter |
| Lupa `copy()` saat menghitung tanggal dengan Carbon | Tanggal asli ikut berubah | `$date->copy()->addWeekdays(...)` |
| Menghapus kategori/petugas lewat database | Foreign key error atau jejak audit hilang | Nonaktifkan dengan `is_active = false` |

## 11.6 Mencari tahu sendiri

- **Route mana memanggil controller apa?** `php artisan route:list --except-vendor --path=pengaduan`
- **Struktur tabel?** Buka file migration di `database/migrations/`, atau `wadulin_erd.sql`.
- **Isi konfigurasi?** `php artisan config:show wadulin`
- **Error terakhir?** `storage/logs/laravel.log`
- **Dokumentasi Laravel** (versi 13): https://laravel.com/docs

Cara membaca kode yang belum kamu kenal: mulai dari **route** → buka **controller**-nya → lihat **Form Request** dan **policy** yang dipakai → ikuti method **model** yang dipanggil → lihat **view**-nya. Urutan ini sama dengan alur request di [bab 2](02-alur-request-laravel.md).

## 11.7 Keterbatasan yang diketahui

Hal-hal berikut **disadari** dan belum dikerjakan. Kalau kamu mendapat tugas di area ini, mulailah dari sini.

| Keterbatasan | Penjelasan | Arah perbaikan |
| --- | --- | --- |
| Kode tiket bisa bentrok | Dua pengiriman di waktu yang sama bisa mendapat nomor sama. Kolom unik akan menolak salah satunya (error 500). | Tabel penghitung dengan *lock*, atau coba ulang saat bentrok |
| SLA belum menghitung hari libur | `addWeekdays` hanya melewati Sabtu dan Minggu | Tabel hari libur nasional |
| Batas wilayah berbentuk kotak | Koordinat dicek dengan kotak kasar, bukan batas kabupaten sebenarnya | Isi `districts.boundary` dengan poligon dan cek titik di dalamnya |
| Belum ada lupa kata sandi | Tabel `password_reset_tokens` sudah ada, fiturnya belum | Fitur reset password bawaan Laravel |
| Notifikasi hanya di aplikasi | Saluran `database` saja | Tambah `mail` atau WhatsApp di `ComplaintUpdated::via()` |
| Pengumuman tidak bisa dihapus | Hanya bisa dijadikan draf | Tambah `destroy` + hapus file sampul |
| Kolom belum dipakai | `users.avatar_path`, `users.address`, `categories.icon` (tidak tampil), `districts.boundary` | Pakai atau hapus |
| Foto bukti bisa diakses lewat URL langsung | Disimpan di disk `public` | Pindah ke disk `local` + controller yang mengecek izin |
| Butuh internet untuk peta dan QR | Leaflet, tile OpenStreetMap, dan qrcodejs dimuat dari luar | Bundel library lewat npm |
| Statistik beranda dihitung di PHP | Lambat kalau data sudah sangat banyak | Hitung di SQL atau *cache* |
| `welcome.blade.php` tidak dipakai | Sisa bawaan Laravel | Hapus |

## 11.8 Aturan tim

- Ikuti gaya kode yang sudah ada: tipe parameter dan tipe kembalian wajib, kurung kurawal di setiap `if`, nama variabel yang jelas (`$isPublished`, bukan `$p`).
- Buat file dengan `php artisan make:...` supaya struktur dan namespace benar.
- Jangan menambah paket Composer/npm baru tanpa persetujuan.
- Jangan membuat folder utama baru tanpa persetujuan.
- Ikuti aturan Git tim (cabang, pesan commit, siapa yang boleh commit dan merge). Tanyakan ke pembimbing kalau belum jelas.
- Selesai mengerjakan: Pint, tes, perbarui `docs/`.

---

Selesai. Kalau ada bagian dokumentasi yang membingungkan atau sudah tidak sesuai dengan kode, perbaiki dan beri tahu tim. Dokumentasi yang usang lebih berbahaya daripada tidak ada dokumentasi.

Kembali ke [daftar isi](README.md).
