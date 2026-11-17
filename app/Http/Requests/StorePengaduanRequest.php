<?php

namespace App\Http\Requests;

use App\Enums\KategoriPengaduan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePengaduanRequest extends FormRequest
{
    /**
     * Pengaduan submission is intentionally public.
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
            'nama_pelapor' => ['required', 'string', 'max:100'],
            'kontak_pelapor' => ['nullable', 'string', 'max:100'],
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['required', 'string', 'max:2000'],
            'kategori' => ['required', Rule::enum(KategoriPengaduan::class)],
            'alamat' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, \Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $hasLat = filled($this->input('latitude'));
                $hasLng = filled($this->input('longitude'));

                if (! $hasLat || ! $hasLng) {
                    $validator->errors()->add('lokasi', 'Lokasi pengaduan wajib ditentukan pada peta.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_pelapor.required' => 'Nama pelapor wajib diisi.',
            'nama_pelapor.max' => 'Nama pelapor maksimal :max karakter.',
            'judul.required' => 'Judul pengaduan wajib diisi.',
            'judul.max' => 'Judul pengaduan maksimal :max karakter.',
            'deskripsi.required' => 'Deskripsi pengaduan wajib diisi.',
            'deskripsi.max' => 'Deskripsi pengaduan maksimal :max karakter.',
            'kategori.required' => 'Pilih kategori pengaduan.',
            'alamat.required' => 'Alamat lokasi wajib diisi.',
            'alamat.max' => 'Alamat maksimal :max karakter.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat jpg, jpeg, png, atau webp.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}
