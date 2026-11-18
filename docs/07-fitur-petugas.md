# 7. Fitur Petugas (Panel Admin)

Ada dua jenis petugas: **admin kecamatan** dan **admin kabupaten**. Keduanya memakai panel yang hampir sama, dengan perbedaan hak.

## 7.1 Satu panel, dua peran

### Struktur route

`routes/web.php:67-101`:

```php
$adminComplaintRoutes = function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/pengaduan', [AdminComplaintController::class, 'index'])->name('pengaduan.index');
    Route::get('/pengaduan/{complaint}', [AdminComplaintController::class, 'show'])->name('pengaduan.show');
    Route::patch('/pengaduan/{complaint}/status', ComplaintStatusController::class)->name('pengaduan.status');
    Route::post('/pengaduan/{complaint}/disposisi', ComplaintDispositionController::class)->name('pengaduan.disposisi');
    Route::resource('qr', QrLocationController::class)->only(['index', 'store', 'update'])->parameters(['qr' => 'qrLocation']);
};

// Kecamatan: /kecamatan/...  nama: kecamatan.*
Route::middleware(['auth', 'role:admin_kecamatan'])->prefix('kecamatan')->name('kecamatan.')->group($adminComplaintRoutes);

// Kabupaten: /kabupaten/...  nama: kabupaten.*  + menu tambahan
Route::middleware(['auth', 'role:admin_kabupaten'])->prefix('kabupaten')->name('kabupaten.')->group(function () use ($adminComplaintRoutes): void {
    $adminComplaintRoutes();
    Route::get('/eskalasi', EscalationController::class)->name('eskalasi');
    Route::resource('kategori', ...);
    Route::resource('petugas', ...);
    Route::resource('pengumuman', ...);
    Route::resource('faq', ...);
});
```

| Halaman | Kecamatan | Kabupaten |
| --- | --- | --- |
| Dashboard | `/kecamatan` | `/kabupaten` |
| Daftar pengaduan | `/kecamatan/pengaduan` (kecamatannya saja) | `/kabupaten/pengaduan` (semua) |
| Detail, ubah status, disposisi | ✓ | ✓ (aksi hanya untuk yang tereskalasi) |
| Titik QR | ✓ (kecamatannya) | ✓ (semua) |
| Eskalasi | — | ✓ |
| Kategori, Petugas, Pengumuman, FAQ | — | ✓ |

> **`Route::resource` itu apa?** Jalan pintas untuk membuat sekumpulan route CRUD standar. `->only(['index', 'store', 'update'])` membatasi hanya tiga: `GET /qr` (index), `POST /qr` (store), `PUT/PATCH /qr/{qrLocation}` (update). `->parameters(['qr' => 'qrLocation'])` mengganti nama parameter URL supaya cocok dengan nama variabel `$qrLocation` di controller (route model binding mencocokkan berdasarkan nama).

### Cara view tahu panel mana yang sedang dipakai

View admin dipakai bersama oleh dua panel. Supaya link mengarah ke panel yang benar, view mengambil awalan dari peran user:

```blade
@php $panel = auth()->user()->role->panelPrefix(); @endphp   {{-- 'kecamatan' atau 'kabupaten' --}}

<a href="{{ route($panel.'.pengaduan.show', $complaint) }}">
```

`UserRole::panelPrefix()` ada di `app/Enums/UserRole.php:23`.

### Layout panel

`resources/views/layouts/admin.blade.php`: sidebar gelap berisi menu (menu kabupaten punya bagian "Pengaturan"), kolom pencarian kode tiket di atas, pesan sukses, dan **daftar error validasi** (semua form admin menampilkan error di sini).

---

## 7.2 Dashboard admin

**File:** `app/Http/Controllers/Admin/DashboardController.php`, `resources/views/admin/dashboard.blade.php`, komponen `x-kpi-card`

```php
$complaints = fn (): Builder => Complaint::query()->visibleTo($user);

$kpi = [
    'total' => $complaints()->count(),
    'pending' => $complaints()->where('status', ComplaintStatus::Pending)->count(),
    // ...
    'overdue' => $complaints()->overdue()->count(),
    'eskalasi' => $complaints()->whereNotNull('escalated_at')->whereIn('status', ComplaintStatus::open())->count(),
    'rating' => $complaints()->whereNotNull('rating')->avg('rating'),
    'rata_hari' => $this->averageResolutionDays($complaints()),
];
```

