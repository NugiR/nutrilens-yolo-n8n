<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMealAiResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'food_name' => ['required', 'string'],
            'calories'  => ['required', 'numeric', 'min:0'],
            'protein'   => ['nullable', 'numeric', 'min:0'],
            'carbs'     => ['nullable', 'numeric', 'min:0'],
            'fat'       => ['nullable', 'numeric', 'min:0'],
            'fiber'     => ['nullable', 'numeric', 'min:0'],
            'vitamins'  => ['nullable', 'array'],
            'summary'   => ['nullable', 'string'],
        ];
    }
}
