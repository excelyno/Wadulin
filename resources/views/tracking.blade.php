@extends('layouts.app')

@section('title', 'Lacak Tiket · WADULIN')

@section('content')
    <div class="container-page py-10">
        <div class="max-w-2xl">
            <h1 class="text-3xl font-extrabold">Lacak pengaduan</h1>
            <p class="mt-2 text-ink-700">Masukkan kode tiket yang kamu terima setelah mengirim pengaduan. Tidak perlu masuk akun.</p>

            <form method="GET" action="{{ route('lacak') }}" class="mt-6">
                <label for="kode" class="field-label">Kode tiket</label>
                <div class="mt-1.5 flex flex-col gap-2 sm:flex-row">
                    <input id="kode" name="kode" value="{{ $code }}" required placeholder="WDL-{{ now()->year }}-000123"
                           @class(['field-input mt-0 flex-1 font-mono uppercase', 'field-input-invalid' => $code !== '' && $complaint === null])>
                    <button class="btn btn-primary px-6">Lacak</button>
                </div>
            </form>

            @if ($code !== '' && $complaint === null)
                <p class="alert-error mt-4" role="alert">Kode tiket tidak ditemukan: <strong class="font-mono">{{ $code }}</strong>. Periksa lagi formatnya, contoh: WDL-{{ now()->year }}-000123.</p>
            @elseif ($complaint)
                <section class="panel mt-8 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="ticket">{{ $complaint->ticket_code }}</p>
                            <h2 class="mt-1 text-xl font-bold">{{ $complaint->title }}</h2>
                            <p class="mt-1 text-sm text-ink-700">{{ $complaint->category->name }} · Kec. {{ $complaint->district->name }} · {{ $complaint->created_at->translatedFormat('d F Y') }}</p>
                        </div>
                        <x-status-badge :status="$complaint->status" :escalated="$complaint->isEscalated()" />
                    </div>

                    <h3 class="mt-6 font-semibold">Riwayat</h3>
                    <ol class="mt-3 space-y-4 border-l-2 border-line pl-5">
                        @foreach ($complaint->statusHistories as $history)
                            <li class="relative">
                                <span class="absolute -left-[27px] top-1.5 h-3 w-3 rounded-full bg-primary-600 ring-4 ring-white"></span>
                                <p class="text-xs text-ink-700">{{ $history->created_at->translatedFormat('d M Y, H.i') }} WIB</p>
                                <p class="font-semibold">{{ $history->from_status ? $history->to_status->label() : 'Pengaduan diterima' }}</p>
                                @if ($history->note)
                                    <p class="mt-1 text-sm text-ink-700">{{ $history->note }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif
        </div>
    </div>
@endsection
