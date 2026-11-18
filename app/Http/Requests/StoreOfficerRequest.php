<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreOfficerRequest extends FormRequest
{
    /**
     * Access is restricted to admin_kabupaten by the route middleware.
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
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'phone' => ['required', 'string', 'regex:/^(08|\+628)[0-9]{8,12}$/'],
            'district_id' => ['required', 'integer', Rule::exists('districts', 'id')],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi (minimal 3 karakter).',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.regex' => 'Nomor HP tidak valid.',
            'district_id.required' => 'Pilih kecamatan penugasan.',
        ];
    }
}
