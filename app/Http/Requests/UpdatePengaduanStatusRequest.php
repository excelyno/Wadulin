<?php

namespace App\Http\Requests;

use App\Enums\StatusPengaduan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePengaduanStatusRequest extends FormRequest
{
    /**
     * Only authenticated staff may change a complaint's status. The route is
     * also guarded by the `auth` middleware, so this is a defense-in-depth check.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(StatusPengaduan::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Pilih status pengaduan.',
        ];
    }
}
