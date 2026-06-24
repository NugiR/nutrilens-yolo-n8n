<?php

namespace App\Http\Requests;

use App\Enums\MealType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreMealLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'             => ['nullable', 'integer', 'exists:users,id'],
            'meal_type'           => ['required', new Enum(MealType::class)],
            'detected_food_name'  => ['required', 'string', Rule::in(config('yolo.food_classes'))],
            'detection_confidence'=> ['required', 'numeric', 'min:0', 'max:1'],
            'date'                => ['nullable', 'date'],
            'photo'               => ['nullable', 'file', 'image', 'max:5120'],
        ];
    }
}
