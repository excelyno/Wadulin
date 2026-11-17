@extends('layouts.app')

@section('title', 'Lacak Pengaduan · Wadulin')

@section('content')
    <div class="mx-auto max-w-3xl px-5 py-20 sm:px-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm sm:p-12">
            <span class="text-xs font-bold tracking-[.18em] text-primary-700">LACAK TIKET</span>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Pantau pengaduan Anda</h1>
            <p class="mt-3 text-slate-600">Masukkan kode tiket yang diterima setelah pengaduan dikirim. Contoh: <span class="font-mono font-medium">PDG-261117-0042</span>.</p>

            <form method="GET" action="{{ route('pengaduan.track') }}" class="mt-8">
                <label for="kode" class="text-sm font-medium text-slate-700">Kode tiket</label>
                <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                    <input id="kode" name="kode" value="{{ old('kode') }}" required autofocus
                           class="min-w-0 flex-1 rounded-lg border-0 px-3 py-3 font-mono uppercase text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-primary-600"
                           placeholder="PDG-261117-0042">
                    <button class="rounded-lg bg-primary-600 px-5 py-3 text-sm font-semibold text-white hover:bg-primary-700">Lacak pengaduan</button>
                </div>
                @error('kode')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </form>
        </div>
    </div>
@endsection
