<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MealLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Guest-only routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// n8n callback — no CSRF, no auth (see bootstrap/app.php withMiddleware exclusion)
Route::post('/webhook/nutrition-result', [WebhookController::class, 'handle']);

// DEV ONLY — test n8n webhook connectivity (remove before production)
if (app()->isLocal()) {
    Route::get('/dev/test-webhook', function () {
        /** @var \App\Services\WebhookService $svc */
        $svc = app(\App\Services\WebhookService::class);

        // find any existing meal-photo to attach, or skip image
        $files = \Illuminate\Support\Facades\Storage::disk('public')->files('meal-photos');
        $photoPath = count($files) > 0 ? $files[0] : null;

        $svc->sendToN8n([
            'meal_log_id' => 0,
            'food_name'   => 'test-ping',
            'confidence'  => 0.99,
            'meal_type'   => 'pagi',
            'date'        => now()->toDateString(),
            'user_id'     => 0,
        ], $photoPath);

        return response()->json([
            'status'     => 'dispatched',
            'photo_path' => $photoPath ?? 'none — save a real meal log first',
        ]);
    });
}

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::post('/meal-logs', [MealLogController::class, 'store'])->name('meal-logs.store');
    Route::get('/meal-logs/{id}/status', [MealLogController::class, 'status'])->name('meal-logs.status');
    Route::delete('/meal-logs/{id}', [MealLogController::class, 'destroy'])->name('meal-logs.destroy');

    Route::get('/history', [HistoryController::class, 'index'])->name('history');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/api/chart-data', [ChartController::class, 'data'])->name('chart.data');
});
