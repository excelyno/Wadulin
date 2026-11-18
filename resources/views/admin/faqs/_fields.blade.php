@php $prefix = $faq ? 'faq'.$faq->id.'-' : 'new-'; @endphp

<div>
    <label for="{{ $prefix }}question" class="field-label">Pertanyaan</label>
    <input id="{{ $prefix }}question" name="question" required maxlength="255" value="{{ $faq?->question }}"
           class="field-input mt-1 text-sm text-sm">
</div>
<div>
    <label for="{{ $prefix }}answer" class="field-label">Jawaban</label>
    <textarea id="{{ $prefix }}answer" name="answer" required rows="4" maxlength="5000"
              class="field-input mt-1 text-sm text-sm">{{ $faq?->answer }}</textarea>
</div>
<div>
    <label for="{{ $prefix }}sort_order" class="field-label">Urutan</label>
    <input id="{{ $prefix }}sort_order" type="number" name="sort_order" min="0" max="999" value="{{ $faq?->sort_order ?? 0 }}"
           class="field-input mt-1 w-28 text-sm text-sm">
</div>
<label class="flex items-center gap-2 text-sm">
    <input type="hidden" name="is_published" value="0">
    <input type="checkbox" name="is_published" value="1" @checked($faq?->is_published ?? true) class="h-4 w-4 rounded border-ink-900/40 text-primary-600">
    Tampilkan di halaman FAQ
</label>
