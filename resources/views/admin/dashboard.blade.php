@extends('layouts.admin')

@section('title', 'Dashboard')

@php
    $panel = auth()->user()->role->panelPrefix();
    $isKabupaten = auth()->user()->isAdminKabupaten();
    $maxCategory = max(1, $perCategory->max('complaints_count'));
@endphp

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold">Dashboard {{ $isKabupaten ? 'Kabupaten' : 'Kecamatan '.auth()->user()->district?->name }}</h1>
        <p class="mt-1 text-sm text-ink-700">Ringkasan penanganan pengaduan per {{ now()->translatedFormat('d M Y, H.i') }} WIB.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @if ($isKabupaten)
            <x-kpi-card label="Total pengaduan" :value="number_format($kpi['total'], 0, ',', '.')" :href="route('kabupaten.pengaduan.index')" />
            <x-kpi-card label="% selesai" :value="$kpi['total'] > 0 ? round($kpi['selesai'] / $kpi['total'] * 100).'%' : '—'" tone="green" />
            <x-kpi-card label="Eskalasi aktif" :value="$kpi['eskalasi']" tone="violet" :href="route('kabupaten.eskalasi')" />
        @else
            <x-kpi-card label="Pending" :value="$kpi['pending']" tone="amber" :href="route('kecamatan.pengaduan.index', ['status' => 'pending'])" />
            <x-kpi-card label="Diproses" :value="$kpi['diproses']" tone="blue" :href="route('kecamatan.pengaduan.index', ['status' => 'diproses'])" />
            <x-kpi-card label="Selesai bulan ini" :value="$kpi['selesai_bulan_ini']" tone="green" />
        @endif
        <x-kpi-card label="Melewati SLA" :value="$kpi['overdue']" tone="red" :href="route($panel.'.pengaduan.index', ['sla' => 'lewat'])" />
        <x-kpi-card label="Rata-rata waktu selesai" :value="$kpi['rata_hari'] !== null ? str_replace('.', ',', $kpi['rata_hari']).' hari' : '—'" />
        <x-kpi-card label="Rating rata-rata" :value="$kpi['rating'] !== null ? number_format($kpi['rating'], 1, ',', '.').' / 5' : '—'" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="panel">
            <h2 class="border-b border-line px-5 py-4 font-semibold">Segera jatuh tempo & terlambat</h2>
            @forelse ($dueSoon as $complaint)
                <a href="{{ route($panel.'.pengaduan.show', $complaint) }}" class="flex items-center justify-between gap-4 border-b border-line px-5 py-3 last:border-0 hover:bg-paper">
                    <span class="min-w-0">
                        <span class="block font-mono text-xs text-ink-700">{{ $complaint->ticket_code }}</span>
                        <span class="block truncate text-sm font-medium">{{ $complaint->title }}</span>
                    </span>
                    <x-sla-chip :complaint="$complaint" />
                </a>
            @empty
                <p class="px-5 py-8 text-center text-sm text-ink-700">Tidak ada pengaduan di antrian.</p>
            @endforelse
        </section>

        <section class="panel">
            <h2 class="border-b border-line px-5 py-4 font-semibold">Pengaduan per kategori</h2>
            <div class="space-y-3 px-5 py-4">
                @foreach ($perCategory as $category)
                    <div>
                        <div class="flex justify-between text-sm">
                            <span>{{ $category->name }}</span>
                            <span class="font-semibold">{{ $category->complaints_count }}</span>
                        </div>
                        <div class="mt-1 h-2 rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-primary-600" style="width: {{ $category->complaints_count / $maxCategory * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    @if ($isKabupaten)
        <section class="mt-6 overflow-hidden panel">
            <h2 class="border-b border-line px-5 py-4 font-semibold">Peringkat kecamatan</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-paper text-left text-xs font-semibold text-ink-700">
                        <tr>
                            <th class="px-5 py-3">#</th>
                            <th class="px-5 py-3">Kecamatan</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3 text-right">Selesai</th>
                            <th class="px-5 py-3 text-right">% Selesai</th>
                            <th class="px-5 py-3 text-right">Lewat SLA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($districtRanking as $district)
                            <tr class="hover:bg-paper">
                                <td class="px-5 py-3 text-ink-700">{{ $loop->iteration }}</td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('kabupaten.pengaduan.index', ['kecamatan' => $district->id]) }}" class="font-medium hover:text-primary-700">{{ $district->name }}</a>
                                </td>
                                <td class="px-5 py-3 text-right">{{ $district->total_count }}</td>
                                <td class="px-5 py-3 text-right">{{ $district->selesai_count }}</td>
                                <td class="px-5 py-3 text-right">{{ $district->selesai_percentage !== null ? $district->selesai_percentage.'%' : '—' }}</td>
                                <td class="px-5 py-3 text-right {{ $district->overdue_count > 0 ? 'font-semibold text-red-700' : '' }}">{{ $district->overdue_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
