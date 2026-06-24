<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMealLogRequest;
use App\Repositories\MealLogRepository;
use App\Services\MealLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MealLogController extends Controller
{
    public function __construct(
        private MealLogService $mealLogService,
        private MealLogRepository $mealLogRepo,
    ) {}

    public function store(StoreMealLogRequest $request): RedirectResponse|JsonResponse
    {
        $log = $this->mealLogService->storeFromDetection(
            user: $request->user(),
            mealType: $request->input('meal_type'),
            foodName: $request->input('detected_food_name'),
            confidence: (float) $request->input('detection_confidence'),
            date: $request->input('date', today()->toDateString()),
            photo: $request->file('photo'),
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Makanan terdeteksi. Menunggu analisis nutrisi...',
                'data' => [
                    'id' => $log->id,
                    'meal_type' => $log->meal_type->value,
                    'detected_food_name' => $log->detected_food_name,
                    'detection_confidence' => (float) $log->detection_confidence,
                    'status' => $log->status->value,
                ],
            ], 201);
        }

        return back()->with('success', 'Makanan terdeteksi. Menunggu analisis nutrisi...');
    }

    public function status(Request $request, int $id): JsonResponse
    {
        $log = $this->mealLogRepo->findOrFail($id);
        $log->load('aiResult');

        abort_unless($log->user_id === $request->user()->id, 403);

        return response()->json([
            'id' => $log->id,
            'status' => $log->status->value,
            'detected_food_name' => $log->detected_food_name,
            'detection_confidence' => $log->detection_confidence !== null
                ? (float) $log->detection_confidence
                : null,
            'ai_result' => $log->aiResult ? [
                'food_name' => $log->aiResult->food_name,
                'calories' => $log->aiResult->calories,
            ] : null,
        ]);
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $log = $this->mealLogRepo->findOrFail($id);

        $this->mealLogService->delete($log, $request->user()->id);

        return back()->with('success', 'Data makanan berhasil dihapus.');
    }
}
