@php $prefix = $announcement ? 'ann'.$announcement->id.'-' : 'new-'; @endphp

<div>
    <label for="{{ $prefix }}title" class="field-label">Judul</label>
    <input id="{{ $prefix }}title" name="title" required maxlength="200" value="{{ $announcement?->title }}"
           class="field-input mt-1 text-sm text-sm">
</div>
<div>
    <label for="{{ $prefix }}excerpt" class="field-label">Ringkasan <span class="font-normal text-ink-700">(opsional)</span></label>
    <input id="{{ $prefix }}excerpt" name="excerpt" maxlength="300" value="{{ $announcement?->excerpt }}"
           class="field-input mt-1 text-sm text-sm">
</div>
<div>
    <label for="{{ $prefix }}body" class="field-label">Isi</label>
    <textarea id="{{ $prefix }}body" name="body" required rows="6"
              class="field-input mt-1 text-sm text-sm">{{ $announcement?->body }}</textarea>
</div>
<div>
    <label for="{{ $prefix }}cover" class="field-label">Sampul <span class="font-normal text-ink-700">(opsional, maks. 2 MB)</span></label>
    <input id="{{ $prefix }}cover" type="file" name="cover" accept="image/jpeg,image/png,image/webp"
           class="mt-1 block w-full text-sm file:mr-3 file:rounded-md file:rounded-md file:border file:border-ink-900/30 file:bg-white file:px-3 file:py-1.5 file:text-sm file:font-semibold">
</div>
<label class="flex items-center gap-2 text-sm">
    <input type="hidden" name="is_published" value="0">
    <input type="checkbox" name="is_published" value="1" @checked($announcement?->is_published ?? true) class="h-4 w-4 rounded border-ink-900/40 text-primary-600">
    Terbitkan
</label>
