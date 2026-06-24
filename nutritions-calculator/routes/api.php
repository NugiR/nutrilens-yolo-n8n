<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\HistoryApiController;
use App\Http\Controllers\Api\HomeApiController;
use App\Http\Controllers\Api\MealAiResultApiController;
use App\Http\Controllers\Api\MealLogApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/forgot-password', [AuthApiController::class, 'sendResetLink']);

// n8n webhook callback — no auth required
Route::post('/webhook/nutrition-result', [WebhookController::class, 'handle']);

// Public meal log submission — no auth required
Route::post('/meal-logs', [MealLogApiController::class, 'store']);

// Authenticated routes — require Bearer token (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/me', [AuthApiController::class, 'me']);

    // Home — today's meals + daily summary
    Route::get('/home', [HomeApiController::class, 'index']);

    // Meal logs
    Route::delete('/meal-logs/{id}', [MealLogApiController::class, 'destroy']);
    Route::get('/meal-logs/today', [MealLogApiController::class, 'today']);

    // History — supports ?date=, ?date_from=, ?date_to=
    Route::get('/history', [HistoryApiController::class, 'index']);

    // Profile
    Route::get('/profile', [ProfileApiController::class, 'show']);
    Route::put('/profile', [ProfileApiController::class, 'update']);

    // Chart data — supports ?month=YYYY-MM
    Route::get('/chart-data', [\App\Http\Controllers\ChartController::class, 'data']);

    // Meal AI Results — nested under meal-log
    Route::get('/meal-logs/{mealLogId}/ai-result', [MealAiResultApiController::class, 'show']);
    Route::post('/meal-logs/{mealLogId}/ai-result', [MealAiResultApiController::class, 'store']);
    Route::put('/meal-logs/{mealLogId}/ai-result', [MealAiResultApiController::class, 'update']);
});
