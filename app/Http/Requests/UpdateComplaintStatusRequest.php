<?php

namespace App\Http\Requests;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateComplaintStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('updateStatus', $this->complaint()) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $allowedStatuses = array_map(
            fn (ComplaintStatus $status): string => $status->value,
            $this->complaint()->status->officerTransitions(),
        );

        return [
            'status' => ['required', Rule::in($allowedStatuses)],
            'note' => [
                Rule::requiredIf($this->input('status') === ComplaintStatus::Ditolak->value),
                'nullable',
                'string',
                'min:10',
                'max:2000',
            ],
            'is_public' => ['sometimes', 'boolean'],
            'photos' => ['nullable', 'array', 'max:3'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Pilih aksi status.',
            'status.in' => 'Transisi status tidak diizinkan dari status saat ini.',
            'note.required' => 'Alasan wajib diisi.',
            'note.min' => 'Alasan wajib diisi (minimal :min karakter).',
            'photos.max' => 'Maksimal 3 foto hasil.',
            'photos.*.image' => 'File harus berupa gambar.',
            'photos.*.mimes' => 'Foto harus berformat jpg, png, atau webp.',
            'photos.*.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }

    private function complaint(): Complaint
    {
        return $this->route('complaint');
    }
}
