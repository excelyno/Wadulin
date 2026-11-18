@extends('layouts.app')

@section('title', 'WADULIN · Pengaduan Masyarakat Kabupaten Jember')

@php
    $slaDays = config('wadulin.sla_working_days');
    $number = fn (int $value): string => number_format($value, 0, ',', '.');
@endphp

@section('content')
    <section class="border-b border-line bg-white">
        <div class="container-page grid gap-10 py-12 lg:grid-cols-[1fr_380px] lg:py-16">
            <div>
                <h1 class="max-w-2xl text-4xl font-extrabold leading-tight sm:text-5xl">Laporkan masalah layanan publik di Kabupaten Jember</h1>
                <p class="mt-5 max-w-xl text-lg leading-8 text-ink-700">
                    Jalan rusak, lampu jalan mati, sampah menumpuk, atau layanan desa yang lambat. Laporanmu diteruskan ke petugas kecamatan dan wajib ditindaklanjuti dalam {{ $slaDays }} hari kerja.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('pengaduan.create') }}" class="btn btn-primary px-6 py-3 text-base">Buat pengaduan</a>
                    <a href="#cara-melapor" class="btn btn-secondary px-6 py-3 text-base">Cara melapor</a>
                </div>
                <p class="mt-4 text-sm text-ink-700">Perlu akun warga. Gratis, cukup email dan nomor HP.</p>
            </div>

            <div class="panel self-start p-5">
                <h2 class="text-lg font-bold">Lacak pengaduan</h2>
                <p class="mt-1 text-sm text-ink-700">Tidak perlu masuk. Masukkan kode tiket yang kamu terima.</p>
                <form method="GET" action="{{ route('lacak') }}" class="mt-4">
                    <label for="kode" class="field-label">Kode tiket</label>
                    <input id="kode" name="kode" required placeholder="WDL-{{ now()->year }}-000123" class="field-input font-mono uppercase">
                    <button class="btn btn-primary mt-3 w-full">Lacak</button>
                </form>
                <dl class="mt-5 grid grid-cols-3 border-t border-line pt-4 text-center">
                    <div><dt class="text-xs text-ink-700">Menunggu</dt><dd class="mt-1 text-xl font-bold">{{ $number($stats['pending']) }}</dd></div>
                    <div class="border-x border-line"><dt class="text-xs text-ink-700">Ditangani</dt><dd class="mt-1 text-xl font-bold">{{ $number($stats['diproses']) }}</dd></div>
                    <div><dt class="text-xs text-ink-700">Selesai</dt><dd class="mt-1 text-xl font-bold">{{ $number($stats['selesai']) }}</dd></div>
                </dl>
            </div>
        </div>
    </section>

    <section class="container-page py-12">
        <dl class="grid gap-px overflow-hidden rounded-md border border-line bg-line sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['Pengaduan masuk', $number($stats['total'])],
                ['Selesai tepat waktu', $stats['within_sla_percentage'] !== null ? $stats['within_sla_percentage'].'%' : '—'],
                ['Rata-rata waktu selesai', $stats['average_days'] !== null ? str_replace('.', ',', $stats['average_days']).' hari' : '—'],
                ['Kecamatan terlayani', $stats['districts']],
            ] as [$label, $value])
                <div class="bg-white p-5">
                    <dt class="text-sm text-ink-700">{{ $label }}</dt>
                    <dd class="mt-1 text-3xl font-extrabold">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    <section id="cara-melapor" class="container-page scroll-mt-6 pb-12">
        <h2 class="text-2xl font-bold">Cara melapor</h2>
        <ol class="mt-6 grid gap-6 md:grid-cols-4">
            @foreach ([
                ['Isi laporan', 'Pilih kategori, tandai lokasi di peta, tulis kronologi, dan unggah 1–3 foto bukti.'],
                ['Simpan kode tiket', 'Kamu langsung menerima kode tiket, misalnya WDL-'.now()->year.'-000123.'],
                ['Petugas menindaklanjuti', 'Petugas kecamatan memverifikasi dalam '.$slaDays.' hari kerja. Bila terlambat, kabupaten ikut memantau.'],
                ['Beri penilaian', 'Setelah selesai, lihat foto hasil penanganan dan beri penilaian 1–5.'],
            ] as $index => [$title, $description])
                <li class="border-t-4 border-primary-600 pt-4">
                    <p class="text-sm font-semibold text-primary-700">Langkah {{ $index + 1 }}</p>
                    <h3 class="mt-1 font-bold">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-6 text-ink-700">{{ $description }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="container-page grid gap-10 pb-12 lg:grid-cols-[1fr_340px]">
        <div>
            <div class="flex flex-wrap items-baseline justify-between gap-3">
                <h2 class="text-2xl font-bold">Aduan terbaru</h2>
                <a href="{{ route('peta') }}" class="link text-sm">Lihat di peta</a>
            </div>
            <div class="panel mt-4 divide-y divide-line">
                @forelse ($recentComplaints as $complaint)
                    <a href="{{ route('lacak', ['kode' => $complaint->ticket_code]) }}" class="flex items-start justify-between gap-4 px-4 py-3 hover:bg-paper">
                        <span class="min-w-0">
                            <span class="ticket block text-xs">{{ $complaint->ticket_code }}</span>
                            <span class="block truncate font-semibold">{{ $complaint->title }}</span>
                            <span class="block text-sm text-ink-700">{{ $complaint->category->name }} · Kec. {{ $complaint->district->name }} · {{ $complaint->created_at->diffForHumans() }}</span>
                        </span>
                        <x-status-badge :status="$complaint->status" class="shrink-0" />
                    </a>
                @empty
                    <p class="px-4 py-8 text-center text-sm text-ink-700">Belum ada aduan.</p>
                @endforelse
            </div>
        </div>

        <div>
            <h2 class="text-2xl font-bold">Kategori</h2>
            <p class="mt-1 text-sm text-ink-700">Jumlah aduan 12 bulan terakhir.</p>
            <ul class="panel mt-4 divide-y divide-line text-sm">
                @foreach ($categories as $category)
                    <li class="flex justify-between gap-3 px-4 py-2.5">
                        <span>{{ $category->name }}</span>
                        <span class="font-semibold tabular-nums">{{ $number($category->complaints_count) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    @if ($announcements->isNotEmpty())
        <section class="container-page pb-4">
            <div class="flex flex-wrap items-baseline justify-between gap-3">
                <h2 class="text-2xl font-bold">Pengumuman</h2>
                <a href="{{ route('pengumuman.index') }}" class="link text-sm">Semua pengumuman</a>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-3">
                @foreach ($announcements as $announcement)
                    @include('announcements._card', ['announcement' => $announcement])
                @endforeach
            </div>
        </section>
    @endif
@endsection