> **Kenapa `$complaints` berupa fungsi (`fn () => ...`), bukan langsung query?** Query builder Laravel bisa berubah (*mutable*). Kalau kita memakai satu objek query lalu menambah `where('status', Pending)`, syarat itu menempel dan ikut terbawa ke perhitungan berikutnya. Dengan fungsi, setiap pemanggilan `$complaints()` membuat query **baru** yang bersih, tapi tetap dengan `visibleTo($user)`. Jadi semua angka otomatis terbatas sesuai peran.

Isi dashboard:

| Bagian | Kecamatan | Kabupaten | Sumber data |
| --- | --- | --- | --- |
| KPI atas | Pending, Diproses, Selesai bulan ini | Total, % selesai, Eskalasi aktif | `$kpi` |
| KPI bawah | Melewati SLA, Rata-rata waktu selesai, Rating | sama | `$kpi` |
| Segera jatuh tempo | 8 pengaduan terbuka dengan `due_at` ≤ besok, urut paling mendesak | sama | `$dueSoon` |
| Per kategori | Bar sederhana | sama | `withCount` + `visibleTo` |
| Peringkat kecamatan | — | Tabel: total, selesai, % selesai, lewat SLA | `districtRanking()` |

`districtRanking()` (`:77`) memakai beberapa `withCount` beralias sekaligus:

```php
District::query()->withCount([
    'complaints as total_count',
    'complaints as selesai_count' => fn (Builder $query) => $query->where('status', ComplaintStatus::Selesai),
    'complaints as overdue_count' => fn (Builder $query) => $query->overdue(),
])
```

Hasilnya satu query dengan tiga subquery hitung. Lalu persentase dihitung di PHP dan diurutkan: persentase selesai tinggi di atas, dikurangi jumlah yang lewat SLA.

KPI kartu yang punya `href` bisa diklik untuk membuka daftar yang sudah difilter, misalnya "Melewati SLA" → `?sla=lewat`.

---

## 7.3 Daftar / antrian pengaduan

**File:** `Admin\ComplaintController::index()` (`:16`), `resources/views/admin/complaints/index.blade.php`

Filter yang tersedia (semua lewat query string, jadi bisa di-bookmark):

| Parameter | Contoh | Efek |
| --- | --- | --- |
| `status` | `?status=pending` | Tab status |
| `kategori` | `?kategori=3` | Filter kategori |
| `kecamatan` | `?kecamatan=18` | **Hanya kabupaten.** Untuk kecamatan, nilai ini diabaikan. |
| `sla` | `?sla=lewat` atau `?sla=24jam` | Terlambat / jatuh tempo ≤ 24 jam |
| `q` | `?q=WDL-2026` | Cari di kode tiket atau judul |

```php
$districtId = $user->isAdminKabupaten() ? ($request->integer('kecamatan') ?: null) : null;

Complaint::query()
    ->visibleTo($user)
    ->with(['category', 'district', 'user'])
    ->when($status, fn ($query) => $query->where('status', $status))
    // ...
    ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner
        ->where('ticket_code', 'like', "%{$search}%")
        ->orWhere('title', 'like', "%{$search}%")))
    ->latest()
    ->paginate(15)
    ->withQueryString();
```

Poin penting:

- **`visibleTo($user)` selalu dipasang pertama.** Petugas kecamatan tidak bisa melihat kecamatan lain walau mengubah `?kecamatan=` di URL.
- **`orWhere` dibungkus `where(fn ...)`.** Tanpa pembungkus, SQL-nya menjadi `district_id = 5 AND ticket_code LIKE ... OR title LIKE ...`. Karena `AND` lebih kuat dari `OR`, bagian `OR title LIKE` akan mencari ke **semua kecamatan**, sehingga data bocor. Dengan pembungkus: `district_id = 5 AND (ticket_code LIKE ... OR title LIKE ...)`. Ini kesalahan klasik, perhatikan baik-baik.
- `{$search}` di dalam `like` aman dari SQL injection karena Laravel memakai *parameter binding*. Nilainya dikirim terpisah dari perintah SQL.
- Jumlah per tab status dihitung dengan query `groupBy` terpisah, juga dengan `visibleTo`.

Kotak pencarian di header layout admin mengirim `?q=` ke halaman ini.

---

## 7.4 Detail pengaduan (admin)

