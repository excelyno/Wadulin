@extends('layouts.app')

@section('title', 'Buat Pengaduan · WADULIN')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
    <div class="container-page py-10">
        <div class="max-w-3xl">
            <h1 class="text-3xl font-extrabold">Buat pengaduan</h1>
            <p class="mt-2 text-ink-700">Isi tiga bagian di bawah. Butuh sekitar dua menit. Setelah terkirim kamu mendapat kode tiket.</p>

            @if ($errors->any())
                <div class="alert-error mt-6" role="alert">
                    <p class="font-semibold">Ada {{ $errors->count() }} isian yang perlu diperbaiki:</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($qrLocation)
                <p class="alert-success mt-6">Lokasi terisi dari QR <strong>{{ $qrLocation->name }}</strong> (Kec. {{ $qrLocation->district->name }}). Geser penanda bila titiknya kurang tepat.</p>
            @elseif (request()->filled('qr'))
                <p class="alert-info mt-6">Kode QR tidak dikenali atau sudah tidak aktif. Pilih lokasi secara manual.</p>
            @endif

            <form method="POST" action="{{ route('pengaduan.store') }}" enctype="multipart/form-data" class="mt-8 space-y-10">
                @csrf
                @if ($qrLocation)
                    <input type="hidden" name="qr_location_id" value="{{ $qrLocation->id }}">
                @endif

                <fieldset>
                    <legend class="text-xl font-bold">1. Jenis masalah</legend>
                    <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($categories as $category)
                            <label class="cursor-pointer">
                                <input type="radio" name="category_id" value="{{ $category->id }}" class="peer sr-only" @checked((int) old('category_id') === $category->id) required>
                                <span class="block h-full rounded-md border border-ink-900/30 bg-white px-3 py-2.5 peer-checked:border-2 peer-checked:border-primary-600 peer-checked:bg-primary-50 peer-focus-visible:outline-3 peer-focus-visible:outline-accent-500">
                                    <span class="block text-sm font-semibold">{{ $category->name }}</span>
                                    @if ($category->description)
                                        <span class="block text-xs text-ink-700">{{ $category->description }}</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('category_id')<p class="field-error">{{ $message }}</p>@enderror
                </fieldset>

                <fieldset>
                    <legend class="text-xl font-bold">2. Lokasi</legend>
                    <p class="mt-1 text-sm text-ink-700">Klik peta atau pakai lokasimu sekarang. Penanda bisa digeser.</p>
                    <div id="map" @class(['mt-3 h-80 overflow-hidden rounded-md border', 'border-2 border-red-700' => $errors->has('latitude'), 'border-line' => ! $errors->has('latitude')])></div>
                    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
                        <p id="coord-readout" class="text-sm text-ink-700" aria-live="polite">Lokasi belum dipilih.</p>
                        <button type="button" id="locate-btn" class="btn btn-secondary py-2">Pakai lokasi saya</button>
                    </div>
                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $qrLocation?->latitude) }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $qrLocation?->longitude) }}">
                    @error('latitude')<p class="field-error">{{ $message }}</p>@enderror

                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="district_id" class="field-label">Kecamatan</label>
                            <select id="district_id" name="district_id" required @class(['field-input', 'field-input-invalid' => $errors->has('district_id')])>
                                <option value="">Pilih kecamatan</option>
                                @foreach ($districts as $district)
                                    <option value="{{ $district->id }}" @selected((int) old('district_id', $qrLocation?->district_id) === $district->id)>{{ $district->name }}</option>
                                @endforeach
                            </select>
                            @error('district_id')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="address" class="field-label">Alamat atau patokan <span class="font-normal text-ink-700">(opsional)</span></label>
                            <input id="address" name="address" value="{{ old('address', $qrLocation?->address) }}" maxlength="500" placeholder="Depan SDN 1, dekat pertigaan" class="field-input">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="space-y-5">
                    <legend class="text-xl font-bold">3. Detail</legend>
                    <div>
                        <label for="title" class="field-label">Judul
                            <span class="field-hint">Singkat dan jelas, minimal 10 karakter.</span>
                        </label>
                        <input id="title" name="title" value="{{ old('title') }}" required minlength="10" maxlength="150" placeholder="Jalan berlubang di depan Pasar Tanjung"
                               @class(['field-input', 'field-input-invalid' => $errors->has('title')])>
                        @error('title')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="description" class="field-label">Kronologi
                            <span class="field-hint">Apa masalahnya, sejak kapan, dan dampaknya. Minimal 20 karakter.</span>
                        </label>
                        <textarea id="description" name="description" rows="5" required minlength="20" maxlength="2000"
                                  @class(['field-input', 'field-input-invalid' => $errors->has('description')])>{{ old('description') }}</textarea>
                        @error('description')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="photos" class="field-label">Foto bukti
                            <span class="field-hint">1–3 foto, format jpg, png, atau webp, maksimal 2 MB per foto.</span>
                        </label>
                        <input id="photos" type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required
                               class="mt-1.5 block w-full text-sm file:mr-3 file:rounded-md file:border file:border-ink-900/30 file:bg-white file:px-4 file:py-2 file:text-sm file:font-semibold hover:file:bg-paper">
                        @error('photos')<p class="field-error">{{ $message }}</p>@enderror
                        @error('photos.*')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-start gap-3 text-sm">
                        <input type="hidden" name="is_anonymous" value="0">
                        <input type="checkbox" name="is_anonymous" value="1" @checked(old('is_anonymous') === '1') class="mt-0.5 h-5 w-5 rounded border-ink-900/40 text-primary-600">
                        <span><span class="font-semibold">Sembunyikan nama saya dari publik</span><span class="block text-ink-700">Petugas tetap bisa melihat kontakmu untuk klarifikasi.</span></span>
                    </label>
                </fieldset>

                <div class="flex flex-wrap items-center gap-4 border-t border-line pt-6">
                    <button class="btn btn-primary px-6 py-3 text-base">Kirim pengaduan</button>
                    <a href="{{ route('dashboard') }}" class="link text-sm">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            const jember = [-8.1724, 113.7008];
            const map = L.map('map').setView(jember, 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap',
            }).addTo(map);

            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const readout = document.getElementById('coord-readout');
            let marker = null;

            function setPoint(lat, lng) {
                latInput.value = lat.toFixed(7);
                lngInput.value = lng.toFixed(7);
                readout.textContent = 'Lokasi: ' + lat.toFixed(5) + ', ' + lng.toFixed(5);
                if (marker) {
                    marker.setLatLng([lat, lng]);
                    return;
                }
                marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                marker.on('dragend', function (e) {
                    const ll = e.target.getLatLng();
                    setPoint(ll.lat, ll.lng);
                });
            }

            map.on('click', (e) => setPoint(e.latlng.lat, e.latlng.lng));

            document.getElementById('locate-btn').addEventListener('click', function () {
                if (!navigator.geolocation) {
                    readout.textContent = 'Browser tidak mendukung lokasi. Pilih manual di peta.';
                    return;
                }
                navigator.geolocation.getCurrentPosition(function (pos) {
                    setPoint(pos.coords.latitude, pos.coords.longitude);
                    map.setView([pos.coords.latitude, pos.coords.longitude], 16);
                }, function () {
                    readout.textContent = 'Izin lokasi ditolak. Pilih manual di peta.';
                }, { enableHighAccuracy: true, timeout: 10000 });
            });

            if (latInput.value && lngInput.value) {
                setPoint(parseFloat(latInput.value), parseFloat(lngInput.value));
                map.setView([parseFloat(latInput.value), parseFloat(lngInput.value)], 15);
            }
        })();
    </script>
@endpush
