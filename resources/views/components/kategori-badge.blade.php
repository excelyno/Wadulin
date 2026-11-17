@props(['kategori'])

@php
    $classes = match ($kategori->value) {
        'infrastruktur' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'kebersihan' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'keamanan' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'kesehatan' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'pelayanan' => 'bg-violet-50 text-violet-700 ring-violet-600/20',
        'lainnya' => 'bg-slate-100 text-slate-700 ring-slate-600/20',
        default => 'bg-slate-100 text-slate-700 ring-slate-600/20',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset $classes"]) }}>
    {{ $kategori->label() }}
</span>
