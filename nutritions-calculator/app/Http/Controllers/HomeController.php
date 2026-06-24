<?php

namespace App\Http\Controllers;

use App\Models\DailySummary;
use App\Repositories\MealLogRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private MealLogRepository $mealLogRepo,
    ) {}

    public function index(Request $request): View
    {
        $user         = $request->user();
        $todayLogs    = $this->mealLogRepo->todayForUser($user->id);
        $dailySummary = DailySummary::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        $hasInsight = $todayLogs->isNotEmpty();

        return view('home', compact('todayLogs', 'dailySummary', 'hasInsight'));
    }
}
