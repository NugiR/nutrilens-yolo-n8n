<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', new Enum(Gender::class)],
            'height_cm' => ['nullable', 'integer', 'min:50', 'max:300'],
            'weight_kg' => ['nullable', 'integer', 'min:10', 'max:500'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
