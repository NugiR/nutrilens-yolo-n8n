<?php

namespace App\Services;

use App\Enums\CalorieStatus;
use App\Models\DailySummary;
use App\Models\MealAiResult;
use App\Models\MealLog;

class NutritionService
{
    public function calculateBmi(?float $weightKg, ?int $heightCm): ?float
    {
        if (! $weightKg || ! $heightCm || $heightCm <= 0) {
            return null;
        }

        $heightM = $heightCm / 100;

        return round($weightKg / ($heightM ** 2), 2);
    }

    public function classifyCalories(float $calories): CalorieStatus
    {
        return match (true) {
            $calories < 400 => CalorieStatus::Kurang,
            $calories <= 800 => CalorieStatus::Cukup,
            default => CalorieStatus::Kelebihan,
        };
    }

    /**
     * Aggregate daily nutrient totals from meal logs (with aiResult eager-loaded).
     * Returns array keyed by day-of-month: ['1' => [protein, carbs, fat, fiber, vitamins], ...]
     */
    public function aggregateForChart(iterable $logs): array
    {
        $result = [];

        foreach ($logs as $log) {
            $ai = $log->aiResult;

            if (! $ai) {
                continue;
            }

            $day = (string) $log->date->day;

            if (! isset($result[$day])) {
                $result[$day] = ['protein' => 0, 'carbs' => 0, 'fat' => 0, 'fiber' => 0, 'vitamins' => 0];
            }

            $result[$day]['protein'] += (float) $ai->protein_g;
            $result[$day]['carbs'] += (float) $ai->carbs_g;
            $result[$day]['fat'] += (float) $ai->fat_g;
            $result[$day]['fiber'] += (float) $ai->fiber_g;
            $result[$day]['vitamins'] += $this->sumVitamins($ai->vitamins_json);
        }

        return $result;
    }

    public function recordAiResult(MealLog $log, array $data, array $rawPayload): void
    {
        MealAiResult::updateOrCreate(
            ['meal_log_id' => $log->id],
            [
                'food_name' => $data['food_name'],
                'calories' => $data['calories'],
                'protein_g' => $data['protein'] ?? 0,
                'carbs_g' => $data['carbs'] ?? 0,
                'fat_g' => $data['fat'] ?? 0,
                'fiber_g' => $data['fiber'] ?? 0,
                'vitamins_json' => $data['vitamins'] ?? [],
                'calorie_status' => $this->classifyCalories($data['calories']),
                'summary' => $data['summary'] ?? '',
                'raw_response' => $rawPayload,
            ]
        );

        $log->update(['status' => 'done']);

        $this->recalculateDailySummary($log->user_id, $log->date->toDateString());
    }

    public function recalculateDailySummary(int $userId, string $date): void
    {
        $results = MealAiResult::whereHas('mealLog', function ($q) use ($userId, $date) {
            $q->where('user_id', $userId)->whereDate('date', $date)->where('status', 'done');
        })->get();

        DailySummary::updateOrCreate(
            ['user_id' => $userId, 'date' => $date],
            [
                'total_calories'  => $results->sum('calories'),
                'total_protein_g' => $results->sum('protein_g'),
                'total_carbs_g'   => $results->sum('carbs_g'),
                'total_fat_g'     => $results->sum('fat_g'),
                'total_fiber_g'   => $results->sum('fiber_g'),
                'meal_count'      => $results->count(),
            ]
        );
    }

    private function sumVitamins(?array $vitamins): float
    {
        if (empty($vitamins)) {
            return 0;
        }

        return (float) array_sum(array_values($vitamins));
    }
}
