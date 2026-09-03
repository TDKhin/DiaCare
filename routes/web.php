<?php

use App\Http\Controllers\HealthController;
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
    Route::get('/medications', [HealthController::class, 'medications'])->name('medications');
    Route::get('/reports', [HealthController::class, 'reports'])->name('reports');
    Route::get('/alerts', [HealthController::class, 'alerts'])->name('alerts');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
