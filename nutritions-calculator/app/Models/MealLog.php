<?php

namespace App\Models;

use App\Enums\MealLogStatus;
use App\Enums\MealType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'meal_type', 'date', 'photo_path', 'detected_food_name', 'detection_confidence', 'status'])]
class MealLog extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'meal_type' => MealType::class,
            'status' => MealLogStatus::class,
            'detection_confidence' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiResult(): HasOne
    {
        return $this->hasOne(MealAiResult::class);
    }
}
