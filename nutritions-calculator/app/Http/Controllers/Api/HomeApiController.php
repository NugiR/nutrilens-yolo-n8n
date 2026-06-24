<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailySummary;
use App\Repositories\MealLogRepository;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeApiController extends Controller
{
    use ApiResponsable;

    public function __construct(private MealLogRepository $mealLogRepo) {}

    public function index(Request $request): JsonResponse
    {
        $user   = $request->user();
        $limit  = $request->query('limit') ? (int) $request->query('limit') : null;
        $page   = (int) $request->query('page', 1);

        $todayLogs    = $this->mealLogRepo->todayForUser($user->id, $limit, $page);
        $dailySummary = DailySummary::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        $items = collect($limit ? $todayLogs->items() : $todayLogs)->map(fn ($log) => $this->formatLog($log));

        return $this->success([
            'today_logs'    => $items,
            'has_insight'   => $items->isNotEmpty(),
            'daily_summary' => $dailySummary ? [
                'total_calories'  => $dailySummary->total_calories,
                'total_protein_g' => $dailySummary->total_protein_g,
                'total_carbs_g'   => $dailySummary->total_carbs_g,
                'total_fat_g'     => $dailySummary->total_fat_g,
                'total_fiber_g'   => $dailySummary->total_fiber_g,
                'meal_count'      => $dailySummary->meal_count,
            ] : null,
        ]);
    }

    private function formatLog($log): array
    {
        $item = [
            'id'         => $log->id,
            'meal_type'  => $log->meal_type->value,
            'date'       => $log->date->toDateString(),
            'status'     => $log->status->value,
            'photo_path' => $log->photo_path ?: null,
            'detected_food_name' => $log->detected_food_name,
            'detection_confidence' => $log->detection_confidence !== null
                ? (float) $log->detection_confidence
                : null,
            'ai_result'  => null,
        ];

        if ($log->aiResult) {
            $item['ai_result'] = [
                'food_name'      => $log->aiResult->food_name,
                'calories'       => $log->aiResult->calories,
                'protein_g'      => $log->aiResult->protein_g,
                'carbs_g'        => $log->aiResult->carbs_g,
                'fat_g'          => $log->aiResult->fat_g,
                'fiber_g'        => $log->aiResult->fiber_g,
                'vitamins_json'  => $log->aiResult->vitamins_json,
                'calorie_status' => $log->aiResult->calorie_status->value,
                'summary'        => $log->aiResult->summary,
            ];
        }

        return $item;
    }
}
