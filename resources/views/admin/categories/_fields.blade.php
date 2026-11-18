@php $prefix = $category ? 'cat'.$category->id.'-' : 'new-'; @endphp

<div>
    <label for="{{ $prefix }}name" class="field-label">Nama</label>
    <input id="{{ $prefix }}name" name="name" required maxlength="100" value="{{ $category?->name }}"
           class="field-input mt-1 text-sm text-sm">
</div>
<div>
    <label for="{{ $prefix }}icon" class="field-label">Ikon (nama Lucide)</label>
    <input id="{{ $prefix }}icon" name="icon" maxlength="50" value="{{ $category?->icon }}" placeholder="construction"
           class="field-input mt-1 text-sm font-mono text-sm">
</div>
<div class="sm:col-span-2">
    <label for="{{ $prefix }}description" class="field-label">Deskripsi</label>
    <input id="{{ $prefix }}description" name="description" maxlength="255" value="{{ $category?->description }}"
           class="field-input mt-1 text-sm text-sm">
</div>
<label class="flex items-center gap-2 text-sm sm:col-span-2">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" @checked($category?->is_active ?? true) class="h-4 w-4 rounded border-ink-900/40 text-primary-600">
    Aktif
</label>
