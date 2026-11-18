@extends('layouts.admin')

@php
    $panel = auth()->user()->role->panelPrefix();
    $canUpdateStatus = auth()->user()->can('updateStatus', $complaint);
    $canDispose = auth()->user()->can('dispose', $complaint);
    $transitions = $complaint->status->officerTransitions();
    $actionLabels = ['diproses' => 'Proses', 'selesai' => 'Selesaikan', 'ditolak' => 'Tolak'];

    $timeline = $complaint->statusHistories->map(fn ($h) => ['at' => $h->created_at, 'type' => 'status', 'item' => $h])
        ->concat($complaint->dispositions->map(fn ($d) => ['at' => $d->created_at, 'type' => 'disposisi', 'item' => $d]))
        ->sortBy('at');
@endphp

@section('title', $complaint->ticket_code)

@section('content')
    <a href="{{ route($panel.'.pengaduan.index') }}" class="text-sm font-medium text-ink-700 hover:text-slate-800">← Kembali ke daftar</a>

    <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="font-mono text-sm text-ink-700">{{ $complaint->ticket_code }}</p>
            <h1 class="mt-1 text-2xl font-extrabold">{{ $complaint->title }}</h1>
            <p class="mt-1 text-sm text-ink-700">{{ $complaint->category->name }} · Kec. {{ $complaint->district->name }} · {{ $complaint->created_at->translatedFormat('d M Y, H.i') }} WIB</p>
        </div>
        <x-status-badge :status="$complaint->status" :escalated="$complaint->isEscalated()" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="panel p-5">
                <h2 class="font-semibold">Deskripsi</h2>
                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $complaint->description }}</p>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-ink-700">Alamat / patokan</dt><dd>{{ $complaint->address ?: '—' }}</dd></div>
                    <div><dt class="text-ink-700">Koordinat</dt><dd class="font-mono text-xs">{{ $complaint->latitude }}, {{ $complaint->longitude }}</dd></div>
                    @if ($complaint->qrLocation)
                        <div><dt class="text-ink-700">Sumber</dt><dd>QR: {{ $complaint->qrLocation->name }}</dd></div>
                    @endif
                </dl>
                <div id="map" class="mt-4 h-64 overflow-hidden rounded-md border border-line"
                     data-lat="{{ $complaint->latitude }}" data-lng="{{ $complaint->longitude }}"></div>
            </section>

            @foreach (['bukti' => 'Foto bukti', 'hasil' => 'Foto hasil'] as $type => $heading)
                @php $photos = $complaint->attachments->where('type.value', $type); @endphp
                @if ($photos->isNotEmpty())
                    <section class="panel p-5">
                        <h2 class="font-semibold">{{ $heading }}</h2>
                        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($photos as $photo)
                                <a href="{{ Storage::disk('public')->url($photo->file_path) }}" target="_blank" rel="noopener">
                                    <img src="{{ Storage::disk('public')->url($photo->file_path) }}" alt="{{ $heading }} {{ $complaint->ticket_code }}" class="aspect-square w-full rounded-md object-cover">
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach

            <section class="panel p-5">
                <h2 class="font-semibold">Timeline</h2>
                <ol class="mt-4 space-y-4 border-l-2 border-line pl-5">
                    @foreach ($timeline as $entry)
                        <li class="relative">
                            <span class="absolute -left-[27px] top-1 h-3 w-3 rounded-full border-2 border-white {{ $entry['type'] === 'disposisi' ? 'bg-violet-600' : 'bg-primary-600' }}"></span>
                            <p class="text-xs text-ink-700">{{ $entry['at']->translatedFormat('d M Y, H.i') }} WIB</p>
                            @if ($entry['type'] === 'status')
                                @php $history = $entry['item']; @endphp
                                <p class="text-sm font-medium">
                                    {{ $history->from_status ? $history->from_status->label().' → ' : 'Dibuat · ' }}{{ $history->to_status->label() }}
                                    <span class="font-normal text-ink-700">oleh {{ $history->changer->name }}</span>
                                </p>
                                @if ($history->note)
                                    <p class="mt-1 rounded-md bg-paper px-3 py-2 text-sm text-slate-700">
                                        @unless ($history->is_public)<span class="mr-1 rounded bg-slate-200 px-1.5 py-0.5 text-xs font-semibold text-ink-700">Internal</span>@endunless
                                        {{ $history->note }}
                                    </p>
                                @endif
                            @else
                                @php $disposition = $entry['item']; @endphp
                                <p class="text-sm font-medium">Disposisi: Kec. {{ $disposition->fromDistrict->name }} → Kec. {{ $disposition->toDistrict->name }}
                                    <span class="font-normal text-ink-700">oleh {{ $disposition->mover->name }}</span></p>
                                <p class="mt-1 rounded-md bg-violet-50 px-3 py-2 text-sm text-violet-900">{{ $disposition->reason }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>

        <div class="space-y-6">
            <section class="panel p-5">
                <h2 class="font-semibold">Pelapor</h2>
                <p class="mt-2 text-sm font-medium">{{ $complaint->user->name }}</p>
                @if ($complaint->is_anonymous)
                    <p class="text-xs text-ink-700">Memilih disembunyikan di tampilan publik.</p>
                @endif
                <p class="mt-2 text-sm"><a href="tel:{{ $complaint->user->phone }}" class="text-primary-700 hover:underline">{{ $complaint->user->phone }}</a></p>
                <p class="text-sm"><a href="mailto:{{ $complaint->user->email }}" class="text-primary-700 hover:underline">{{ $complaint->user->email }}</a></p>
            </section>

            <section class="panel p-5">
                <h2 class="font-semibold">SLA</h2>
                <p class="mt-2 text-sm">Batas: {{ $complaint->due_at->translatedFormat('d M Y, H.i') }} WIB</p>
                <div class="mt-2"><x-sla-chip :complaint="$complaint" /></div>
                @if ($complaint->rating)
                    <p class="mt-3 text-sm">Penilaian pelapor: <strong>{{ $complaint->rating }}/5</strong></p>
                    @if ($complaint->feedback)<p class="mt-1 text-sm text-ink-700">“{{ $complaint->feedback }}”</p>@endif
                @endif
            </section>

            @if ($canUpdateStatus)
                <section class="panel p-5">
                    <h2 class="font-semibold">Ubah status</h2>
                    <form method="POST" action="{{ route($panel.'.pengaduan.status', $complaint) }}" enctype="multipart/form-data" class="mt-3 space-y-3">
                        @csrf
                        @method('PATCH')
                        <fieldset class="flex flex-wrap gap-2">
                            <legend class="sr-only">Aksi</legend>
                            @foreach ($transitions as $transition)
                                <label class="cursor-pointer">
                                    <input type="radio" name="status" value="{{ $transition->value }}" class="peer sr-only" @checked(old('status') === $transition->value) required>
                                    <span class="inline-block rounded-md border border-slate-300 px-3 py-2 text-sm font-medium peer-checked:border-primary-600 peer-checked:bg-primary-50 peer-checked:text-primary-700 peer-focus-visible:ring-2 peer-focus-visible:ring-primary-600">
                                        {{ $actionLabels[$transition->value] }}
                                    </span>
                                </label>
                            @endforeach
                        </fieldset>
                        <div>
                            <label for="note" class="field-label">Catatan <span class="font-normal text-ink-700">(wajib untuk Tolak)</span></label>
                            <textarea id="note" name="note" rows="3" class="field-input mt-1 text-sm text-sm">{{ old('note') }}</textarea>
                        </div>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="is_public" value="0">
                            <input type="checkbox" name="is_public" value="1" @checked(old('is_public', '1') === '1') class="h-4 w-4 rounded border-ink-900/40 text-primary-600">
                            Tampilkan catatan ke pelapor & publik
                        </label>
                        <div>
                            <label for="photos" class="field-label">Foto hasil <span class="font-normal text-ink-700">(dianjurkan saat Selesai, maks. 3)</span></label>
                            <input id="photos" type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple
                                   class="mt-1 block w-full text-sm file:mr-3 file:rounded-md file:rounded-md file:border file:border-ink-900/30 file:bg-white file:px-3 file:py-1.5 file:text-sm file:font-semibold">
                        </div>
                        <button class="w-full btn btn-primary">Simpan status</button>
                    </form>
                </section>
            @endif

            @if ($canDispose)
                <section class="panel p-5">
                    <h2 class="font-semibold">Disposisi ke kecamatan lain</h2>
                    <form method="POST" action="{{ route($panel.'.pengaduan.disposisi', $complaint) }}" class="mt-3 space-y-3"
                          onsubmit="return confirm('Pindahkan pengaduan ini ke kecamatan lain?')">
                        @csrf
                        <div>
                            <label for="district_id" class="field-label">Kecamatan tujuan</label>
                            <select id="district_id" name="district_id" required class="field-input mt-1 text-sm text-sm">
                                <option value="">Pilih kecamatan…</option>
                                @foreach ($otherDistricts as $district)
                                    <option value="{{ $district->id }}" @selected((int) old('district_id') === $district->id)>{{ $district->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="reason" class="field-label">Alasan</label>
                            <textarea id="reason" name="reason" rows="3" required minlength="10" class="field-input mt-1 text-sm text-sm">{{ old('reason') }}</textarea>
                        </div>
                        <button class="w-full btn border border-violet-700 bg-white text-violet-800 hover:bg-violet-50">Disposisikan</button>
                    </form>
                </section>
            @endif

            @if (! $canUpdateStatus && ! $canDispose && ! $complaint->status->isFinal())
                <p class="panel p-5 text-sm text-ink-700">
                    Aksi hanya tersedia bila pengaduan tereskalasi (melewati batas SLA).
                </p>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            const el = document.getElementById('map');
            const latLng = [parseFloat(el.dataset.lat), parseFloat(el.dataset.lng)];
            const map = L.map(el).setView(latLng, 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap',
            }).addTo(map);
            L.marker(latLng).addTo(map);
        })();
    </script>
@endpush
