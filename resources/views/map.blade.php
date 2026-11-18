@extends('layouts.app')

@section('title', 'Peta Pengaduan · WADULIN')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
    <div class="container-page py-10">
        <h1 class="text-3xl font-extrabold">Peta aduan</h1>
        <p class="mt-2 text-ink-700">{{ $markers->count() }} aduan terbaru di Kabupaten Jember. Nama pelapor tidak ditampilkan. Klik titik untuk melihat perkembangannya.</p>

        <div class="mt-4 flex flex-wrap gap-4 text-sm">
            @foreach ([\App\Enums\ComplaintStatus::Pending, \App\Enums\ComplaintStatus::Diproses, \App\Enums\ComplaintStatus::Selesai] as $status)
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full" style="background: {{ $status->hexColor() }}"></span>{{ $status->label() }}</span>
            @endforeach
        </div>

        <div id="map" class="mt-4 h-[70vh] min-h-[420px] overflow-hidden rounded-md border border-line"></div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            const markers = @json($markers);
            const map = L.map('map').setView([-8.1724, 113.7008], 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap',
            }).addTo(map);

            const escape = (text) => String(text).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

            markers.forEach(function (m) {
                L.circleMarker([m.lat, m.lng], { radius: 7, color: '#fff', weight: 2, fillColor: m.color, fillOpacity: 0.9 })
                    .addTo(map)
                    .bindPopup(
                        '<p style="font-family:monospace;font-size:11px;color:#64748b">' + escape(m.ticket) + '</p>' +
                        '<p style="font-weight:600;margin:2px 0">' + escape(m.title) + '</p>' +
                        '<p style="font-size:12px;color:#475569">' + escape(m.category) + ' · Kec. ' + escape(m.district) + '</p>' +
                        '<p style="font-size:12px;margin-top:4px"><strong style="color:' + m.color + '">' + escape(m.status) + '</strong> · <a href="' + escape(m.url) + '">Lacak →</a></p>'
                    );
            });
        })();
    </script>
@endpush