**File:** `Admin\ComplaintController::show()` (`:64`), `resources/views/admin/complaints/show.blade.php`

Berbeda dengan detail milik warga, versi admin memuat **semua** riwayat (termasuk catatan internal, ditandai label "Internal"), data kontak pelapor (nama, HP, email), sumber QR, peta lokasi, dan foto dibagi menjadi "Foto bukti" dan "Foto hasil".

```php
Gate::authorize('view', $complaint);

$complaint->load([
    'category', 'district', 'user', 'qrLocation', 'attachments',
    'statusHistories.changer.district',
    'dispositions.fromDistrict', 'dispositions.toDistrict', 'dispositions.mover',
]);
```

`statusHistories.changer.district` adalah *nested eager loading*: muat riwayat, lalu user pengubahnya, lalu kecamatan user itu. Semua dalam beberapa query, bukan satu query per baris timeline.

Di awal view, izin dihitung sekali:

```php
$canUpdateStatus = auth()->user()->can('updateStatus', $complaint);
$canDispose = auth()->user()->can('dispose', $complaint);
$transitions = $complaint->status->officerTransitions();
```

Kotak "Ubah status" dan "Disposisi" hanya tampil kalau izinnya ada. Kalau keduanya tidak ada dan pengaduan belum final (biasanya admin kabupaten melihat pengaduan yang belum tereskalasi), muncul penjelasan "Aksi hanya tersedia bila pengaduan tereskalasi".

> Pelapor yang memilih **sembunyikan nama** (`is_anonymous`) tetap terlihat namanya oleh petugas, dengan keterangan bahwa ia memilih disembunyikan di tampilan publik. Petugas butuh kontak untuk klarifikasi.

---

## 7.5 Mengubah status

**File:** `Admin\ComplaintStatusController`, `UpdateComplaintStatusRequest`, `ComplaintPolicy::updateStatus()`, `Complaint::transitionTo()`

