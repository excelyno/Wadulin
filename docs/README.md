# Dokumentasi Proyek WADULIN

Selamat datang! Dokumentasi ini ditulis untuk kamu yang baru bergabung, khususnya anak magang yang baru belajar Laravel. Tujuannya supaya kamu paham:

1. **Apa** yang dikerjakan aplikasi ini (fitur dan aturan bisnisnya).
2. **Di mana** kode untuk tiap fitur berada.
3. **Bagaimana** kode itu bekerja, langkah demi langkah.
4. **Kenapa** kode ditulis dengan cara itu, bukan cara lain.

Jangan buru-buru. Baca berurutan, buka file kode yang disebut, dan cocokkan dengan penjelasannya. Setiap rujukan kode ditulis seperti `app/Models/Complaint.php:178`, artinya file `app/Models/Complaint.php` baris 178. Nomor baris bisa bergeser kalau kodenya diubah. Kalau tidak cocok, cari nama fungsinya.

## Urutan baca

| No | Dokumen | Isi | Cocok dibaca saat |
| --- | --- | --- | --- |
| 1 | [Gambaran umum](01-gambaran-umum.md) | Apa itu WADULIN, siapa penggunanya, istilah penting, teknologi, struktur folder | Hari pertama |
| 2 | [Cara kerja request di Laravel](02-alur-request-laravel.md) | Perjalanan satu request dari browser sampai halaman tampil, lapis demi lapis | Hari pertama |
| 3 | [Database dan model](03-database-dan-model.md) | Semua tabel, kolom, relasi, diagram ERD, dan alasan desainnya | Hari kedua |
| 4 | [Status, SLA, dan eskalasi](04-status-sla-eskalasi.md) | Inti aturan bisnis: siklus hidup pengaduan | Hari kedua |
| 5 | [Fitur publik](05-fitur-publik.md) | Beranda, lacak tiket, peta, pengumuman, FAQ, scan QR | Hari ketiga |
| 6 | [Fitur warga](06-fitur-warga.md) | Daftar, masuk, buat pengaduan, batal, nilai, notifikasi | Hari ketiga |
| 7 | [Fitur petugas](07-fitur-petugas.md) | Panel kecamatan dan kabupaten: antrian, ubah status, disposisi, master data | Hari keempat |
| 8 | [Keamanan dan hak akses](08-keamanan-hak-akses.md) | Middleware peran, policy, validasi, throttle, privasi pelapor | Hari keempat |
| 9 | [Tampilan (frontend)](09-tampilan-frontend.md) | Blade, layout, komponen, Tailwind, peta Leaflet, QR | Hari kelima |
| 10 | [Testing](10-testing.md) | Cara tes ditulis, daftar tes, factory | Hari kelima |
| 11 | [Panduan kerja harian](11-panduan-kerja.md) | Perintah sehari-hari, contoh menambah fitur, kesalahan umum, keterbatasan yang diketahui | Simpan sebagai pegangan |

## Cara memakai dokumentasi ini

- **Buka kodenya berdampingan.** Dokumentasi ini sengaja tidak menyalin semua kode. Yang ditampilkan hanya potongan penting. Sisanya ada di file aslinya.
- **Jalankan aplikasinya.** Ikuti langkah di [README utama](../README.md), lalu masuk dengan akun demo. Coba setiap fitur sambil membaca bab yang sesuai.
- **Pakai tes sebagai contoh.** File di `tests/Feature/` adalah "contoh pemakaian" yang pasti benar, karena dijalankan otomatis.
- **Kalau bingung, cari dari route.** Hampir semua fitur bisa dilacak dari `routes/web.php`: URL → controller → model → view.

## Kalau kode berubah

Dokumentasi ini ikut disimpan di repo supaya bisa diperbarui bersama kode. Kalau kamu mengubah perilaku fitur, ubah juga bab yang membahasnya di pull request yang sama.
