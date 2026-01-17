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
        return [];
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
