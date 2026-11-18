<a href="{{ route('pengaduan.show', $complaint) }}" class="flex items-start justify-between gap-4 px-4 py-3 hover:bg-paper">
    <span class="min-w-0">
        <span class="ticket block text-xs">{{ $complaint->ticket_code }}</span>
        <span class="block truncate font-semibold">{{ $complaint->title }}</span>
        <span class="block text-sm text-ink-700">{{ $complaint->category->name }} · Kec. {{ $complaint->district->name }} · {{ $complaint->created_at->diffForHumans() }}</span>
    </span>
    <x-status-badge :status="$complaint->status" class="shrink-0" />
</a>
