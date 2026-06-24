<?php

namespace App\Http\Controllers;

use App\Repositories\MealLogRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function __construct(
        private MealLogRepository $mealLogRepo,
    ) {}

    public function index(Request $request): View
    {
        $logs = $this->mealLogRepo->forUserFiltered(
            userId: $request->user()->id,
            date: $request->query('date'),
            dateFrom: $request->query('date_from'),
            dateTo: $request->query('date_to'),
        );

        return view('history', compact('logs'));
    }
}
