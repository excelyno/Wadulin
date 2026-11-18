@extends('layouts.admin')

@section('title', 'Kategori')

@section('content')
    <div class="mb-5">
        <h1 class="text-2xl font-extrabold">Kategori Pengaduan</h1>
        <p class="mt-1 text-sm text-ink-700">Kategori yang sudah dipakai tidak dihapus, cukup dinonaktifkan agar tidak muncul di form warga.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-3 xl:col-span-2">
            @foreach ($categories as $category)
                <details class="panel">
                    <summary class="flex cursor-pointer items-center justify-between gap-4 px-5 py-4">
                        <span>
                            <span class="font-medium">{{ $category->name }}</span>
                            <span class="ml-2 font-mono text-xs text-ink-700">{{ $category->icon }}</span>
                        </span>
                        <span class="flex items-center gap-3 text-sm">
                            <span class="text-ink-700">{{ $category->complaints_count }} pengaduan</span>
                            <span class="rounded border px-2 py-0.5 text-xs font-semibold {{ $category->is_active ? 'border-green-300 bg-green-50 text-green-900' : 'border-slate-300 bg-slate-50 text-ink-700' }}">
                                {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </span>
                    </summary>
                    <form method="POST" action="{{ route('kabupaten.kategori.update', $category) }}" class="grid gap-3 border-t border-line px-5 py-4 sm:grid-cols-2">
                        @csrf
                        @method('PUT')
                        @include('admin.categories._fields', ['category' => $category])
                        <div class="sm:col-span-2">
                            <button class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </details>
            @endforeach
        </div>

        <section class="h-fit panel p-5">
            <h2 class="font-semibold">Tambah kategori</h2>
            <form method="POST" action="{{ route('kabupaten.kategori.store') }}" class="mt-3 grid gap-3">
                @csrf
                @include('admin.categories._fields', ['category' => null])
                <button class="btn btn-primary">Tambah</button>
            </form>
        </section>
    </div>
@endsection
