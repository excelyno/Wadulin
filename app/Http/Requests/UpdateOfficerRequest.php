<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateOfficerRequest extends FormRequest
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
        /** @var User $officer */
        $officer = $this->route('officer');

        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^(08|\+628)[0-9]{8,12}$/'],
            'district_id' => [
                Rule::requiredIf($officer->role === UserRole::AdminKecamatan),
                Rule::prohibitedIf($officer->role === UserRole::AdminKabupaten),
                'nullable',
                'integer',
                Rule::exists('districts', 'id'),
            ],
            'is_active' => [
                'boolean',
                // Prevent an admin from locking themselves out.
                Rule::prohibitedIf($officer->is($this->user()) && ! $this->boolean('is_active')),
            ],
            'password' => ['nullable', 'string', Password::min(8)->letters()->numbers()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Nomor HP tidak valid.',
            'district_id.required' => 'Pilih kecamatan penugasan.',
            'is_active.prohibited' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.',
        ];
    }
}