Alurnya sudah dibahas lengkap di [bab 2](02-alur-request-laravel.md#22-contoh-nyata-petugas-mengubah-status-pengaduan) dan aturannya di [bab 4](04-status-sla-eskalasi.md). Ringkasan form:

| Field | Keterangan |
| --- | --- |
| `status` | Radio. Pilihannya dari `officerTransitions()`. Label tombol: Proses / Selesaikan / Tolak. |
| `note` | Catatan. **Wajib (min 10 karakter) kalau memilih Tolak.** |
| `is_public` | Checkbox "Tampilkan catatan ke pelapor & publik". Bawaan: dicentang. |
| `photos[]` | Foto hasil, maks 3. Disimpan sebagai lampiran `hasil`. |

Setelah berhasil, pelapor menerima notifikasi ([bab 6.9](06-fitur-warga.md#69-notifikasi)).

> **Kenapa alasan penolakan wajib?** Warga berhak tahu kenapa laporannya ditolak. Penolakan tanpa alasan menurunkan kepercayaan dan mengundang laporan ulang.

---

## 7.6 Disposisi

**File:** `Admin\ComplaintDispositionController`, `StoreComplaintDispositionRequest`, `ComplaintPolicy::dispose()`, `Complaint::disposeTo()`

Aturan lengkapnya di [bab 4.5](04-status-sla-eskalasi.md#45-disposisi-memindahkan-ke-kecamatan-lain). Bagian controller yang perlu diperhatikan:

```php
$complaint->disposeTo($targetDistrict, $user, $request->validated('reason'));

$complaint->user->notify(new ComplaintUpdated($complaint, "Pengaduan kamu dialihkan ke Kecamatan {$targetDistrict->name} ..."));

$redirect = $user->isAdminKabupaten()
    ? redirect()->route('kabupaten.pengaduan.show', $complaint)
    : redirect()->route('kecamatan.pengaduan.index');
```

Kecamatan diarahkan ke daftar antrian karena setelah disposisi ia tidak berhak lagi membuka pengaduan itu. Kabupaten tetap bisa melihat semua, jadi diarahkan kembali ke detail.

Dropdown kecamatan tujuan di view sudah tidak berisi kecamatan saat ini (`District::whereKeyNot($complaint->district_id)`), dan validasi `Rule::notIn` menjaga hal yang sama di server.

---

## 7.7 Titik QR (kecamatan & kabupaten)

**File:** `Admin\QrLocationController`, `StoreQrLocationRequest`, `UpdateQrLocationRequest`, `QrLocationPolicy`, `resources/views/admin/qr-locations/`

### Daftar

```php
QrLocation::query()->manageableBy($user)->with('district')->withCount('complaints')->orderBy('name')->get();
```

Setiap titik menampilkan gambar QR, URL scan, tombol "Unduh PNG", jumlah pengaduan yang berasal dari QR itu, dan form edit.

### Membuat

```php
QrLocation::create([
    ...$request->validated(),
    'district_id' => $user->isAdminKabupaten() ? $request->validated('district_id') : $user->district_id,
    'code' => QrLocation::generateCode(),
    'created_by' => $user->id,
]);
```

- **Admin kecamatan tidak memilih kecamatan.** Titik otomatis masuk kecamatannya. Di `StoreQrLocationRequest`, field `district_id` bahkan **dilarang** (`prohibitedIf`) untuk admin kecamatan, dan **wajib** (`requiredIf`) untuk admin kabupaten.
- Kode dibuat server, bukan diketik petugas.

### Mengubah

`UpdateQrLocationRequest` mewarisi semua aturan `StoreQrLocationRequest`, tapi `authorize()`-nya memakai `QrLocationPolicy::update`:

```php
return match ($user->role) {
    UserRole::AdminKabupaten => true,
    UserRole::AdminKecamatan => $qrLocation->district_id === $user->district_id,
    UserRole::Masyarakat => false,
};
```

Controller juga membuang `district_id` untuk admin kecamatan (`$request->safe()->except('district_id')`), sebagai pengaman lapis kedua.

### Gambar QR

Gambar QR dibuat **di browser** dengan library `qrcodejs` dari CDN (lihat `@push('scripts')` di view). Setiap elemen `.qr-code` punya `data-url` berisi URL scan:

```js
document.querySelectorAll('.qr-code').forEach(function (el) {
    new QRCode(el, { text: el.dataset.url, width: 160, height: 160, correctLevel: QRCode.CorrectLevel.M });
});
```

Tombol "Unduh PNG" mengambil `<canvas>` hasil qrcodejs, mengubahnya menjadi data PNG (`toDataURL`), lalu memicu unduhan.

> **Kenapa tidak membuat QR di server dengan library PHP?** Menambah dependensi Composer butuh persetujuan dan perawatan. QR hanya perlu dilihat dan diunduh petugas, jadi membuatnya di browser sudah cukup. Konsekuensinya: halaman ini butuh internet untuk memuat library.

> URL di QR memakai `route('qr.scan', $qrLocation)`, yang bergantung pada `APP_URL` dan domain saat halaman dibuka. **Cetak QR dari domain produksi**, bukan dari `localhost`, supaya bisa dipindai warga.

---

## 7.8 Eskalasi (kabupaten)

**File:** `Admin\EscalationController`, `resources/views/admin/escalations/index.blade.php`

```php
Complaint::query()
    ->with(['category', 'district'])
    ->whereNotNull('escalated_at')
    ->whereIn('status', ComplaintStatus::open())
    ->orderBy('due_at')
    ->paginate(20);
```

Daftar pengaduan tereskalasi yang masih terbuka, diurutkan dari `due_at` paling lama (paling terlambat di atas). Kolom "Keterlambatan" memakai komponen `x-sla-chip`. Klik kode tiket untuk membuka detail dan menindaklanjuti.

---

## 7.9 Pengumuman (kabupaten)

**File:** `Admin\AnnouncementController`, `StoreAnnouncementRequest`, `UpdateAnnouncementRequest`, `resources/views/admin/announcements/`

### Slug otomatis

```php
protected function prepareForValidation(): void
{
    $this->merge(['slug' => Str::slug((string) $this->input('title'))]);
}
```

`prepareForValidation()` dijalankan **sebelum** validasi. Slug dibuat dari judul ("Libur Layanan Idul Fitri" → `libur-layanan-idul-fitri`), lalu divalidasi unik. Saat update, `->ignore($this->route('announcement'))` membuat pengumuman itu sendiri tidak dianggap bentrok.

Pola yang sama dipakai `StoreCategoryRequest` untuk slug kategori.

### Menerbitkan

```php
$isPublished = $request->boolean('is_published');
Announcement::create([
    ...$request->safe()->except('cover'),
    'cover_path' => $request->file('cover')?->store('announcements', 'public'),
    'is_published' => $isPublished,
    'published_at' => $isPublished ? now() : null,
    'author_id' => $request->user()->id,
]);
```

- `published_at` diisi saat **pertama kali** diterbitkan. Saat update, kalau sebelumnya sudah punya `published_at`, nilainya tidak diubah. Jadi menyunting typo tidak mengubah tanggal terbit.
- Mematikan "Terbitkan" menyembunyikan pengumuman dari publik (halaman detailnya jadi 404).
- Sampul opsional. Saat diganti, file lama dihapus dari disk supaya tidak menumpuk.

Tidak ada tombol hapus. Pengumuman yang tidak dipakai cukup dijadikan draf.

---

## 7.10 Kategori (kabupaten)

**File:** `Admin\CategoryController`, `StoreCategoryRequest`, `UpdateCategoryRequest`, `resources/views/admin/categories/`

- Daftar kategori dengan jumlah pengaduan (`withCount('complaints')`).
- Tambah dan ubah: nama, ikon (nama ikon Lucide, hanya huruf kecil/angka/tanda hubung), deskripsi, aktif.
- **Tidak bisa dihapus**, hanya dinonaktifkan. Kategori nonaktif hilang dari form warga, tapi pengaduan lama tetap utuh.
- Nama yang menghasilkan slug sama dengan kategori lain ditolak ("Kategori dengan nama ini sudah ada").

`UpdateCategoryRequest` hanya `extends StoreCategoryRequest` tanpa isi tambahan, karena aturannya sama. Bagian `ignore($this->route('category'))` otomatis bernilai `null` saat membuat baru dan berisi kategori saat mengubah.

Form tambah dan ubah memakai partial yang sama: `admin/categories/_fields.blade.php`. Variabel `$prefix` membuat atribut `id` setiap input unik, karena dalam satu halaman ada banyak form edit (satu per kategori).

---

## 7.11 Akun petugas (kabupaten)

**File:** `Admin\OfficerController`, `StoreOfficerRequest`, `UpdateOfficerRequest`, `resources/views/admin/officers/index.blade.php`

### Membuat petugas

```php
User::create([
    ...$request->validated(),
    'role' => UserRole::AdminKecamatan,
    'email_verified_at' => now(),
]);
```

Form hanya membuat **admin kecamatan** dan wajib memilih kecamatan. Akun admin kabupaten tidak bisa dibuat dari aplikasi (dibuat lewat seeder atau tinker), supaya jumlah orang dengan akses tertinggi tetap terkendali.

### Mengubah petugas

```php
abort_if($officer->role === UserRole::Masyarakat, 404);

$officer->update(array_filter(
    $request->validated(),
    fn (mixed $value, string $key): bool => $key !== 'password' || filled($value),
    ARRAY_FILTER_USE_BOTH,
));
```

- **Akun warga tidak boleh diubah dari halaman ini** (404). Route parameter `{officer}` sebenarnya bisa menerima ID user mana pun, jadi pemeriksaan ini wajib.
- **Password kosong = tidak diubah.** `array_filter` membuang key `password` kalau nilainya kosong.

Aturan khusus di `UpdateOfficerRequest`:

```php
'district_id' => [
    Rule::requiredIf($officer->role === UserRole::AdminKecamatan),
    Rule::prohibitedIf($officer->role === UserRole::AdminKabupaten),
    ...
],
'is_active' => [
    'boolean',
    Rule::prohibitedIf($officer->is($this->user()) && ! $this->boolean('is_active')),
],
```

- Admin kecamatan wajib punya kecamatan; admin kabupaten dilarang punya kecamatan.
- **Admin tidak bisa menonaktifkan akunnya sendiri**, supaya tidak terkunci dari sistem.

Petugas **tidak pernah dihapus**, hanya dinonaktifkan, untuk menjaga jejak audit ([bab 3](03-database-dan-model.md#users--semua-akun)).

---

## 7.12 FAQ (kabupaten)

**File:** `Admin\FaqController`, `StoreFaqRequest`, `UpdateFaqRequest`, `resources/views/admin/faqs/`

Satu-satunya data master yang **bisa dihapus** (`destroy`), karena tidak ada tabel lain yang bergantung pada FAQ. Field: pertanyaan, jawaban, urutan (`sort_order`, kosong = 0), tampil/sembunyikan.

Lanjut ke [bab 8: Keamanan dan hak akses](08-keamanan-hak-akses.md).
