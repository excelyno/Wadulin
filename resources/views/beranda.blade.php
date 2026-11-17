@extends('layouts.app')

@section('title', 'Wadulin · Sistem Pengaduan Masyarakat')

@section('content')
    <section class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-700 to-slate-900 text-white">
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.4) 1px, transparent 0); background-size: 24px 24px;"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-sm font-medium ring-1 ring-inset ring-white/20">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    Layanan masyarakat terbuka &amp; transparan
                </span>
                <h1 class="mt-5 text-4xl font-bold tracking-tight sm:text-5xl">
                    Sampaikan keluhan warga, pantau penanganannya secara langsung.
                </h1>
                <p class="mt-5 text-lg leading-relaxed text-indigo-100">
                    Wadulin adalah sistem pengaduan masyarakat. Isi formulir pengaduan, lihat daftar
                    dan lokasi pengaduan di dashboard, serta ikuti penanganannya pada peta live.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('pengaduan.create') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-5 py-3 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                            <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/>
                        </svg>
                        Ajukan Pengaduan
                    </a>
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/10 px-5 py-3 text-sm font-semibold text-white ring-1 ring-inset ring-white/30 transition hover:bg-white/20">
                        Lihat Dashboard
                    </a>
                </div>
                <p class="mt-6 text-sm text-indigo-200">
                    Saat ini tercatat <span class="font-semibold text-white">{{ number_format($totalPengaduan) }}</span> pengaduan warga.
                </p>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Cara Kerja Wadulin</h2>
            <p class="mt-3 text-slate-600">Tiga langkah sederhana dari laporan hingga penanganan.</p>
        </div>
        <div class="mt-12 grid gap-8 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                        <path d="M21 12.75a.75.75 0 0 0 0-1.5H7.5l4.72-4.72a.75.75 0 0 0-1.06-1.06l-6 6a.75.75 0 0 0 0 1.06l6 6a.75.75 0 1 0 1.06-1.06L7.5 12.75H21Z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-semibold text-slate-900">1. Isi formulir pengaduan</h3>
                <p class="mt-2 text-sm text-slate-600">Lengkapi judul, kategori, deskripsi, dan tentukan lokasi pengaduan langsung pada peta. Tidak perlu membuat akun.</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                        <path fill-rule="evenodd" d="M11.54 22.35c.19.13.38.21.6.21a1 1 0 0 0 .61-.21l.06-.05C16.41 19.45 21 15.06 21 10.5A9 9 0 1 0 3 10.5c0 4.56 4.59 8.95 8.54 11.8l-.01.05ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-semibold text-slate-900">2. Pantau di dashboard &amp; peta</h3>
                <p class="mt-2 text-sm text-slate-600">Semua pengaduan tampil di dashboard publik. Lokasi setiap laporan ditandai pada peta live yang diperbarui otomatis.</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                        <path d="M12 2.25a.75.75 0 0 1 .67.42l2.6 5.27 5.82.85a.75.75 0 0 1 .41 1.28l-4.21 4.1.99 5.79a.75.75 0 0 1-1.09.79L12 17.9l-5.2 2.73a.75.75 0 0 1-1.09-.79l.99-5.79-4.2-4.1a.75.75 0 0 1 .41-1.28l5.82-.85 2.6-5.27a.75.75 0 0 1 .67-.42Z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-semibold text-slate-900">3. Petugas menindaklanjuti</h3>
                <p class="mt-2 text-sm text-slate-600">Petugas masuk melalui dashboard, memperbarui status pengaduan menjadi diproses hingga selesai. Perubahan langsung terlihat.</p>
            </div>
        </div>
    </section>

    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Kategori Pengaduan</h2>
                <p class="mt-3 text-slate-600">Pilih kategori yang paling sesuai saat Anda mengajukan pengaduan.</p>
            </div>
            <div class="mt-10 flex flex-wrap justify-center gap-3">
                @foreach (\App\Enums\KategoriPengaduan::cases() as $kategori)
                    <x-kategori-badge :kategori="$kategori" class="!px-3 !py-1.5 !text-sm" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl bg-slate-900 px-6 py-12 sm:px-12">
            <div class="mx-auto max-w-3xl text-center">
                <h2 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">Punya keluhan warga?</h2>
                <p class="mt-3 text-slate-300">Sampaikan sekarang dan ikuti penanganannya secara terbuka.</p>
                <a href="{{ route('pengaduan.create') }}"
                   class="mt-6 inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    Mulai Ajukan Pengaduan
                </a>
            </div>
        </div>
    </section>
@endsection
