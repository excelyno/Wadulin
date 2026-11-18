<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQrLocationRequest extends FormRequest
{
    /**
     * Access is restricted to officers by the route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * District officers always create points in their own district, so only
     * the regency picks one.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            // Kotak batas kasar Kabupaten Jember.
            'latitude' => ['required', 'numeric', 'between:-8.60,-7.90'],
            'longitude' => ['required', 'numeric', 'between:113.20,114.10'],
            'district_id' => [
                Rule::requiredIf($this->user()->isAdminKabupaten()),
                Rule::prohibitedIf(! $this->user()->isAdminKabupaten()),
                'nullable',
                'integer',
                Rule::exists('districts', 'id'),
            ],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama titik wajib diisi.',
            'name.min' => 'Nama titik minimal :min karakter.',
            'latitude.required' => 'Latitude wajib diisi.',
            'latitude.between' => 'Lokasi harus berada di Kabupaten Jember.',
            'longitude.required' => 'Longitude wajib diisi.',
            'longitude.between' => 'Lokasi harus berada di Kabupaten Jember.',
            'district_id.required' => 'Pilih kecamatan.',
        ];
    }
}
