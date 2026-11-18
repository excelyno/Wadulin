<?php

namespace App\Http\Requests;

use App\Models\Complaint;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComplaintDispositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('dispose', $this->complaint()) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'district_id' => [
                'required',
                'integer',
                Rule::exists('districts', 'id'),
                Rule::notIn([$this->complaint()->district_id]),
            ],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'district_id.required' => 'Pilih kecamatan tujuan.',
            'district_id.exists' => 'Kecamatan tujuan tidak ditemukan.',
            'district_id.not_in' => 'Kecamatan tujuan harus berbeda dari kecamatan saat ini.',
            'reason.required' => 'Alasan wajib diisi.',
            'reason.min' => 'Alasan wajib diisi (minimal :min karakter).',
        ];
    }

    private function complaint(): Complaint
    {
        return $this->route('complaint');
    }
}
