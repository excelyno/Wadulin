@extends('layouts.admin')

@section('title', 'Pengumuman')

@section('content')
    <div class="mb-5">
        <h1 class="text-2xl font-extrabold">Pengumuman</h1>
        <p class="mt-1 text-sm text-ink-700">Pengumuman terbit tampil di beranda dan halaman publik. Matikan "Terbitkan" untuk menyembunyikan.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-3 xl:col-span-2">
            @forelse ($announcements as $announcement)
                <details class="panel">
                    <summary class="flex cursor-pointer flex-wrap items-center justify-between gap-3 px-5 py-4">
                        <span>
                            <span class="block font-medium">{{ $announcement->title }}</span>
                            <span class="block text-xs text-ink-700">{{ $announcement->author->name }} · {{ ($announcement->published_at ?? $announcement->created_at)->translatedFormat('d M Y') }}</span>
                        </span>
                        <span class="flex items-center gap-3">
                            @if ($announcement->is_published)
                                <a href="{{ route('pengumuman.show', $announcement) }}" target="_blank" class="text-xs font-semibold text-primary-700 hover:underline">Lihat ↗</a>
                            @endif
                            <span class="rounded border px-2 py-0.5 text-xs font-semibold {{ $announcement->is_published ? 'border-green-300 bg-green-50 text-green-900' : 'border-slate-300 bg-slate-50 text-ink-700' }}">
                                {{ $announcement->is_published ? 'Terbit' : 'Draf' }}
                            </span>
                        </span>
                    </summary>
                    <form method="POST" action="{{ route('kabupaten.pengumuman.update', $announcement) }}" enctype="multipart/form-data" class="grid gap-3 border-t border-line px-5 py-4">
                        @csrf
                        @method('PUT')
                        @include('admin.announcements._fields', ['announcement' => $announcement])
                        <div>
                            <button class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </details>
            @empty
                <p class="panel p-10 text-center text-sm text-ink-700">Belum ada pengumuman.</p>
            @endforelse
        </div>

        <section class="h-fit panel p-5">
            <h2 class="font-semibold">Tulis pengumuman</h2>
            <form method="POST" action="{{ route('kabupaten.pengumuman.store') }}" enctype="multipart/form-data" class="mt-3 grid gap-3">
                @csrf
                @include('admin.announcements._fields', ['announcement' => null])
                <button class="btn btn-primary">Simpan</button>
            </form>
        </section>
    </div>
@endsection
