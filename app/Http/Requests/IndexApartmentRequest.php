<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexApartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'governorate_id' => ['nullable', 'integer', 'exists:governorates,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'sort_by' => ['nullable', 'in:price,created_at'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'city_id' => $this->filled('city_id') ? (int) $this->input('city_id') : null,
            'governorate_id' => $this->filled('governorate_id') ? (int) $this->input('governorate_id') : null,
            'per_page' => $this->filled('per_page') ? (int) $this->input('per_page') : null,
        ]);
    }
}
