<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComplaintRequest extends FormRequest
{
    /**
     * Access is restricted to masyarakat by the route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'district_id' => ['required', 'integer', Rule::exists('districts', 'id')],
            'title' => ['required', 'string', 'min:10', 'max:150'],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'address' => ['nullable', 'string', 'max:500'],
            // Kotak batas kasar Kabupaten Jember.
            'latitude' => ['required', 'numeric', 'between:-8.60,-7.90'],
            'longitude' => ['required', 'numeric', 'between:113.20,114.10'],
            'is_anonymous' => ['boolean'],
            'qr_location_id' => ['nullable', 'integer', Rule::exists('qr_locations', 'id')->where('is_active', true)],
            'photos' => ['required', 'array', 'min:1', 'max:3'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Pilih kategori pengaduan.',
            'category_id.exists' => 'Pilih kategori pengaduan.',
            'district_id.required' => 'Pilih kecamatan lokasi kejadian.',
            'title.required' => 'Judul terlalu pendek.',
            'title.min' => 'Judul terlalu pendek.',
            'description.required' => 'Ceritakan masalahnya minimal 20 karakter.',
            'description.min' => 'Ceritakan masalahnya minimal 20 karakter.',
            'latitude.required' => 'Tentukan lokasi pada peta.',
            'latitude.between' => 'Lokasi harus berada di Kabupaten Jember.',
            'longitude.required' => 'Tentukan lokasi pada peta.',
            'longitude.between' => 'Lokasi harus berada di Kabupaten Jember.',
            'photos.required' => 'Unggah minimal 1 foto.',
            'photos.max' => 'Maksimal 3 foto.',
            'photos.*.image' => 'File harus berupa gambar.',
            'photos.*.mimes' => 'Foto harus berformat jpg, png, atau webp.',
            'photos.*.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}
