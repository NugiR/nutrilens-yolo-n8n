<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMealAiResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'food_name' => ['sometimes', 'string'],
            'calories'  => ['sometimes', 'numeric', 'min:0'],
            'protein'   => ['sometimes', 'numeric', 'min:0'],
            'carbs'     => ['sometimes', 'numeric', 'min:0'],
            'fat'       => ['sometimes', 'numeric', 'min:0'],
            'fiber'     => ['sometimes', 'numeric', 'min:0'],
            'vitamins'  => ['sometimes', 'array'],
            'summary'   => ['sometimes', 'string'],
        ];
    }
}
