<?php

namespace App\Http\Requests;

use App\Models\Announcement;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    /**
     * Access is restricted to admin_kabupaten by the route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug((string) $this->input('title'))]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:200'],
            'slug' => ['required', Rule::unique(Announcement::class, 'slug')->ignore($this->route('announcement'))],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['required', 'string', 'min:20'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_published' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'title.min' => 'Judul minimal :min karakter.',
            'slug.required' => 'Judul wajib diisi.',
            'slug.unique' => 'Pengumuman dengan judul ini sudah ada.',
            'body.required' => 'Isi pengumuman wajib diisi.',
            'body.min' => 'Isi pengumuman minimal :min karakter.',
            'cover.image' => 'Sampul harus berupa gambar.',
            'cover.max' => 'Ukuran sampul maksimal 2 MB.',
        ];
    }
}
