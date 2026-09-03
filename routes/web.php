<?php

use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\MealPhotoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [HealthController::class, 'dashboard'])->name('dashboard');
    Route::get('/glucose', [HealthController::class, 'glucose'])->name('glucose');
    Route::get('/meals', [HealthController::class, 'meals'])->name('meals');
    Route::get('/activity', [HealthController::class, 'activity'])->name('activity');
    Route::get('/meal-photo', [HealthController::class, 'mealPhoto'])->name('meal-photo');
    Route::post('/meal-photo/analyze', [MealPhotoController::class, 'analyze'])->name('meal-photo.analyze');
    Route::get('/meal-photo/{mealPhoto}', [MealPhotoController::class, 'show'])->name('meal-photo.show');
    Route::delete('/meal-photo/{mealPhoto}', [MealPhotoController::class, 'destroy'])->name('meal-photo.destroy');
    Route::get('/medications', [HealthController::class, 'medications'])->name('medications');
    Route::get('/reports', [HealthController::class, 'reports'])->name('reports');
    Route::get('/alerts', [HealthController::class, 'alerts'])->name('alerts');
    Route::get('/assistant/history', [ChatbotController::class, 'history'])->name('assistant.history');
    Route::post('/assistant/messages', [ChatbotController::class, 'send'])->name('assistant.send');
    Route::delete('/assistant/history', [ChatbotController::class, 'clear'])->name('assistant.clear');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
