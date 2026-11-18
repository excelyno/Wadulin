@php $prefix = $qrLocation ? 'qr'.$qrLocation->id.'-' : 'new-'; @endphp

<div class="sm:col-span-2">
    <label for="{{ $prefix }}name" class="field-label">Nama titik</label>
    <input id="{{ $prefix }}name" name="name" required maxlength="150" value="{{ $qrLocation?->name }}" placeholder="Taman Alun-alun Jember"
           class="field-input mt-1 text-sm text-sm">
</div>
<div class="sm:col-span-2">
    <label for="{{ $prefix }}address" class="field-label">Alamat <span class="font-normal text-ink-700">(opsional)</span></label>
    <input id="{{ $prefix }}address" name="address" maxlength="500" value="{{ $qrLocation?->address }}"
           class="field-input mt-1 text-sm text-sm">
</div>
<div>
    <label for="{{ $prefix }}latitude" class="field-label">Latitude</label>
    <input id="{{ $prefix }}latitude" name="latitude" required inputmode="decimal" value="{{ $qrLocation?->latitude }}" placeholder="-8.1724"
           class="field-input mt-1 text-sm font-mono text-sm">
</div>
<div>
    <label for="{{ $prefix }}longitude" class="field-label">Longitude</label>
    <input id="{{ $prefix }}longitude" name="longitude" required inputmode="decimal" value="{{ $qrLocation?->longitude }}" placeholder="113.7008"
           class="field-input mt-1 text-sm font-mono text-sm">
</div>
@if (auth()->user()->isAdminKabupaten())
    <div class="sm:col-span-2">
        <label for="{{ $prefix }}district" class="field-label">Kecamatan</label>
        <select id="{{ $prefix }}district" name="district_id" required class="field-input mt-1 text-sm text-sm">
            <option value="">Pilih kecamatan…</option>
            @foreach ($districts as $district)
                <option value="{{ $district->id }}" @selected($qrLocation?->district_id === $district->id)>{{ $district->name }}</option>
            @endforeach
        </select>
    </div>
@endif
<label class="flex items-center gap-2 text-sm sm:col-span-2">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" @checked($qrLocation?->is_active ?? true) class="h-4 w-4 rounded border-ink-900/40 text-primary-600">
    Aktif
</label>
