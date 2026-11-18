<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Seed the frequently asked questions shown on the public FAQ page.
     */
    public function run(): void
    {
        $faqs = [
            ['Bagaimana cara membuat pengaduan?', 'Daftar atau masuk sebagai warga, pilih "Laporkan", lalu isi kategori, titik lokasi di peta, judul, deskripsi, dan minimal satu foto.'],
            ['Berapa lama pengaduan saya ditangani?', 'Petugas kecamatan menindaklanjuti dalam '.config('wadulin.sla_working_days').' hari kerja. Bila lewat batas waktu, pengaduan otomatis dieskalasi ke kabupaten.'],
            ['Bagaimana cara memantau pengaduan?', 'Gunakan menu "Lacak Tiket" dan masukkan kode tiket (contoh: WDL-2026-000123). Tidak perlu masuk akun.'],
            ['Apakah identitas saya ditampilkan ke publik?', 'Tidak. Halaman lacak dan peta publik tidak pernah menampilkan nama pelapor. Data kontak hanya terlihat oleh petugas.'],
            ['Bisakah saya membatalkan pengaduan?', 'Bisa, selama statusnya masih Pending. Buka detail pengaduan lalu pilih "Batalkan pengaduan".'],
            ['Apa fungsi QR di fasilitas umum?', 'Pindai QR yang ditempel di fasilitas umum untuk langsung membuka form pengaduan dengan lokasi yang sudah terisi.'],
        ];

        foreach ($faqs as $index => [$question, $answer]) {
            Faq::updateOrCreate(
                ['question' => $question],
                ['answer' => $answer, 'sort_order' => $index + 1, 'is_published' => true],
            );
        }
    }
}
