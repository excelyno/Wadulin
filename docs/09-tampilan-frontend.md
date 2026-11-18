# 9. Tampilan (Frontend)

Tampilan dibuat dengan **Blade** (template HTML Laravel) dan **Tailwind CSS 4**. JavaScript hanya dipakai untuk peta dan QR.

## 9.1 Cara kerja Blade secara singkat

Blade adalah HTML dengan tambahan sintaks. Server mengubahnya menjadi HTML biasa sebelum dikirim ke browser.

| Sintaks | Arti | Contoh di proyek |
| --- | --- | --- |
| `{{ $x }}` | Cetak nilai, **otomatis di-escape** (aman dari XSS) | `{{ $complaint->title }}` |
| `{!! $x !!}` | Cetak tanpa escape. **Jangan dipakai untuk data pengguna.** | Tidak dipakai |
| `@if / @foreach / @forelse` | Logika | `@forelse ($complaints as $complaint) ... @empty ... @endforelse` |
| `@extends('layouts.app')` | Halaman ini memakai layout tertentu | Semua halaman |
| `@section('content') ... @endsection` | Isi bagian yang disediakan layout | Semua halaman |
| `@yield('content')` | Di layout: tempat isi halaman dimasukkan | `layouts/app.blade.php` |
| `@push('scripts')` / `@stack('scripts')` | Halaman menitipkan script, layout mencetaknya di akhir `<body>` | Peta di `create`, `map`, admin `show` |
| `@include('complaints._card', [...])` | Sisipkan partial | Kartu pengaduan, field form |
| `<x-status-badge :status="..." />` | Pakai komponen | Badge status di mana-mana |
| `@csrf`, `@method('PATCH')` | Token keamanan, method HTTP palsu | Semua form |
| `@error('field') ... @enderror` | Tampilkan pesan error validasi field itu | Form warga |
| `old('field', $default)` | Isian lama setelah gagal validasi | Form warga |
| `@class([...])` | Gabungkan class berdasarkan kondisi | Input yang error, menu aktif |
| `@can('aksi', $model)` | Tampilkan hanya kalau policy mengizinkan | Tombol batal & nilai |
| `@checked(...)`, `@selected(...)` | Cetak atribut `checked`/`selected` kalau kondisi benar | Radio, checkbox, dropdown |

Contoh `@class` dari form login:

```blade
<input @class(['field-input', 'field-input-invalid' => $errors->has('email')])>
```

Class `field-input` selalu ada. `field-input-invalid` hanya ditambahkan kalau field `email` punya error.

## 9.2 Layout

Ada dua layout. Setiap halaman memilih salah satu lewat `@extends`.

### `layouts/app.blade.php` — situs publik dan warga

Isi dari atas ke bawah:

1. **Link "Langsung ke konten"**: tersembunyi, muncul saat menekan Tab. Membantu pengguna keyboard dan pembaca layar melompati menu.
2. **Bar hitam tipis**: "Layanan pengaduan resmi Pemerintah Kabupaten Jember".
3. **Header**: nama WADULIN, menu, tombol Masuk/Buat pengaduan atau nama user + Keluar.
   - Menu dibuat dari array `$navItems` di bagian `@php` paling atas. Isinya berubah sesuai status login dan peran.
   - Menu aktif ditandai garis bawah, ditentukan dengan `request()->routeIs(...)`.
   - **Di layar kecil** menu desktop disembunyikan (`hidden lg:flex`) dan diganti tombol "Menu" berbasis `<details>`, sehingga tetap bisa dibuka tanpa JavaScript.
4. **Pesan flash** `session('success')` / `session('error')`.
5. **`<main id="konten">`** berisi `@yield('content')`.
6. **Footer**: penjelasan singkat (membaca angka SLA dari config) dan link.

### `layouts/admin.blade.php` — panel petugas

Sidebar gelap (`bg-ink-900`) supaya jelas berbeda dari situs publik, header berisi pencarian kode tiket dan tombol Keluar, lalu area konten yang selalu menampilkan pesan sukses dan **daftar error validasi** di atas. Karena itu form-form admin tidak perlu menulis `@error` per field.

## 9.3 Komponen dan partial

