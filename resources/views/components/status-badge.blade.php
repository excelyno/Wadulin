@props(['status', 'escalated' => false])

@php
    $classes = match ($status) {
        \App\Enums\ComplaintStatus::Pending => 'border-amber-300 bg-amber-50 text-amber-900',
        \App\Enums\ComplaintStatus::Diproses => 'border-blue-300 bg-blue-50 text-blue-900',
        \App\Enums\ComplaintStatus::Selesai => 'border-green-300 bg-green-50 text-green-900',
        \App\Enums\ComplaintStatus::Ditolak => 'border-red-300 bg-red-50 text-red-900',
        \App\Enums\ComplaintStatus::Dibatalkan => 'border-slate-300 bg-slate-50 text-slate-700',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex flex-wrap items-center gap-1']) }}>
    <span class="inline-flex items-center gap-1.5 rounded border px-2 py-0.5 text-xs font-semibold {{ $classes }}">
        <span class="h-2 w-2 rounded-full" style="background: {{ $status->hexColor() }}"></span>
        {{ $status->label() }}
    </span>
    @if ($escalated)
        <span class="inline-flex items-center rounded border border-violet-300 bg-violet-50 px-2 py-0.5 text-xs font-semibold text-violet-900">Tereskalasi</span>
    @endif
</span>
