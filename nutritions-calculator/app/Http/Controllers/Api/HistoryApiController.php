<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\MealLogRepository;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HistoryApiController extends Controller
{
    use ApiResponsable;

    public function __construct(private MealLogRepository $mealLogRepo) {}

    public function index(Request $request): JsonResponse
    {
        $limit = $request->query('limit') ? (int) $request->query('limit') : null;
        $page  = (int) $request->query('page', 1);

        $logs = $this->mealLogRepo->forUserFiltered(
            userId: $request->user()->id,
            date: $request->query('date'),
            dateFrom: $request->query('date_from'),
            dateTo: $request->query('date_to'),
            limit: $limit,
            page: $page,
        );

        $items = collect($limit ? $logs->items() : $logs)->map(fn ($log) => $this->formatLog($log));

        if ($limit) {
            return $this->success($logs->setCollection($items));
        }

        return $this->success($items);
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
