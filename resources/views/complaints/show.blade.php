@extends('layouts.app')

@section('title', $complaint->ticket_code.' · WADULIN')

@php
    $timeline = $complaint->statusHistories->map(fn ($history) => ['at' => $history->created_at, 'type' => 'status', 'item' => $history])
        ->concat($complaint->dispositions->map(fn ($disposition) => ['at' => $disposition->created_at, 'type' => 'disposisi', 'item' => $disposition]))
        ->sortBy('at');
@endphp

@section('content')
    <div class="container-page py-10">
        <a href="{{ route('pengaduan.index') }}" class="link text-sm">← Pengaduan saya</a>

        <div class="mt-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="ticket">{{ $complaint->ticket_code }}</p>
                <h1 class="mt-1 text-3xl font-extrabold">{{ $complaint->title }}</h1>
                <p class="mt-1 text-ink-700">{{ $complaint->category->name }} · Kec. {{ $complaint->district->name }} · {{ $complaint->created_at->translatedFormat('d F Y, H.i') }} WIB</p>
            </div>
            <x-status-badge :status="$complaint->status" :escalated="$complaint->isEscalated()" />
        </div>

        @if ($complaint->status === \App\Enums\ComplaintStatus::Pending)
            <p class="alert-info mt-5">Menunggu verifikasi petugas Kecamatan {{ $complaint->district->name }}. Batas tindak lanjut: <strong>{{ $complaint->due_at->translatedFormat('d F Y') }}</strong>.</p>
        @elseif ($complaint->isEscalated())
            <p class="alert-info mt-5">Pengaduan ini melewati batas waktu dan kini ikut dipantau Pemkab.</p>
        @endif

        <div class="mt-6 grid gap-8 lg:grid-cols-[1fr_360px]">
            <div class="space-y-6">
                <section class="panel p-5">
                    <h2 class="font-bold">Isi laporan</h2>
                    <p class="mt-2 whitespace-pre-line leading-7 text-ink-700">{{ $complaint->description }}</p>
                    @if ($complaint->address)
                        <p class="mt-3 text-sm"><span class="font-semibold">Alamat/patokan:</span> {{ $complaint->address }}</p>
                    @endif

                    @if ($complaint->attachments->isNotEmpty())
                        <div class="mt-5 grid grid-cols-3 gap-3">
                            @foreach ($complaint->attachments as $photo)
                                <figure>
                                    <a href="{{ Storage::disk('public')->url($photo->file_path) }}" target="_blank" rel="noopener">
                                        <img src="{{ Storage::disk('public')->url($photo->file_path) }}" alt="Foto {{ $photo->type->value }}" class="aspect-square w-full rounded border border-line object-cover">
                                    </a>
                                    <figcaption class="mt-1 text-xs text-ink-700">{{ $photo->type === \App\Enums\AttachmentType::Hasil ? 'Hasil penanganan' : 'Bukti laporan' }}</figcaption>
                                </figure>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="panel p-5">
                    <h2 class="font-bold">Riwayat penanganan</h2>
                    <ol class="mt-4 space-y-5 border-l-2 border-line pl-5">
                        @foreach ($timeline as $entry)
                            <li class="relative">
                                <span @class(['absolute -left-[27px] top-1.5 h-3 w-3 rounded-full ring-4 ring-white', 'bg-violet-600' => $entry['type'] === 'disposisi', 'bg-primary-600' => $entry['type'] === 'status'])></span>
                                <p class="text-xs text-ink-700">{{ $entry['at']->translatedFormat('d M Y, H.i') }} WIB</p>
                                @if ($entry['type'] === 'status')
                                    @php $history = $entry['item']; @endphp
                                    <p class="font-semibold">{{ $history->from_status ? $history->to_status->label() : 'Pengaduan dikirim' }}</p>
                                    @if ($history->note)
                                        <p @class(['mt-1 text-sm', 'rounded border-l-4 border-red-700 bg-red-50 px-3 py-2 text-red-900' => $history->to_status === \App\Enums\ComplaintStatus::Ditolak, 'text-ink-700' => $history->to_status !== \App\Enums\ComplaintStatus::Ditolak])>{{ $history->note }}</p>
                                    @endif
                                @else
                                    <p class="font-semibold">Dialihkan ke Kec. {{ $entry['item']->toDistrict->name }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </section>
            </div>

            <div class="space-y-6">
                @if ($complaint->rating)
                    <section class="panel p-5">
                        <h2 class="font-bold">Penilaianmu</h2>
                        <p class="mt-2 text-2xl tracking-widest text-amber-500" aria-label="{{ $complaint->rating }} dari 5">{{ str_repeat('★', $complaint->rating) }}<span class="text-line">{{ str_repeat('★', 5 - $complaint->rating) }}</span></p>
                        @if ($complaint->feedback)
                            <p class="mt-2 text-sm text-ink-700">“{{ $complaint->feedback }}”</p>
                        @endif
                    </section>
                @endif

                @can('rate', $complaint)
                    <section class="panel p-5">
                        <h2 class="font-bold">Beri penilaian</h2>
                        <p class="mt-1 text-sm text-ink-700">Penilaianmu membantu Pemkab menilai kinerja kecamatan.</p>
                        <form method="POST" action="{{ route('pengaduan.rating', $complaint) }}" class="mt-4 space-y-4">
                            @csrf
                            <fieldset>
                                <legend class="field-label">Seberapa puas kamu?</legend>
                                <div class="mt-2 grid grid-cols-5 gap-1">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="rating" value="{{ $star }}" class="peer sr-only" @checked((int) old('rating') === $star) required>
                                            <span class="block rounded border border-ink-900/30 py-2 text-center text-sm font-semibold peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-focus-visible:outline-3 peer-focus-visible:outline-accent-500">{{ $star }}★</span>
                                        </label>
                                    @endfor
                                </div>
                                <p class="mt-1 flex justify-between text-xs text-ink-700"><span>Tidak puas</span><span>Sangat puas</span></p>
                                @error('rating')<p class="field-error">{{ $message }}</p>@enderror
                            </fieldset>
                            <div>
                                <label for="feedback" class="field-label">Ulasan <span class="font-normal text-ink-700">(opsional)</span></label>
                                <textarea id="feedback" name="feedback" rows="3" maxlength="1000" class="field-input">{{ old('feedback') }}</textarea>
                                @error('feedback')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <button class="btn btn-primary w-full">Kirim penilaian</button>
                        </form>
                    </section>
                @endcan

                @can('cancel', $complaint)
                    <section class="panel p-5">
                        <h2 class="font-bold">Batalkan pengaduan</h2>
                        <p class="mt-1 text-sm text-ink-700">Bisa selama petugas belum memproses. Tidak bisa diurungkan.</p>
                        <form method="POST" action="{{ route('pengaduan.cancel', $complaint) }}" class="mt-4 space-y-3"
                              onsubmit="return confirm('Batalkan pengaduan ini?')">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label for="reason" class="field-label">Alasan <span class="font-normal text-ink-700">(opsional)</span></label>
                                <textarea id="reason" name="reason" rows="2" maxlength="500" class="field-input">{{ old('reason') }}</textarea>
                                @error('reason')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <button class="btn btn-danger w-full">Batalkan pengaduan</button>
                        </form>
                    </section>
                @endcan
            </div>
        </div>
    </div>
@endsection
