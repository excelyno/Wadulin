<?php

namespace App\Http\Requests;

use App\Models\Complaint;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreComplaintRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('rate', $this->complaint()) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Pilih penilaian 1 sampai 5 bintang.',
            'rating.between' => 'Pilih penilaian 1 sampai 5 bintang.',
            'feedback.max' => 'Ulasan maksimal :max karakter.',
        ];
    }

    private function complaint(): Complaint
    {
        return $this->route('complaint');
    }
}
