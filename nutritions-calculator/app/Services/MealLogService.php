<?php

namespace App\Services;

use App\Enums\MealLogStatus;
use App\Models\MealLog;
use App\Models\User;

class MealLogService
{
    public function __construct(
        private WebhookService $webhookService,
    ) {}

    public function storeFromDetection(
        User $user,
        string $mealType,
        string $foodName,
        float $confidence,
        string $date,
    ): MealLog {
        $log = MealLog::create([
            'user_id' => $user->id,
            'meal_type' => $mealType,
            'date' => $date,
            'photo_path' => '',
            'detected_food_name' => $foodName,
            'detection_confidence' => $confidence,
            'status' => MealLogStatus::Pending,
        ]);

        $this->webhookService->sendToN8n([
            'meal_log_id' => $log->id,
            'food_name' => $foodName,
            'confidence' => $confidence,
            'meal_type' => $log->meal_type->value,
            'date' => $date,
            'user_id' => $user->id,
        ]);

        return $log;
    }

    public function delete(MealLog $log, int $requestingUserId): void
    {
        abort_unless($log->user_id === $requestingUserId, 403);

        $log->delete();
    }
}
