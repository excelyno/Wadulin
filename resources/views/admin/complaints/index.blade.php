@extends('layouts.admin')

@php
    $panel = auth()->user()->role->panelPrefix();
    $isKabupaten = auth()->user()->isAdminKabupaten();
    $tabs = ['' => 'Semua'] + collect(\App\Enums\ComplaintStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
    $hasFilter = collect($filters)->except('status')->filter()->isNotEmpty();
@endphp

@section('title', $isKabupaten ? 'Semua Pengaduan' : 'Antrian Pengaduan')

@section('content')
    <div class="mb-5">
        <h1 class="text-2xl font-extrabold">{{ $isKabupaten ? 'Semua Pengaduan' : 'Antrian Pengaduan' }}</h1>
    </div>

    <div class="mb-4 flex gap-1 overflow-x-auto border-b border-line">
        @foreach ($tabs as $value => $label)
            @php
                $count = $value === '' ? $statusCounts->sum() : ($statusCounts[$value] ?? 0);
                $isActive = ($filters['status'] ?? '') === $value;
            @endphp
            <a href="{{ route($panel.'.pengaduan.index', array_filter([...$filters, 'status' => $value])) }}"
               class="-mb-px whitespace-nowrap border-b-2 px-4 py-2.5 text-sm font-medium {{ $isActive ? 'border-primary-600 text-primary-700' : 'border-transparent text-ink-700 hover:text-slate-900' }}">
                {{ $label }} <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs text-ink-700">{{ $count }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <input type="hidden" name="status" value="{{ $filters['status'] }}">
        <div>
            <label for="q" class="block text-xs font-medium text-ink-700">Cari</label>
            <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Kode / judul"
                   class="field-input mt-1 w-auto py-2 text-sm">
        </div>
        <div>
            <label for="kategori" class="block text-xs font-medium text-ink-700">Kategori</label>
            <select id="kategori" name="kategori" class="field-input mt-1 w-auto py-2 pr-8 text-sm">
                <option value="">Semua</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($filters['kategori'] === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        @if ($isKabupaten)
            <div>
                <label for="kecamatan" class="block text-xs font-medium text-ink-700">Kecamatan</label>
                <select id="kecamatan" name="kecamatan" class="field-input mt-1 w-auto py-2 pr-8 text-sm">
                    <option value="">Semua</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district->id }}" @selected($filters['kecamatan'] === $district->id)>{{ $district->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div>
            <label for="sla" class="block text-xs font-medium text-ink-700">SLA</label>
            <select id="sla" name="sla" class="field-input mt-1 w-auto py-2 pr-8 text-sm">
                <option value="">Semua</option>
                <option value="24jam" @selected($filters['sla'] === '24jam')>≤ 24 jam</option>
                <option value="lewat" @selected($filters['sla'] === 'lewat')>Terlambat</option>
            </select>
        </div>
        <button class="btn btn-primary">Terapkan</button>
        @if ($hasFilter)
            <a href="{{ route($panel.'.pengaduan.index', ['status' => $filters['status']]) }}" class="px-2 py-2 text-sm font-medium text-ink-700 hover:text-slate-900">Reset filter</a>
        @endif
    </form>

    <div class="overflow-hidden panel">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-paper text-left text-xs font-semibold text-ink-700">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Judul</th>
                        <th class="px-4 py-3">Kategori</th>
                        @if ($isKabupaten)<th class="px-4 py-3">Kecamatan</th>@endif
                        <th class="px-4 py-3">Pelapor</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">SLA</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($complaints as $complaint)
                        <tr class="hover:bg-paper">
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs">
                                <a href="{{ route($panel.'.pengaduan.show', $complaint) }}" class="font-semibold text-primary-700 hover:underline">{{ $complaint->ticket_code }}</a>
                            </td>
                            <td class="max-w-xs truncate px-4 py-3">{{ $complaint->title }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $complaint->category->name }}</td>
                            @if ($isKabupaten)<td class="whitespace-nowrap px-4 py-3">{{ $complaint->district->name }}</td>@endif
                            <td class="whitespace-nowrap px-4 py-3">{{ $complaint->user->name }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-ink-700">{{ $complaint->created_at->translatedFormat('d M Y') }}</td>
                            <td class="whitespace-nowrap px-4 py-3"><x-sla-chip :complaint="$complaint" /></td>
                            <td class="whitespace-nowrap px-4 py-3"><x-status-badge :status="$complaint->status" :escalated="$complaint->isEscalated()" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-ink-700">
                                {{ $hasFilter ? 'Tidak ada pengaduan yang cocok dengan filter.' : 'Tidak ada pengaduan di antrian.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $complaints->links() }}</div>
@endsection
