@extends('layouts.app')

@section('title', 'FAQ · WADULIN')

@section('content')
    <div class="container-page max-w-3xl py-10">
        <h1 class="text-3xl font-extrabold">Bantuan</h1>
        <p class="mt-2 text-ink-700">Jawaban untuk pertanyaan yang paling sering diajukan warga.</p>

        <div class="panel mt-8 divide-y divide-line">
            @forelse ($faqs as $faq)
                <details class="group">
                    <summary class="flex cursor-pointer items-center justify-between gap-4 px-5 py-4 font-semibold">
                        {{ $faq->question }}
                        <span class="text-xl leading-none text-primary-700 group-open:hidden">+</span><span class="hidden text-xl leading-none text-primary-700 group-open:inline">−</span>
                    </summary>
                    <p class="whitespace-pre-line px-5 pb-4 leading-7 text-ink-700">{{ $faq->answer }}</p>
                </details>
            @empty
                <p class="p-10 text-center text-sm text-ink-700">Belum ada FAQ.</p>
            @endforelse
        </div>

        <p class="mt-8 text-sm text-ink-700">Masih ada pertanyaan? <a href="{{ route('pengaduan.create') }}" class="link">Kirim pengaduan</a> atau <a href="{{ route('lacak') }}" class="link">lacak tiketmu</a>.</p>
    </div>
@endsection
