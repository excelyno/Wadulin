@extends('layouts.app')

@section('title', $pengaduan->judul . ' · Wadulin')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                    <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.61l3.72 3.72a.75.75 0 1 1-1.06 1.06l-5-5a.75.75 0 0 1 0-1.06l5-5a.75.75 0 1 1 1.06 1.06L5.61 9.25h10.64A.75.75 0 0 1 17 10Z" clip-rule="evenodd"/>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>

        <div class="grid gap-8 lg:grid-cols-5">
            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-mono font-medium text-slate-600">{{ $pengaduan->kode_pengaduan }}</span>
                        <x-status-badge :status="$pengaduan->status" />
                        <x-kategori-badge :kategori="$pengaduan->kategori" />
                    </div>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">{{ $pengaduan->judul }}</h1>

                    <dl class="mt-6 space-y-4 text-sm">
                        <div>
                            <dt class="font-medium text-slate-500">Deskripsi</dt>
                            <dd class="mt-1 whitespace-pre-line text-slate-700">{{ $pengaduan->deskripsi }}</dd>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="font-medium text-slate-500">Alamat</dt>
                                <dd class="mt-1 flex items-start gap-1.5 text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400">
                                        <path fill-rule="evenodd" d="M11.54 22.35c.19.13.38.21.6.21a1 1 0 0 0 .61-.21l.06-.05C16.41 19.45 21 15.06 21 10.5A9 9 0 1 0 3 10.5c0 4.56 4.59 8.95 8.54 11.8l-.01.05ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $pengaduan->alamat }}
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">Pelapor</dt>
                                <dd class="mt-1 text-slate-700">{{ $pengaduan->nama_pelapor }}</dd>
                                @if ($pengaduan->kontak_pelapor)
                                    <dd class="text-slate-500">{{ $pengaduan->kontak_pelapor }}</dd>
                                @endif
                            </div>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">Dilaporkan pada</dt>
                            <dd class="mt-1 text-slate-700">{{ $pengaduan->created_at->translatedFormat('l, d F Y · H:i') }}</dd>
                        </div>
                    </dl>

                    @if ($pengaduan->foto)
                        <div class="mt-6">
                            <p class="text-sm font-medium text-slate-500">Foto Pendukung</p>
                            <img src="{{ Illuminate\Support\Facades\Storage::url($pengaduan->foto) }}"
                                 alt="Foto pengaduan"
                                 class="mt-2 max-h-96 w-auto rounded-lg border border-slate-200 object-cover">
                        </div>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-semibold text-slate-900">Lokasi Pengaduan</h2>
                    <div id="map" class="mt-3 h-80 w-full overflow-hidden rounded-lg ring-1 ring-slate-300"
                         data-lat="{{ $pengaduan->latitude }}" data-lng="{{ $pengaduan->longitude }}"
                         data-judul="{{ $pengaduan->judul }}" data-alamat="{{ $pengaduan->alamat }}"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            const el = document.getElementById('map');
            const lat = parseFloat(el.dataset.lat);
            const lng = parseFloat(el.dataset.lng);

            const map = L.map('map').setView([lat, lng], 15);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
                subdomains: 'abcd',
                maxZoom: 20,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>'
            }).addTo(map);

            const marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup('<strong>' + el.dataset.judul + '</strong><br>' + el.dataset.alamat);
        })();
    </script>
@endpush
