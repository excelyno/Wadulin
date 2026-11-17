@extends('layouts.app')

@section('title', 'Ajukan Pengaduan · Wadulin')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
    <div class="mx-auto max-w-3xl px-5 py-14 sm:px-8">
        <div class="mb-8">
            <a href="{{ route('beranda') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                    <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.61l3.72 3.72a.75.75 0 1 1-1.06 1.06l-5-5a.75.75 0 0 1 0-1.06l5-5a.75.75 0 1 1 1.06 1.06L5.61 9.25h10.64A.75.75 0 0 1 17 10Z" clip-rule="evenodd"/>
                </svg>
                Kembali
            </a>
            <p class="text-xs font-bold tracking-[.18em] text-primary-700">BUAT PENGADUAN</p>
            <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Sampaikan aduanmu.</h1>
            <p class="mt-2 text-slate-600">Lengkapi data berikut. Anda tidak perlu membuat akun untuk mengirim pengaduan.</p>
        </div>

        <form method="POST" action="{{ route('pengaduan.store') }}" enctype="multipart/form-data" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-7 shadow-sm sm:p-9">
            @csrf

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="nama_pelapor" class="block text-sm font-medium text-slate-700">Nama Pelapor <span class="text-rose-600">*</span></label>
                    <input type="text" name="nama_pelapor" id="nama_pelapor" value="{{ old('nama_pelapor') }}" autocomplete="name"
                           class="mt-1.5 block w-full rounded-md border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset {{ $errors->has('nama_pelapor') ? 'ring-rose-500 focus:ring-rose-500' : 'ring-slate-300 focus:ring-2 focus:ring-primary-600' }}">
                    @error('nama_pelapor')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="kontak_pelapor" class="block text-sm font-medium text-slate-700">Kontak <span class="text-slate-400">(opsional)</span></label>
                    <input type="text" name="kontak_pelapor" id="kontak_pelapor" value="{{ old('kontak_pelapor') }}" placeholder="Nomor HP atau email"
                           class="mt-1.5 block w-full rounded-md border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset {{ $errors->has('kontak_pelapor') ? 'ring-rose-500 focus:ring-rose-500' : 'ring-slate-300 focus:ring-2 focus:ring-primary-600' }}">
                    @error('kontak_pelapor')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="judul" class="block text-sm font-medium text-slate-700">Judul Pengaduan <span class="text-rose-600">*</span></label>
                <input type="text" name="judul" id="judul" value="{{ old('judul') }}"
                        class="mt-1.5 block w-full rounded-md border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset {{ $errors->has('judul') ? 'ring-rose-500 focus:ring-rose-500' : 'ring-slate-300 focus:ring-2 focus:ring-primary-600' }}">
                @error('judul')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="kategori" class="block text-sm font-medium text-slate-700">Kategori <span class="text-rose-600">*</span></label>
                <select name="kategori" id="kategori"
                         class="mt-1.5 block w-full rounded-md border-0 bg-white px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset {{ $errors->has('kategori') ? 'ring-rose-500 focus:ring-rose-500' : 'ring-slate-300 focus:ring-2 focus:ring-primary-600' }}">
                    <option value="" {{ old('kategori') === '' ? 'selected' : '' }}>Pilih kategori…</option>
                    @foreach ($kategoriOptions as $opt)
                        <option value="{{ $opt->value }}" {{ old('kategori') === $opt->value ? 'selected' : '' }}>{{ $opt->label() }}</option>
                    @endforeach
                </select>
                @error('kategori')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="deskripsi" class="block text-sm font-medium text-slate-700">Deskripsi <span class="text-rose-600">*</span></label>
                <textarea name="deskripsi" id="deskripsi" rows="5"
                          class="mt-1.5 block w-full rounded-md border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset {{ $errors->has('deskripsi') ? 'ring-rose-500 focus:ring-rose-500' : 'ring-slate-300 focus:ring-2 focus:ring-primary-600' }}">{{ old('deskripsi') }}</textarea>
                @error('deskripsi')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="alamat" class="block text-sm font-medium text-slate-700">Alamat Lokasi <span class="text-rose-600">*</span></label>
                <input type="text" name="alamat" id="alamat" value="{{ old('alamat') }}" placeholder="Mis. Jl. Merdeka No. 10, Gambir, Jakarta"
                       class="mt-1.5 block w-full rounded-md border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset {{ $errors->has('alamat') ? 'ring-rose-500 focus:ring-rose-500' : 'ring-slate-300 focus:ring-2 focus:ring-primary-600' }}">
                @error('alamat')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Lokasi pada Peta <span class="text-rose-600">*</span></label>
                <p class="mt-1 text-sm text-slate-500">Klik pada peta atau gunakan tombol untuk menentukan titik pengaduan. Anda bisa menggeser penanda.</p>
                <div id="map" class="mt-2 h-80 w-full overflow-hidden rounded-lg ring-1 ring-slate-300"></div>
                <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
                    <p id="coord-readout" class="text-sm text-slate-500">Lokasi belum dipilih.</p>
                    <button type="button" id="locate-btn"
                            class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                            <path fill-rule="evenodd" d="M11.54 22.35c.19.13.38.21.6.21a1 1 0 0 0 .61-.21l.06-.05C16.41 19.45 21 15.06 21 10.5A9 9 0 1 0 3 10.5c0 4.56 4.59 8.95 8.54 11.8l-.01.05ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                        </svg>
                        Gunakan lokasi saya
                    </button>
                </div>
                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                @error('lokasi')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="foto" class="block text-sm font-medium text-slate-700">Foto Pendukung <span class="text-slate-400">(opsional)</span></label>
                <input type="file" name="foto" id="foto" accept="image/*"
                       class="mt-1.5 block w-full text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-primary-50 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-primary-700 hover:file:bg-primary-100">
                <p class="mt-1 text-xs text-slate-500">Format jpg, jpeg, png, atau webp. Maksimal 2 MB.</p>
                @error('foto')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-6">
                <a href="{{ route('beranda') }}" class="rounded-md px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100">Batal</a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-md bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                    Kirim Pengaduan
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            const map = L.map('map').setView([-6.2088, 106.8456], 12);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
                subdomains: 'abcd',
                maxZoom: 20,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>'
            }).addTo(map);

            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const readout = document.getElementById('coord-readout');
            let marker = null;

            function updateReadout(lat, lng) {
                readout.textContent = 'Lokasi: ' + lat.toFixed(5) + ', ' + lng.toFixed(5);
                readout.classList.remove('text-slate-500');
                readout.classList.add('text-emerald-700', 'font-medium');
            }

            function placeMarker(lat, lng, fly) {
                latInput.value = lat;
                lngInput.value = lng;
                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                    marker.on('dragend', function (e) {
                        const ll = e.target.getLatLng();
                        latInput.value = ll.lat;
                        lngInput.value = ll.lng;
                        updateReadout(ll.lat, ll.lng);
                    });
                }
                updateReadout(lat, lng);
                if (fly) {
                    map.setView([lat, lng], 15);
                }
            }

            map.on('click', function (e) {
                placeMarker(e.latlng.lat, e.latlng.lng, false);
            });

            document.getElementById('locate-btn').addEventListener('click', function () {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung geolokasi.');
                    return;
                }
                navigator.geolocation.getCurrentPosition(function (pos) {
                    placeMarker(pos.coords.latitude, pos.coords.longitude, true);
                }, function () {
                    alert('Tidak dapat mengambil lokasi Anda. Silakan pilih manual pada peta.');
                }, { enableHighAccuracy: true, timeout: 10000 });
            });

            const oldLat = latInput.value ? parseFloat(latInput.value) : null;
            const oldLng = lngInput.value ? parseFloat(lngInput.value) : null;
            if (oldLat && oldLng) {
                placeMarker(oldLat, oldLng, false);
                map.setView([oldLat, oldLng], 14);
            }
        })();
    </script>
@endpush
