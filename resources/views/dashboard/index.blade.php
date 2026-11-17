@extends('layouts.app')

@section('title', 'Dashboard Pengaduan · Wadulin')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
    <div class="mx-auto max-w-7xl px-5 py-12 sm:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Dashboard Pengaduan</h1>
                <p class="mt-2 text-slate-600">Daftar dan lokasi pengaduan warga, diperbarui secara langsung.</p>
            </div>
            <a href="{{ route('pengaduan.create') }}"
                 class="inline-flex items-center justify-center gap-2 rounded-md bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                    <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/>
                </svg>
                Ajukan Pengaduan
            </a>
        </div>

        @guest
            <div class="mt-6 flex flex-col gap-3 rounded-xl border border-primary-600/20 bg-primary-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-primary-700">
                    Anda melihat dashboard sebagai <span class="font-semibold">pengunjung</span>. Masuk atau daftar sebagai petugas untuk mengelola pengaduan.
                </p>
                <div class="flex shrink-0 gap-2">
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-md border border-primary-600/30 bg-white px-3 py-2 text-sm font-medium text-primary-700 hover:bg-primary-50">Masuk</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-primary-700">Daftar</a>
                </div>
            </div>
        @else
            <div class="mt-6 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 shrink-0">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.85-8.64a.75.75 0 0 0-1.22-.87l-2.6 3.64-1.49-1.49a.75.75 0 1 0-1.06 1.06l2.14 2.14a.75.75 0 0 0 1.13-.09l3.1-4.39Z" clip-rule="evenodd"/>
                </svg>
                <span>Selamat datang, <strong>{{ auth()->user()->name }}</strong>. Anda dapat memperbarui status dan menghapus pengaduan.</span>
            </div>
        @endguest

        <div class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
            @php
                $statCards = [
                    ['label' => 'Total Pengaduan', 'value' => $stats['total'], 'ring' => 'bg-primary-50 text-primary-600', 'pending' => false],
                    ['label' => 'Diajukan', 'value' => $stats['pending'], 'ring' => 'bg-amber-50 text-amber-600', 'pending' => false],
                    ['label' => 'Diproses', 'value' => $stats['proses'], 'ring' => 'bg-sky-50 text-sky-600', 'pending' => false],
                    ['label' => 'Selesai', 'value' => $stats['selesai'], 'ring' => 'bg-emerald-50 text-emerald-600', 'pending' => false],
                ];
            @endphp
            @foreach ($statCards as $card)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg {{ $card['ring'] }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                                <path d="M3 4.5A1.5 1.5 0 0 1 4.5 3h15A1.5 1.5 0 0 1 21 4.5v15a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5v-15ZM6 7.25A.75.75 0 0 1 6.75 6.5h6.5a.75.75 0 0 1 0 1.5h-6.5A.75.75 0 0 1 6 7.25Zm0 4A.75.75 0 0 1 6.75 10.5h10.5a.75.75 0 0 1 0 1.5H6.75A.75.75 0 0 1 6 11.25Zm0 4A.75.75 0 0 1 6.75 14.5h8a.75.75 0 0 1 0 1.5h-8A.75.75 0 0 1 6 15.25Z"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($card['value']) }}</p>
                            <p class="text-sm text-slate-500">{{ $card['label'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-600/20">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-rose-500"></span>
                        </span>
                        Live
                    </span>
                    <h2 class="text-base font-semibold text-slate-900">Peta Pengaduan</h2>
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Diajukan</span>
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-sky-500"></span> Diproses</span>
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Selesai</span>
                    <span id="map-updated" class="text-slate-400">Memuat…</span>
                </div>
            </div>
            <div id="map" class="h-[480px] w-full"></div>
        </div>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg font-semibold text-slate-900">Daftar Pengaduan</h2>
            <div class="flex flex-wrap gap-2">
                @php
                    $filters = [
                        '' => 'Semua',
                        'pending' => 'Diajukan',
                        'proses' => 'Diproses',
                        'selesai' => 'Selesai',
                    ];
                @endphp
                @foreach ($filters as $value => $label)
                    @php
                        $isActive = ($statusFilter === $value);
                        $href = $value === '' ? route('dashboard') : route('dashboard', ['status' => $value]);
                    @endphp
                    <a href="{{ $href }}"
                       class="rounded-full px-3.5 py-1.5 text-sm font-medium {{ $isActive ? 'bg-primary-600 text-white' : 'bg-white text-slate-600 ring-1 ring-inset ring-slate-300 hover:bg-slate-100' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @if ($pengaduans->isEmpty())
            <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <p class="text-slate-500">Belum ada pengaduan{{ $statusFilter ? ' dengan status ini' : '' }}.</p>
            </div>
        @else
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($pengaduans as $p)
                    <div class="flex flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-2">
                            <span class="font-mono text-xs text-slate-400">{{ $p->kode_pengaduan }}</span>
                            <x-status-badge :status="$p->status" />
                        </div>
                        <h3 class="mt-2 line-clamp-2 text-base font-semibold text-slate-900">
                             <a href="{{ route('pengaduan.show', $p) }}" class="hover:text-primary-700">{{ $p->judul }}</a>
                        </h3>
                        <div class="mt-2"><x-kategori-badge :kategori="$p->kategori" /></div>
                        <p class="mt-3 flex items-start gap-1.5 text-sm text-slate-600 line-clamp-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400">
                                <path fill-rule="evenodd" d="M11.54 22.35c.19.13.38.21.6.21a1 1 0 0 0 .61-.21l.06-.05C16.41 19.45 21 15.06 21 10.5A9 9 0 1 0 3 10.5c0 4.56 4.59 8.95 8.54 11.8l-.01.05ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                            </svg>
                            {{ $p->alamat }}
                        </p>
                        @if ($p->foto)
                            <img src="{{ Illuminate\Support\Facades\Storage::url($p->foto) }}"
                                 alt="Foto pengaduan"
                                 class="mt-3 h-32 w-full rounded-lg border border-slate-200 object-cover">
                        @endif
                        <div class="mt-3 text-xs text-slate-400">
                            {{ $p->created_at->translatedFormat('d M Y, H:i') }} · {{ $p->nama_pelapor }}
                        </div>

                        @auth
                            <div class="mt-4 space-y-2 border-t border-slate-100 pt-3">
                                <form method="POST" action="{{ route('pengaduan.status', $p) }}">
                                    @csrf
                                    @method('PATCH')
                                    <label for="status-{{ $p->id }}" class="text-xs font-medium text-slate-500">Ubah status</label>
                                    <select id="status-{{ $p->id }}" name="status" onchange="this.form.submit()"
                                             class="mt-1 block w-full rounded-md border-0 bg-white px-2.5 py-1.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-primary-600">
                                        @foreach (\App\Enums\StatusPengaduan::cases() as $st)
                                            <option value="{{ $st->value }}" {{ $p->status === $st ? 'selected' : '' }}>{{ $st->label() }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                <form method="POST" action="{{ route('pengaduan.destroy', $p) }}"
                                      onsubmit="return confirm('Hapus pengaduan {{ $p->kode_pengaduan }}? Tindakan ini tidak dapat dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 text-xs font-medium text-rose-600 hover:text-rose-700 hover:underline">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443A4.73 4.73 0 0 0 4.34 4.5H3a.75.75 0 0 0 0 1.5h.42l.78 9.78A2.75 2.75 0 0 0 6.94 18.5h6.12a2.75 2.75 0 0 0 2.74-2.72l.78-9.78H17a.75.75 0 0 0 0-1.5h-1.34A4.73 4.73 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM9 3.75c0-.69.56-1.25 1.25-1.25h2.5c.69 0 1.25.56 1.25 1.25v.5H9v-.5Z" clip-rule="evenodd"/>
                                        </svg>
                                        Hapus pengaduan
                                    </button>
                                </form>
                            </div>
                        @endauth

                        <div class="mt-4 flex-1"></div>
                        <a href="{{ route('pengaduan.show', $p) }}" class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-primary-600 hover:text-primary-700">
                            Lihat detail
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.64l-3.72-3.72a.75.75 0 1 1 1.06-1.06l5 5a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 1 1-1.06-1.06l3.72-3.72H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $pengaduans->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            const map = L.map('map').setView([-6.2088, 106.8456], 11);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
                subdomains: 'abcd',
                maxZoom: 20,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>'
            }).addTo(map);

            const markerLayer = L.layerGroup().addTo(map);
            const updatedEl = document.getElementById('map-updated');
            const geoUrl = '{{ route('pengaduan.geojson') }}';

            function escapeHtml(value) {
                return String(value || '').replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }

            function buildPopup(p) {
                return '<div style="min-width:200px">'
                    + '<div style="font-weight:600;font-size:13px;line-height:1.2;margin-bottom:4px">' + escapeHtml(p.judul) + '</div>'
                    + '<div style="font-size:11px;color:#475569;margin-bottom:6px">' + escapeHtml(p.kategori_label) + ' &middot; ' + escapeHtml(p.status_label) + '</div>'
                    + '<div style="font-size:11px;color:#64748b;line-height:1.3">' + escapeHtml(p.alamat) + '</div>'
                    + '<div style="font-size:10px;color:#94a3b8;margin-top:6px">' + escapeHtml(p.kode) + ' &middot; ' + escapeHtml(p.created_at) + '</div>'
                    + '<a href="' + p.url + '" style="display:inline-block;margin-top:8px;font-size:12px;color:#4f46e5;font-weight:600">Lihat detail &rarr;</a>'
                    + '</div>';
            }

            async function refresh() {
                try {
                    const res = await fetch(geoUrl, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
                    const json = await res.json();
                    markerLayer.clearLayers();

                    (json.data || []).forEach(function (p) {
                        const marker = L.circleMarker([p.lat, p.lng], {
                            radius: 8,
                            fillColor: p.status_color,
                            color: '#ffffff',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.9
                        });
                        marker.bindPopup(buildPopup(p));
                        marker.addTo(markerLayer);
                    });

                    updatedEl.textContent = 'Diperbarui ' + new Date().toLocaleTimeString('id-ID');
                } catch (e) {
                    updatedEl.textContent = 'Gagal memuat data.';
                }
            }

            refresh();
            setInterval(refresh, 15000);
        })();
    </script>
@endpush
