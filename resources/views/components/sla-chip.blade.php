@props(['complaint'])

@php
    $isOpen = ! $complaint->status->isFinal();
    $dueAt = $complaint->due_at;
@endphp

@if (! $isOpen)
    <span class="text-xs text-slate-400">—</span>
@elseif ($dueAt->isPast())
    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
        Terlambat {{ max(1, (int) $dueAt->diffInDays(now())) }} hari
    </span>
@elseif ($dueAt->lte(now()->addDay()))
    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">
        ≤ 24 jam
    </span>
@else
    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
        Aman · {{ (int) now()->diffInDays($dueAt) }} hari
    </span>
@endif
