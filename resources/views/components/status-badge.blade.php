@props(['status'])

@php
    $classes = match ($status->value) {
        'pending' => 'bg-amber-100 text-amber-800 ring-amber-600/10',
        'proses' => 'bg-sky-100 text-sky-800 ring-sky-600/10',
        'selesai' => 'bg-emerald-100 text-emerald-800 ring-emerald-600/10',
        default => 'bg-slate-100 text-slate-700 ring-slate-600/10',
    };
    $dot = match ($status->value) {
        'pending' => 'bg-amber-500',
        'proses' => 'bg-sky-500',
        'selesai' => 'bg-emerald-500',
        default => 'bg-slate-400',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset $classes"]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>
    {{ $status->label() }}
</span>
