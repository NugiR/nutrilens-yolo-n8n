<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMealLogRequest;
use App\Repositories\MealLogRepository;
use App\Services\MealLogService;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealLogApiController extends Controller
{
    use ApiResponsable;

    public function __construct(
        private MealLogService $mealLogService,
        private MealLogRepository $mealLogRepo,
    ) {}

    public function store(StoreMealLogRequest $request): JsonResponse
    {
        $log = $this->mealLogService->storeFromDetection(
            user: $request->user(),
            mealType: $request->input('meal_type'),
            foodName: $request->input('detected_food_name'),
            confidence: (float) $request->input('detection_confidence'),
            date: $request->input('date', today()->toDateString()),
            photo: $request->file('photo'),
        );

        return $this->created([
            'id' => $log->id,
            'meal_type' => $log->meal_type->value,
            'date' => $log->date->toDateString(),
            'detected_food_name' => $log->detected_food_name,
            'detection_confidence' => (float) $log->detection_confidence,
            'status' => $log->status->value,
            'photo_path' => $log->photo_path ?: null,
        ], 'Makanan terdeteksi. Menunggu analisis nutrisi...');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $log = $this->mealLogRepo->findOrFail($id);
        $this->mealLogService->delete($log, $request->user()->id);

        return $this->success(null, 'Data makanan berhasil dihapus.');
    }

    public function today(Request $request): JsonResponse
    {
        $limit = $request->query('limit') ? (int) $request->query('limit') : null;
        $page = (int) $request->query('page', 1);

        $logs = $this->mealLogRepo->todayForUser($request->user()->id, $limit, $page);

        $items = collect($limit ? $logs->items() : $logs)->map(fn ($log) => $this->formatLog($log));

        if ($limit) {
            return $this->success($logs->setCollection($items));
        }

        return $this->success($items);
    }

    private function formatLog($log): array
    {
        $item = [
            'id' => $log->id,
            'meal_type' => $log->meal_type->value,
            'date' => $log->date->toDateString(),
            'status' => $log->status->value,
            'photo_path' => $log->photo_path ?: null,
            'detected_food_name' => $log->detected_food_name,
            'detection_confidence' => $log->detection_confidence !== null
                ? (float) $log->detection_confidence
                : null,
            'ai_result' => null,
        ];

        if ($log->aiResult) {
            $item['ai_result'] = [
                'food_name' => $log->aiResult->food_name,
                'calories' => $log->aiResult->calories,
                'protein_g' => $log->aiResult->protein_g,
                'carbs_g' => $log->aiResult->carbs_g,
                'fat_g' => $log->aiResult->fat_g,
                'fiber_g' => $log->aiResult->fiber_g,
                'vitamins_json' => $log->aiResult->vitamins_json,
                'calorie_status' => $log->aiResult->calorie_status->value,
                'summary' => $log->aiResult->summary,
            ];
        }

        return $item;
    }
}
