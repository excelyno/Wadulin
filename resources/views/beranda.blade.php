@extends('layouts.app')

@section('title', 'Wadulin · Wadah Aduan Digital Kabupaten Jember')

@section('content')
    <section class="mx-auto grid max-w-[1440px] gap-12 px-5 py-16 sm:px-8 lg:grid-cols-[1fr_460px] lg:items-center lg:gap-20 lg:py-20">
        <div class="max-w-2xl">
            <p class="text-xs font-bold tracking-[.18em] text-primary-700">LAYANAN PENGADUAN RESMI · 31 KECAMATAN</p>
            <h1 class="mt-6 text-5xl font-extrabold leading-[1.08] tracking-[-.04em] text-slate-900 sm:text-6xl">
                Sampaikan aduanmu,<br><span class="text-primary-600">kami tindak lanjuti.</span>
            </h1>
            <p class="mt-6 max-w-xl text-lg leading-8 text-slate-600">Laporkan masalah fasilitas dan layanan publik langsung dari ponselmu. Setiap aduan mendapat kode tiket dan bisa dipantau sampai tuntas.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('pengaduan.create') }}" class="rounded-lg bg-primary-600 px-5 py-3.5 text-sm font-bold text-white shadow-sm hover:bg-primary-700">Laporkan Sekarang</a>
                <a href="{{ route('dashboard') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-3.5 text-sm font-semibold text-slate-900 hover:bg-slate-50">Lihat cara melapor</a>
            </div>
            <form method="GET" action="{{ route('pengaduan.track') }}" class="mt-8 max-w-xl">
                <p class="mb-2 text-sm text-slate-600">Sudah pernah melapor? Cek perkembangannya tanpa perlu masuk.</p>
                <div class="flex rounded-lg bg-white p-1.5 shadow-sm ring-1 ring-slate-200">
                    <input name="kode" required placeholder="WDL-2026-000123" class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2 text-sm font-mono outline-none focus:ring-0">
                    <button class="rounded-md bg-ink-900 px-4 py-2 text-sm font-semibold text-white hover:bg-ink-700">Lacak</button>
                </div>
            </form>
        </div>
        <div class="relative rounded-2xl bg-primary-50 p-7 lg:h-[500px]">
            <div class="absolute right-6 top-6 rounded-xl bg-white p-4 shadow-lg ring-1 ring-slate-100">
                <p class="font-mono text-xs text-slate-500">WDL-2026-000871</p>
                <p class="mt-2 text-sm font-bold text-slate-900">Jalan berlubang di Jl. Kalimantan</p>
                <span class="mt-3 inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">Diproses</span>
            </div>
            <div class="absolute bottom-10 left-7 rounded-xl bg-ink-900 p-5 text-white shadow-lg">
                <p class="text-xs text-slate-300">Status terbaru</p>
                <p class="mt-2 text-base font-bold">Perbaikan dijadwalkan</p>
                <p class="mt-1 text-xs text-slate-400">Hari ini, 10.05 · Kec. Sumbersari</p>
            </div>
            <div class="flex h-full items-center justify-center"><div class="flex h-44 w-44 items-center justify-center rounded-full border-2 border-dashed border-primary-600/40"><div class="flex h-24 w-24 items-center justify-center rounded-full bg-primary-600 text-4xl text-white shadow-xl">⌖</div></div></div>
        </div>
    </section>

    <section class="bg-ink-900 text-white">
        <div class="mx-auto grid max-w-[1440px] gap-8 px-5 py-10 sm:grid-cols-2 sm:px-8 lg:grid-cols-4">
            @foreach ([['12.480', 'Pengaduan masuk sejak 2024'], ['87%', 'Diselesaikan dalam batas SLA'], ['2,4 hari', 'Rata-rata waktu penanganan'], ['31', 'Kecamatan terlayani penuh']] as [$value, $label])
                <div class="border-slate-700 sm:border-l sm:pl-7 first:border-0 first:pl-0"><p class="text-4xl font-extrabold tracking-tight">{{ $value }}</p><p class="mt-2 text-sm text-slate-400">{{ $label }}</p></div>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-[1440px] px-5 py-20 sm:px-8">
        <div class="flex flex-wrap items-end justify-between gap-6"><div><p class="text-xs font-bold tracking-[.18em] text-primary-700">CARA KERJA</p><h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Empat langkah, lalu tinggal dipantau.</h2></div><a href="{{ route('pengaduan.create') }}" class="text-sm font-bold text-primary-700">Panduan lengkap melapor →</a></div>
        <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([['01', 'Lapor', 'Pilih kategori, tandai titik di peta, unggah foto.'], ['02', 'Diverifikasi', 'Petugas memeriksa laporan dan wilayah penanganannya.'], ['03', 'Ditindaklanjuti', 'Penanganan lapangan berjalan dan setiap perubahan tercatat.'], ['04', 'Selesai', 'Lihat hasil penanganan, lalu beri penilaian.']] as [$number, $title, $description])
                <article class="border-t border-slate-200 pt-5"><p class="font-mono text-sm font-semibold text-primary-600">{{ $number }}</p><h3 class="mt-7 text-xl font-bold text-slate-900">{{ $title }}</h3><p class="mt-3 text-sm leading-6 text-slate-600">{{ $description }}</p></article>
            @endforeach
        </div>
    </section>

    <section class="bg-white"><div class="mx-auto max-w-[1440px] px-5 py-20 sm:px-8"><p class="text-xs font-bold tracking-[.18em] text-primary-700">KATEGORI PENGADUAN</p><div class="mt-3 flex flex-wrap items-end justify-between gap-4"><h2 class="text-3xl font-extrabold tracking-tight text-slate-900">Yang paling sering dilaporkan warga.</h2><span class="text-sm text-slate-500">12 bulan terakhir</span></div><div class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">@foreach (\App\Enums\KategoriPengaduan::cases() as $kategori)<div class="flex items-center justify-between rounded-lg border border-slate-200 bg-paper px-4 py-4"><x-kategori-badge :kategori="$kategori" /><span class="text-xs text-slate-500">Lihat →</span></div>@endforeach</div></div></section>

    <section class="mx-auto max-w-[1440px] px-5 py-20 sm:px-8"><div class="grid gap-10 rounded-2xl bg-ink-900 p-7 text-white sm:p-12 lg:grid-cols-[400px_1fr] lg:items-center"><div><p class="text-xs font-bold tracking-[.18em] text-accent-500">LIVE MAP PUBLIK</p><h2 class="mt-4 text-3xl font-extrabold leading-tight">Sebaran aduan se-Kabupaten Jember.</h2><p class="mt-4 text-sm leading-6 text-slate-400">Titik aduan ditampilkan tanpa identitas pelapor. Pantau masalah yang sedang ditangani di sekitarmu.</p><a href="{{ route('dashboard') }}" class="mt-7 inline-block rounded-lg bg-primary-600 px-4 py-3 text-sm font-bold hover:bg-primary-700">Buka peta lengkap</a></div><div class="flex h-72 items-center justify-center rounded-xl border border-white/10 bg-[#0a2e27]"><div class="grid grid-cols-3 gap-8 text-center text-xs text-slate-300"><div><p class="text-2xl font-bold text-white">1.204</p>Menunggu verifikasi</div><div><p class="text-2xl font-bold text-white">863</p>Sedang ditangani</div><div><p class="text-2xl font-bold text-white">9.611</p>Selesai ditangani</div></div></div></div></section>

    <section class="bg-primary-600"><div class="mx-auto flex max-w-[1440px] flex-col justify-between gap-6 px-5 py-12 text-white sm:flex-row sm:items-center sm:px-8"><div><h2 class="text-3xl font-extrabold">Lihat masalah di sekitarmu? Laporkan sekarang.</h2><p class="mt-2 text-primary-50">Butuh kurang dari satu menit. Kamu akan menerima kode tiket untuk memantaunya.</p></div><a href="{{ route('pengaduan.create') }}" class="shrink-0 rounded-lg bg-white px-5 py-3.5 text-sm font-bold text-primary-700 hover:bg-primary-50">Laporkan Sekarang</a></div></section>
@endsection
