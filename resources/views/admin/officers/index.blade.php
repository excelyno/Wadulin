@extends('layouts.admin')

@section('title', 'Petugas')

@section('content')
    <div class="mb-5">
        <h1 class="text-2xl font-extrabold">Akun Petugas</h1>
        <p class="mt-1 text-sm text-ink-700">Akun petugas tidak dihapus agar jejak audit tetap utuh; nonaktifkan bila sudah tidak bertugas.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-3 xl:col-span-2">
            @foreach ($officers as $officer)
                <details class="panel">
                    <summary class="flex cursor-pointer flex-wrap items-center justify-between gap-3 px-5 py-4">
                        <span>
                            <span class="block font-medium">{{ $officer->name }}</span>
                            <span class="block text-xs text-ink-700">{{ $officer->email }} · {{ $officer->role->label() }}{{ $officer->district ? ' · Kec. '.$officer->district->name : '' }}</span>
                        </span>
                        <span class="rounded border px-2 py-0.5 text-xs font-semibold {{ $officer->is_active ? 'border-green-300 bg-green-50 text-green-900' : 'border-slate-300 bg-slate-50 text-ink-700' }}">
                            {{ $officer->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </summary>
                    <form method="POST" action="{{ route('kabupaten.petugas.update', $officer) }}" class="grid gap-3 border-t border-line px-5 py-4 sm:grid-cols-2">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="o{{ $officer->id }}-name" class="field-label">Nama</label>
                            <input id="o{{ $officer->id }}-name" name="name" required value="{{ $officer->name }}" class="field-input mt-1 text-sm text-sm">
                        </div>
                        <div>
                            <label for="o{{ $officer->id }}-phone" class="field-label">Nomor HP</label>
                            <input id="o{{ $officer->id }}-phone" name="phone" required value="{{ $officer->phone }}" class="field-input mt-1 text-sm text-sm">
                        </div>
                        @if ($officer->isAdminKecamatan())
                            <div>
                                <label for="o{{ $officer->id }}-district" class="field-label">Kecamatan</label>
                                <select id="o{{ $officer->id }}-district" name="district_id" class="field-input mt-1 text-sm text-sm">
                                    @foreach ($districts as $district)
                                        <option value="{{ $district->id }}" @selected($officer->district_id === $district->id)>{{ $district->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div>
                            <label for="o{{ $officer->id }}-password" class="field-label">Reset password <span class="font-normal text-ink-700">(kosongkan bila tidak diubah)</span></label>
                            <input id="o{{ $officer->id }}-password" type="password" name="password" autocomplete="new-password" class="field-input mt-1 text-sm text-sm">
                        </div>
                        <label class="flex items-center gap-2 text-sm sm:col-span-2">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked($officer->is_active) class="h-4 w-4 rounded border-ink-900/40 text-primary-600">
                            Akun aktif
                        </label>
                        <div class="sm:col-span-2">
                            <button class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </details>
            @endforeach
        </div>

        <section class="h-fit panel p-5">
            <h2 class="font-semibold">Tambah petugas kecamatan</h2>
            <form method="POST" action="{{ route('kabupaten.petugas.store') }}" class="mt-3 grid gap-3">
                @csrf
                <div>
                    <label for="new-name" class="field-label">Nama</label>
                    <input id="new-name" name="name" required value="{{ old('name') }}" class="field-input mt-1 text-sm text-sm">
                </div>
                <div>
                    <label for="new-email" class="field-label">Email</label>
                    <input id="new-email" type="email" name="email" required value="{{ old('email') }}" class="field-input mt-1 text-sm text-sm">
                </div>
                <div>
                    <label for="new-phone" class="field-label">Nomor HP</label>
                    <input id="new-phone" name="phone" required value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" class="field-input mt-1 text-sm text-sm">
                </div>
                <div>
                    <label for="new-district" class="field-label">Kecamatan</label>
                    <select id="new-district" name="district_id" required class="field-input mt-1 text-sm text-sm">
                        <option value="">Pilih kecamatan…</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}" @selected((int) old('district_id') === $district->id)>{{ $district->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="new-password" class="field-label">Password awal</label>
                    <input id="new-password" type="password" name="password" required autocomplete="new-password" class="field-input mt-1 text-sm text-sm">
                    <p class="mt-1 text-xs text-ink-700">Minimal 8 karakter dengan huruf dan angka.</p>
                </div>
                <button class="btn btn-primary">Buat akun</button>
            </form>
        </section>
    </div>
@endsection
