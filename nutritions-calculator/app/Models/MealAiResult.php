<?php

namespace App\Models;

use App\Enums\CalorieStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'meal_log_id', 'food_name', 'calories', 'protein_g',
    'carbs_g', 'fat_g', 'fiber_g', 'vitamins_json',
    'calorie_status', 'summary', 'raw_response',
])]
class MealAiResult extends Model
{
    protected function casts(): array
    {
        return [
            'calories' => 'float',
            'protein_g' => 'float',
            'carbs_g' => 'float',
            'fat_g' => 'float',
            'fiber_g' => 'float',
            'vitamins_json' => 'array',
            'raw_response' => 'array',
            'calorie_status' => CalorieStatus::class,
        ];
    }

    public function mealLog(): BelongsTo
    {
        return $this->belongsTo(MealLog::class);
    }
}
