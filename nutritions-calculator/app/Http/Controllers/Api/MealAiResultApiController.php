<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMealAiResultRequest;
use App\Http\Requests\UpdateMealAiResultRequest;
use App\Models\MealAiResult;
use App\Models\MealLog;
use App\Services\NutritionService;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealAiResultApiController extends Controller
{
    use ApiResponsable;

    public function __construct(private NutritionService $nutritionService) {}

    public function show(Request $request, int $mealLogId): JsonResponse
    {
        $log = MealLog::where('id', $mealLogId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $result = $log->aiResult;

        if (! $result) {
            return $this->error('AI result not found.', 404);
        }

        return $this->success($this->format($result));
    }

    public function store(StoreMealAiResultRequest $request, int $mealLogId): JsonResponse
    {
        $log = MealLog::where('id', $mealLogId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $this->nutritionService->recordAiResult($log, $request->validated(), $request->all());

        return $this->created($this->format($log->fresh()->aiResult), 'AI result recorded.');
    }

    public function update(UpdateMealAiResultRequest $request, int $mealLogId): JsonResponse
    {
        $log = MealLog::where('id', $mealLogId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $result = $log->aiResult;

        if (! $result) {
            return $this->error('AI result not found.', 404);
        }

        $data = $request->validated();
        $result->update($this->mapFields($data));

        if (isset($data['calories'])) {
            $result->update(['calorie_status' => $this->nutritionService->classifyCalories($data['calories'])]);
            $this->nutritionService->recalculateDailySummary($log->user_id, $log->date->toDateString());
        }

        return $this->success($this->format($result->fresh()), 'AI result updated.');
    }

    private function mapFields(array $data): array
    {
        $map = ['protein' => 'protein_g', 'carbs' => 'carbs_g', 'fat' => 'fat_g', 'fiber' => 'fiber_g', 'vitamins' => 'vitamins_json'];
        $result = [];
        foreach ($data as $key => $value) {
            $result[$map[$key] ?? $key] = $value;
        }
        return $result;
    }

    private function format(MealAiResult $result): array
    {
        return [
            'id'             => $result->id,
            'meal_log_id'    => $result->meal_log_id,
            'food_name'      => $result->food_name,
            'calories'       => (float) $result->calories,
            'protein_g'      => (float) $result->protein_g,
            'carbs_g'        => (float) $result->carbs_g,
            'fat_g'          => (float) $result->fat_g,
            'fiber_g'        => (float) $result->fiber_g,
            'vitamins_json'  => $result->vitamins_json,
            'calorie_status' => $result->calorie_status->value,
            'summary'        => $result->summary,
        ];
    }
}
