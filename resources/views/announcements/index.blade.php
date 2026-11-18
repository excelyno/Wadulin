@extends('layouts.app')

@section('title', 'Pengumuman · WADULIN')

@section('content')
    <div class="container-page py-10">
        <h1 class="text-3xl font-extrabold">Pengumuman</h1>
        <p class="mt-2 text-ink-700">Informasi resmi seputar layanan pengaduan dari Pemkab Jember.</p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($announcements as $announcement)
                @include('announcements._card', ['announcement' => $announcement])
            @empty
                <p class="panel p-10 text-center text-sm text-ink-700 sm:col-span-2 lg:col-span-3">Belum ada pengumuman.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $announcements->links() }}</div>
    </div>
@endsection
