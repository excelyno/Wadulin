@extends('layouts.admin')

@section('title', 'FAQ')

@section('content')
    <div class="mb-5">
        <h1 class="text-2xl font-extrabold">FAQ</h1>
        <p class="mt-1 text-sm text-ink-700">Pertanyaan umum yang tampil di halaman publik, diurutkan dari angka urutan terkecil.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-3 xl:col-span-2">
            @forelse ($faqs as $faq)
                <details class="panel">
                    <summary class="flex cursor-pointer items-center justify-between gap-4 px-5 py-4">
                        <span class="font-medium"><span class="mr-2 font-mono text-xs text-ink-700">#{{ $faq->sort_order }}</span>{{ $faq->question }}</span>
                        <span class="shrink-0 rounded border px-2 py-0.5 text-xs font-semibold {{ $faq->is_published ? 'border-green-300 bg-green-50 text-green-900' : 'border-slate-300 bg-slate-50 text-ink-700' }}">
                            {{ $faq->is_published ? 'Tampil' : 'Disembunyikan' }}
                        </span>
                    </summary>
                    <div class="border-t border-line px-5 py-4">
                        <form method="POST" action="{{ route('kabupaten.faq.update', $faq) }}" class="grid gap-3">
                            @csrf
                            @method('PUT')
                            @include('admin.faqs._fields', ['faq' => $faq])
                            <div>
                                <button class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('kabupaten.faq.destroy', $faq) }}" class="mt-3" onsubmit="return confirm('Hapus FAQ ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-sm font-semibold text-red-700 hover:underline">Hapus</button>
                        </form>
                    </div>
                </details>
            @empty
                <p class="panel p-10 text-center text-sm text-ink-700">Belum ada FAQ.</p>
            @endforelse
        </div>

        <section class="h-fit panel p-5">
            <h2 class="font-semibold">Tambah FAQ</h2>
            <form method="POST" action="{{ route('kabupaten.faq.store') }}" class="mt-3 grid gap-3">
                @csrf
                @include('admin.faqs._fields', ['faq' => null])
                <button class="btn btn-primary">Tambah</button>
            </form>
        </section>
    </div>
@endsection
