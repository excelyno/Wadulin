<a href="{{ route('pengumuman.show', $announcement) }}" class="panel group block overflow-hidden hover:border-primary-600">
    @if ($announcement->cover_path)
        <img src="{{ Storage::disk('public')->url($announcement->cover_path) }}" alt="" class="aspect-[16/9] w-full border-b border-line object-cover">
    @endif
    <div class="p-4">
        <p class="text-xs text-ink-700">{{ $announcement->published_at->translatedFormat('d F Y') }}</p>
        <h3 class="mt-1 font-bold group-hover:underline">{{ $announcement->title }}</h3>
        @if ($announcement->excerpt)
            <p class="mt-2 line-clamp-3 text-sm text-ink-700">{{ $announcement->excerpt }}</p>
        @endif
    </div>
</a>
