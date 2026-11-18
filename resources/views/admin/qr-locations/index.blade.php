@extends('layouts.admin')

@section('title', 'Titik QR')

@php $panel = auth()->user()->role->panelPrefix(); @endphp

@section('content')
    <div class="mb-5">
        <h1 class="text-2xl font-extrabold">Titik QR Pengaduan</h1>
        <p class="mt-1 text-sm text-ink-700">Cetak QR dan tempel di fasilitas umum. Warga yang memindai langsung masuk ke form pengaduan dengan lokasi terisi.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-3 xl:col-span-2">
            @forelse ($qrLocations as $qrLocation)
                @php $scanUrl = route('qr.scan', $qrLocation); @endphp
                <details class="panel">
                    <summary class="flex cursor-pointer flex-wrap items-center justify-between gap-3 px-5 py-4">
                        <span>
                            <span class="block font-medium">{{ $qrLocation->name }}</span>
                            <span class="block text-xs text-ink-700"><span class="font-mono">{{ $qrLocation->code }}</span> · Kec. {{ $qrLocation->district->name }} · {{ $qrLocation->complaints_count }} pengaduan</span>
                        </span>
                        <span class="rounded border px-2 py-0.5 text-xs font-semibold {{ $qrLocation->is_active ? 'border-green-300 bg-green-50 text-green-900' : 'border-slate-300 bg-slate-50 text-ink-700' }}">
                            {{ $qrLocation->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </summary>
                    <div class="grid gap-5 border-t border-line px-5 py-4 md:grid-cols-[180px_1fr]">
                        <div class="text-center">
                            <div class="qr-code mx-auto w-fit rounded-md bg-white p-2 border border-line" data-url="{{ $scanUrl }}"></div>
                            <p class="mt-2 break-all font-mono text-[11px] text-ink-700">{{ $scanUrl }}</p>
                            <button type="button" class="qr-download mt-2 text-sm font-semibold text-primary-700 hover:underline" data-name="QR-{{ $qrLocation->code }}">Unduh PNG</button>
                        </div>
                        <form method="POST" action="{{ route($panel.'.qr.update', $qrLocation) }}" class="grid gap-3 sm:grid-cols-2">
                            @csrf
                            @method('PUT')
                            @include('admin.qr-locations._fields', ['qrLocation' => $qrLocation])
                            <div class="sm:col-span-2">
                                <button class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </details>
            @empty
                <p class="panel p-10 text-center text-sm text-ink-700">Belum ada titik QR.</p>
            @endforelse
        </div>

        <section class="h-fit panel p-5">
            <h2 class="font-semibold">Tambah titik QR</h2>
            <form method="POST" action="{{ route($panel.'.qr.store') }}" class="mt-3 grid gap-3 sm:grid-cols-2">
                @csrf
                @include('admin.qr-locations._fields', ['qrLocation' => null])
                <button class="btn btn-primary sm:col-span-2">Buat titik QR</button>
            </form>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.querySelectorAll('.qr-code').forEach(function (el) {
            new QRCode(el, { text: el.dataset.url, width: 160, height: 160, correctLevel: QRCode.CorrectLevel.M });
        });
        document.querySelectorAll('.qr-download').forEach(function (button) {
            button.addEventListener('click', function () {
                const canvas = button.parentElement.querySelector('canvas');
                if (!canvas) {
                    return;
                }
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = button.dataset.name + '.png';
                link.click();
            });
        });
    </script>
@endpush
