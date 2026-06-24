<?php

namespace App\Http\Controllers;

use App\Repositories\MealLogRepository;
use App\Services\NutritionService;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChartController extends Controller
{
    use ApiResponsable;
    public function __construct(
        private MealLogRepository $mealLogRepo,
        private NutritionService $nutritionService,
    ) {}

    public function data(Request $request): JsonResponse
    {
        $month = $request->query('month', now()->format('Y-m'));

        $logs = $this->mealLogRepo->monthlyForChart($request->user()->id, $month);
        $raw = $this->nutritionService->aggregateForChart($logs);

        [$year, $mon] = explode('-', $month);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int) $mon, (int) $year);

        $labels = range(1, $daysInMonth);
        $protein = array_fill(0, $daysInMonth, 0);
        $vitamin = array_fill(0, $daysInMonth, 0);
        $karbo = array_fill(0, $daysInMonth, 0);
        $lemak = array_fill(0, $daysInMonth, 0);
        $serat = array_fill(0, $daysInMonth, 0);

        foreach ($raw as $day => $values) {
            $i = (int) $day - 1;
            $protein[$i] = $values['protein'];
            $vitamin[$i] = $values['vitamins'];
            $karbo[$i] = $values['carbs'];
            $lemak[$i] = $values['fat'];
            $serat[$i] = $values['fiber'];
        }

        return $this->success(compact('labels', 'protein', 'vitamin', 'karbo', 'lemak', 'serat'));
    }
}
