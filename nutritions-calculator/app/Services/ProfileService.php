<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;

class ProfileService
{
    public function __construct(private NutritionService $nutritionService) {}

    public function update(User $user, array $validated, ?UploadedFile $photo): void
    {
        if ($photo) {
            $validated['photo_path'] = $photo->store("profiles/{$user->id}", 'public');
        }

        unset($validated['photo']);

        $weightKg  = $validated['weight_kg'] ?? $user->weight_kg;
        $heightCm  = $validated['height_cm'] ?? $user->height_cm;

        $validated['bmi'] = $this->nutritionService->calculateBmi($weightKg, $heightCm);

        $user->update($validated);
    }
}
