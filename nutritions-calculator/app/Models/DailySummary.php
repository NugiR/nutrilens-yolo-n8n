<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'date', 'total_calories', 'total_protein_g',
    'total_carbs_g', 'total_fat_g', 'total_fiber_g', 'meal_count',
])]
class DailySummary extends Model
{
    protected function casts(): array
    {
        return [
            'date'            => 'date',
            'total_calories'  => 'float',
            'total_protein_g' => 'float',
            'total_carbs_g'   => 'float',
            'total_fat_g'     => 'float',
            'total_fiber_g'   => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
