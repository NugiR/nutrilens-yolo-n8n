<?php

namespace App\Repositories;

use App\Models\MealLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MealLogRepository
{
    public function todayForUser(int $userId, ?int $limit = null, int $page = 1): Collection|LengthAwarePaginator
    {
        $query = MealLog::with('aiResult')
            ->where('user_id', $userId)
            ->whereDate('date', today());

        return $limit
            ? $query->paginate($limit, ['*'], 'page', $page)
            : $query->get();
    }

    public function monthlyForChart(int $userId, string $month): Collection
    {
        [$year, $mon] = explode('-', $month);

        return MealLog::with('aiResult')
            ->where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $mon)
            ->where('status', 'done')
            ->get(['id', 'date']);
    }

    public function forUserFiltered(
        int $userId,
        ?string $date,
        ?string $dateFrom,
        ?string $dateTo,
        ?int $limit = null,
        int $page = 1,
    ): Collection|LengthAwarePaginator {
        $query = MealLog::with('aiResult')
            ->where('user_id', $userId)
            ->where('status', 'done');

        if ($date) {
            $query->whereDate('date', $date);
        } elseif ($dateFrom && $dateTo) {
            $query->whereBetween('date', [$dateFrom, $dateTo]);
        }

        $query->orderByDesc('date')->orderBy('meal_type');

        return $limit
            ? $query->paginate($limit, ['*'], 'page', $page)
            : $query->get();
    }

    public function findOrFail(int $id): MealLog
    {
        return MealLog::findOrFail($id);
    }
}