**Komponen** (`resources/views/components/`) dipanggil dengan `<x-nama>`. Dipakai untuk potongan UI kecil yang muncul di banyak tempat dan menerima parameter.

| Komponen | Parameter | Fungsi |
| --- | --- | --- |
| `x-status-badge` | `status`, `escalated` (opsional) | Label status berwarna + label "Tereskalasi" |
| `x-sla-chip` | `complaint` | Label sisa waktu SLA ([bab 4.3](04-status-sla-eskalasi.md#menampilkan-sla)) |
| `x-kpi-card` | `label`, `value`, `href`, `tone` | Kartu angka di dashboard admin. Kalau ada `href`, kartunya bisa diklik. |

Contoh pemakaian:

```blade
<x-status-badge :status="$complaint->status" :escalated="$complaint->isEscalated()" class="shrink-0" />
```

- Tanda `:` di depan nama parameter berarti nilainya ekspresi PHP, bukan teks.
- Atribut tambahan (`class="shrink-0"`) diteruskan lewat `$attributes->merge(...)` di dalam komponen.
- Warna titik di badge diambil dari `ComplaintStatus::hexColor()`, sama dengan warna penanda di peta.

**Partial** (file berawalan `_`) disisipkan dengan `@include`. Dipakai untuk potongan yang lebih besar dan spesifik halaman:

| Partial | Dipakai di |
| --- | --- |
| `complaints/_card.blade.php` | Dashboard warga, daftar pengaduan saya |
| `announcements/_card.blade.php` | Beranda, daftar pengumuman |
| `admin/categories/_fields.blade.php` | Form tambah dan ubah kategori |
| `admin/qr-locations/_fields.blade.php` | Form tambah dan ubah titik QR |
| `admin/announcements/_fields.blade.php` | Form tambah dan ubah pengumuman |
| `admin/faqs/_fields.blade.php` | Form tambah dan ubah FAQ |

> **Kenapa form tambah dan ubah memakai partial yang sama?** Supaya field-nya tidak ditulis dua kali. Kalau ada field baru, cukup ditambah di satu file. Partial menerima variabel model (`$category`, `$qrLocation`, dll.). Kalau `null`, berarti form tambah. Itu sebabnya nilai awal ditulis `$category?->name`.

## 9.4 Tailwind CSS dan token desain

Semua gaya ada di `resources/css/app.css`.

### Token (`@theme`)

```css
@theme {
    --font-sans: 'Public Sans', ...;
    --font-mono: 'IBM Plex Mono', ...;
    --color-primary-600: #0b6b55;   /* hijau utama: tombol, link, aksen */
    --color-primary-700: #08513f;
    --color-accent-500: #ffcc00;    /* kuning: garis fokus keyboard, menu aktif admin */
    --color-ink-900: #14211d;       /* teks utama, sidebar admin */
    --color-ink-700: #33423d;       /* teks sekunder */
    --color-paper: #f6f5f1;         /* latar halaman */
    --color-line: #d7d5cd;          /* garis dan border */
}
```

Setiap variabel `--color-xxx` otomatis menjadi class Tailwind: `bg-primary-600`, `text-ink-700`, `border-line`, dan seterusnya. **Gunakan token ini, jangan menulis warna baru sembarangan.** Kalau warna utama perlu diganti, cukup ubah di satu tempat.

### Class komponen (`@layer components`)

Supaya tidak menulis deretan class yang sama berulang kali, proyek ini mendefinisikan class siap pakai:

| Class | Untuk |
| --- | --- |
| `container-page` | Pembungkus konten selebar maks 6xl dengan padding samping |
| `panel` | Kotak putih dengan border tipis |
| `btn` + `btn-primary` / `btn-secondary` / `btn-danger` | Tombol. **Selalu pakai dua class**: `class="btn btn-primary"` |
| `link` | Link teks bergaris bawah |
| `field-label`, `field-hint`, `field-input`, `field-input-invalid`, `field-error` | Elemen formulir |
| `alert-success`, `alert-error`, `alert-info` | Kotak pesan |
| `ticket` | Kode tiket berhuruf monospace |

> **Kenapa `btn-primary` tidak otomatis menyertakan `btn`?** Di Tailwind 4, `@apply` hanya bisa memakai class utilitas Tailwind, bukan class buatan sendiri. Jadi gaya dasar tombol ada di `btn` dan warnanya di `btn-primary`, lalu keduanya dipasang bersama.

### Prinsip desain

Gaya visual WADULIN mengikuti situs layanan publik: polos, jelas, mudah dibaca semua umur.

- Sudut kecil (`rounded-md`), border tipis, **tanpa bayangan dan gradasi**.
- Satu warna utama (hijau). Warna lain hanya untuk makna: kuning = menunggu/peringatan, biru = diproses, hijau = selesai, merah = ditolak/terlambat, ungu = eskalasi/disposisi.
- Judul tegas, teks bahasa sehari-hari. Hindari label hiasan berhuruf kapital.
- **Data asli, bukan angka hiasan.**
- Garis fokus kuning tebal (`@layer base`) di semua elemen interaktif, untuk pengguna keyboard.

### Build

Tailwind hanya menghasilkan class yang benar-benar dipakai. Ia memindai file yang disebut di `@source` (view Blade, view pagination Laravel, cache view). Setelah menambah class baru di Blade:

```bash
npm run dev     # saat development, otomatis memperbarui
npm run build   # sebelum deploy, menghasilkan public/build/
```

Kalau tampilan tidak berubah atau muncul error "Unable to locate file in Vite manifest", jalankan salah satu perintah di atas.

## 9.5 JavaScript

`resources/js/app.js` sengaja kosong. Script khusus halaman ditulis langsung di view dalam `@push('scripts')`, karena hanya dipakai di halaman itu.

| Halaman | Library | Fungsi |
| --- | --- | --- |
| `complaints/create` | Leaflet (unpkg) | Memilih titik lokasi, GPS, penanda bisa digeser |
| `map` | Leaflet | Menampilkan titik pengaduan publik |
| `admin/complaints/show` | Leaflet | Menampilkan lokasi satu pengaduan |
| `admin/qr-locations/index` | qrcodejs (cdnjs) | Membuat dan mengunduh gambar QR |

Library dimuat dari CDN dengan **versi tetap** (`leaflet@1.9.4`, `qrcodejs/1.0.0`), supaya pembaruan library tidak tiba-tiba merusak tampilan.

Peta memakai *tile* (gambar potongan peta) dari OpenStreetMap. Pusat awal peta adalah koordinat Jember `[-8.1724, 113.7008]`.

## 9.6 Pola formulir yang dipakai di seluruh proyek

1. **Error per field** di halaman warga: `@error('field')<p class="field-error">{{ $message }}</p>@enderror`, plus ringkasan jumlah error di atas form.
2. **Isian lama**: `value="{{ old('title') }}"`, supaya warga tidak mengetik ulang setelah gagal validasi.
3. **Checkbox selalu terkirim**: input `hidden` bernilai `0` sebelum checkbox bernilai `1` ([bab 6.4](06-fitur-warga.md#c-validasi-storecomplaintrequest)).
4. **Konfirmasi aksi berbahaya**: `onsubmit="return confirm('...')"` untuk batal, disposisi, hapus FAQ.
5. **Validasi HTML** (`required`, `minlength`, `maxlength`) sebagai bantuan cepat di browser. **Validasi sebenarnya tetap di server.**
6. **Petunjuk di label** (`field-hint`), bukan di placeholder saja, karena placeholder hilang saat mulai mengetik.

## 9.7 Aksesibilitas

- Setiap input punya `<label for="...">` yang cocok dengan `id` input.
- Grup radio dibungkus `<fieldset>` + `<legend>`.
- Radio yang disembunyikan memakai `sr-only` (tetap terbaca pembaca layar), bukan `display:none`.
- Teks koordinat di form pengaduan memakai `aria-live="polite"`, sehingga perubahan lokasi diumumkan pembaca layar.
- Pesan sukses `role="status"`, pesan error `role="alert"`.
- Navigasi diberi `aria-label`.

## 9.8 File view yang tidak dipakai

`resources/views/welcome.blade.php` adalah halaman bawaan Laravel dan tidak dipakai route mana pun. Boleh diabaikan atau dihapus.

Lanjut ke [bab 10: Testing](10-testing.md).
