# WADULIN — Wadah Aduan Digital Kabupaten Jember

Aplikasi pengaduan masyarakat. Warga melaporkan masalah layanan publik (jalan rusak, lampu jalan mati, sampah, dan sejenisnya). Petugas kecamatan menindaklanjuti dalam batas waktu (SLA). Bila terlambat, pengaduan otomatis dieskalasi ke kabupaten.

Dibangun dengan Laravel 13, Tailwind CSS 4, dan Leaflet (OpenStreetMap).

## Fitur

**Publik (tanpa login)**
- Beranda dengan statistik asli dan aduan terbaru
- Lacak tiket dengan kode `WDL-2026-000123`. Nama pelapor dan catatan internal tidak ditampilkan.
- Peta aduan
- Pengumuman dan halaman Bantuan (FAQ)
- Pindai QR di fasilitas umum (`/qr/{kode}`). Form aduan langsung terisi lokasinya.

**Warga**
- Buat pengaduan: kategori, titik di peta, kronologi, 1–3 foto bukti, opsi sembunyikan nama
- Dashboard: ringkasan status dan notifikasi dari petugas
- Batalkan pengaduan selama statusnya masih *Pending*
- Beri penilaian 1–5 setelah pengaduan *Selesai*

**Admin Kecamatan**
- Antrian pengaduan di kecamatannya, dengan filter status, kategori, SLA, dan pencarian
- Ubah status: Pending → Diproses → Selesai, atau Ditolak (alasan wajib). Bisa unggah foto hasil.
- Disposisi ke kecamatan lain. SLA dimulai ulang.
- Kelola titik QR di kecamatannya

**Admin Kabupaten**
- Semua fitur di atas untuk seluruh kecamatan. Ubah status hanya untuk pengaduan yang tereskalasi.
- Dashboard peringkat kecamatan dan daftar eskalasi
- Kelola kategori, akun petugas, pengumuman, dan FAQ

## Kebutuhan

- PHP 8.3+ dengan ekstensi `pdo_sqlite`, `fileinfo`, `mbstring`
- Composer
- Node.js 20+ dan npm

## Instalasi

```bash
composer run setup          # install dependensi, buat .env, generate key, migrate, build aset
php artisan db:seed         # isi data demo (lihat bagian Akun demo)
php artisan storage:link    # agar foto upload bisa tampil
composer run dev            # jalankan server + Vite
```

Buka `http://localhost:8000`.

Untuk mengulang database dari nol beserta data demo:

```bash
php artisan migrate:fresh --seed
```

> Perintah ini **menghapus semua data** di database lokal.

## Akun demo

Dibuat oleh `DatabaseSeeder`. Semua akun memakai kata sandi **`password`**.

| Peran | Email | Keterangan |
| --- | --- | --- |
| Admin Kabupaten | `kabupaten@wadulin.test` | Akses semua kecamatan, eskalasi, dan pengaturan |
| Admin Kecamatan | `patrang@wadulin.test` | Petugas Kecamatan Patrang |
| Warga | `warga@wadulin.test` | Siti Aminah, sudah punya beberapa pengaduan |

Seeder juga membuat **10 akun warga acak** (nama dan email dari Faker, kata sandi tetap `password`). Email mereka bisa dilihat di tabel `users`.

Data demo lain yang ikut dibuat:

- 31 kecamatan di Kabupaten Jember dan 11 kategori pengaduan
- 60 pengaduan dengan status campuran. Sekitar sepertiganya di Kecamatan Patrang agar panel petugas demo terisi. Pengaduan terbuka yang lewat SLA langsung ditandai tereskalasi.
- 1 titik QR (Taman Kota Patrang), 1 pengumuman, dan 6 FAQ

Tabel akun demo juga tampil di halaman login, tetapi **hanya bila `APP_ENV=local`**.

> Jangan jalankan seeder demo di server produksi.

## Konfigurasi

| Variabel `.env` | Bawaan | Fungsi |
| --- | --- | --- |
| `WADULIN_SLA_WORKING_DAYS` | `3` | Batas hari kerja (Senin–Jumat) sebelum pengaduan dieskalasi |

## Eskalasi otomatis

Perintah `complaints:escalate` menandai pengaduan terbuka yang melewati batas SLA. Perintah ini terjadwal tiap jam. Agar berjalan, aktifkan scheduler:

```bash
php artisan schedule:work                       # saat development
* * * * * php /path/ke/proyek/artisan schedule:run   # cron di server
```

Bisa juga dijalankan manual dengan `php artisan complaints:escalate`.

## Tes

```bash
php artisan test --compact
```

Tes memakai SQLite in-memory, jadi tidak menyentuh database lokal.

## Struktur singkat

| Lokasi | Isi |
| --- | --- |
| `app/Models/Complaint.php` | Kode tiket, perhitungan SLA, transisi status, disposisi, pembatalan |
| `app/Enums/ComplaintStatus.php` | Status dan transisi yang diizinkan |
| `app/Policies/` | Siapa boleh melihat, mengubah, membatalkan, atau menilai |
| `app/Http/Controllers/Admin/` | Panel kecamatan dan kabupaten |
| `routes/web.php` | Rute publik, warga (`role:masyarakat`), dan panel (`/kecamatan`, `/kabupaten`) |
| `resources/css/app.css` | Token warna dan komponen UI (`btn`, `panel`, `field-input`, dan lainnya) |

## Catatan

- Peta memakai tile OpenStreetMap dan Leaflet dari CDN unpkg. Gambar QR dibuat di browser dengan `qrcodejs` dari cdnjs. Keduanya butuh koneksi internet.
- Foto disimpan di disk `public` (`storage/app/public/complaints/{kode-tiket}`).
