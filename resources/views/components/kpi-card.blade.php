@props(['label', 'value', 'href' => null, 'tone' => 'slate'])

@php
    $accent = match ($tone) {
        'amber' => 'text-amber-700',
        'blue' => 'text-blue-700',
        'green' => 'text-green-700',
        'red' => 'text-red-700',
        'violet' => 'text-violet-700',
        default => 'text-slate-900',
    };
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    class="panel block p-5 {{ $href ? 'hover:border-primary-600' : '' }}">
    <p class="text-sm text-ink-700">{{ $label }}</p>
    <p class="mt-2 text-3xl font-extrabold {{ $accent }}">{{ $value }}</p>
</{{ $tag }}>
