<?php

namespace App\Http\Controllers;

use App\Repositories\MealLogRepository;
use App\Services\NutritionService;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    use ApiResponsable;
    public function __construct(
        private MealLogRepository $mealLogRepo,
        private NutritionService $nutritionService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $secret = config('services.n8n.secret');

        if ($secret && $request->header('X-Webhook-Secret') !== $secret) {
            abort(401, 'Invalid webhook secret.');
        }

        $data = $request->validate([
            'meal_log_id' => ['required', 'integer', 'exists:meal_logs,id'],
            'food_name' => ['required', 'string'],
            'calories' => ['required', 'numeric', 'min:0'],
            'protein' => ['nullable', 'numeric', 'min:0'],
            'carbs' => ['nullable', 'numeric', 'min:0'],
            'fat' => ['nullable', 'numeric', 'min:0'],
            'fiber' => ['nullable', 'numeric', 'min:0'],
            'vitamins' => ['nullable', 'array'],
            'summary' => ['nullable', 'string'],
        ]);

        $log = $this->mealLogRepo->findOrFail($data['meal_log_id']);

        $this->nutritionService->recordAiResult($log, $data, $request->all());

        return $this->success(null, 'Nutrition result recorded.');
    }
}
