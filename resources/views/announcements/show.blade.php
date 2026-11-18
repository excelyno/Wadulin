@extends('layouts.app')

@section('title', $announcement->title.' · WADULIN')

@section('content')
    <article class="container-page max-w-3xl py-10">
        <a href="{{ route('pengumuman.index') }}" class="link text-sm">← Semua pengumuman</a>
        <p class="mt-6 text-sm text-ink-700">{{ $announcement->published_at->translatedFormat('l, d F Y') }}</p>
        <h1 class="mt-2 text-3xl font-extrabold">{{ $announcement->title }}</h1>
        @if ($announcement->cover_path)
            <img src="{{ Storage::disk('public')->url($announcement->cover_path) }}" alt="" class="mt-6 w-full rounded-md border border-line object-cover">
        @endif
        <div class="mt-6 whitespace-pre-line text-base leading-8 text-ink-900">{{ $announcement->body }}</div>
    </article>
@endsection
