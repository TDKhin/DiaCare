<?php

use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminHealthManagementController;
use App\Http\Controllers\GlucoseReadingController;
use App\Http\Controllers\GlucosePhotoController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\MealPhotoController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::view('/learn', 'learn')->name('learn');
Route::view('/knowledge-sharing', 'knowledge')->name('knowledge');
Route::view('/quiz', 'quiz')->name('quiz');
Route::get('/assistant/history', [ChatbotController::class, 'history'])->name('assistant.history');
Route::post('/assistant/messages', [ChatbotController::class, 'send'])->name('assistant.send');
Route::post('/assistant/history/clear', [ChatbotController::class, 'clear'])->name('assistant.clear.post');
Route::delete('/assistant/history', [ChatbotController::class, 'clear'])->name('assistant.clear');
Route::view('/assistant', 'assistant')->name('assistant');

Route::middleware(['auth', 'verified', 'member'])->group(function () {
    Route::get('/dashboard', [HealthController::class, 'dashboard'])->name('dashboard');
    Route::get('/glucose', [HealthController::class, 'glucose'])->name('glucose');
    Route::post('/glucose', [GlucoseReadingController::class, 'store'])->name('glucose.store');
    Route::post('/glucose-photo/analyze', [GlucosePhotoController::class, 'analyze'])->name('glucose-photo.analyze');
    Route::post('/glucose-photo/{glucosePhotoAnalysis}/confirm', [GlucosePhotoController::class, 'confirm'])->name('glucose-photo.confirm');
    Route::delete('/glucose-photo/{glucosePhotoAnalysis}', [GlucosePhotoController::class, 'destroy'])->name('glucose-photo.destroy');
    Route::delete('/glucose/{glucoseReading}', [GlucoseReadingController::class, 'destroy'])->name('glucose.destroy');
    Route::get('/meals', [HealthController::class, 'meals'])->name('meals');
    Route::post('/meals/manual', [MealPhotoController::class, 'storeManual'])->name('meals.manual.store');
    Route::get('/activity', [HealthController::class, 'activity'])->name('activity');
    Route::post('/activity', [ActivityLogController::class, 'store'])->name('activity.store');
    Route::post('/activity/recommendation', [ActivityLogController::class, 'recommendation'])->name('activity.recommendation');
    Route::delete('/activity/{activityLog}', [ActivityLogController::class, 'destroy'])->name('activity.destroy');
    Route::get('/meal-photo', [HealthController::class, 'mealPhoto'])->name('meal-photo');
    Route::post('/meal-photo/analyze', [MealPhotoController::class, 'analyze'])->name('meal-photo.analyze');
    Route::post('/meal-photo/save', [MealPhotoController::class, 'store'])->name('meal-photo.save');
    Route::get('/meal-photo/{mealPhoto}', [MealPhotoController::class, 'show'])->name('meal-photo.show');
    Route::delete('/meal-photo/{mealPhoto}', [MealPhotoController::class, 'destroy'])->name('meal-photo.destroy');
    Route::get('/medications', [HealthController::class, 'medications'])->name('medications');
    Route::post('/medications', [MedicationController::class, 'store'])->name('medications.store');
    Route::delete('/medications/{medication}', [MedicationController::class, 'destroy'])->name('medications.destroy');
    Route::get('/reports', [HealthController::class, 'reports'])->name('reports');
    Route::get('/alerts', [HealthController::class, 'alerts'])->name('alerts');
    Route::post('/alerts/{alertKey}/read', [HealthController::class, 'markAlertRead'])->name('alerts.read');
});

Route::middleware(['auth', 'member'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users/admin', [UserManagementController::class, 'createAdmin'])->name('users.admin.create');
    Route::patch('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.role');
    Route::patch('/users/{user}/profile', [UserManagementController::class, 'updateAdminProfile'])->name('users.profile.update');
    Route::get('/nutrition', [AdminHealthManagementController::class, 'nutrition'])->name('nutrition');
    Route::get('/activity-medication', [AdminHealthManagementController::class, 'activityMedication'])->name('activity-medication');
    Route::get('/reports', [AdminHealthManagementController::class, 'reports'])->name('reports');
    Route::get('/alerts', [AdminHealthManagementController::class, 'alerts'])->name('alerts');
    Route::delete('/nutrition/{meal}', [AdminHealthManagementController::class, 'deleteMeal'])->name('nutrition.delete');
    Route::patch('/exercise/{activity}', [AdminHealthManagementController::class, 'updateActivity'])->name('exercise.update');
    Route::delete('/exercise/{activity}', [AdminHealthManagementController::class, 'deleteActivity'])->name('exercise.delete');
    Route::patch('/medications/{medication}', [AdminHealthManagementController::class, 'updateMedication'])->name('medications.update');
    Route::delete('/medications/{medication}', [AdminHealthManagementController::class, 'deleteMedication'])->name('medications.delete');
    Route::post('/glucose', [AdminHealthManagementController::class, 'createGlucose'])->name('glucose.create');
    Route::patch('/glucose/{reading}', [AdminHealthManagementController::class, 'updateGlucose'])->name('glucose.update');
    Route::delete('/glucose/{reading}', [AdminHealthManagementController::class, 'deleteGlucose'])->name('glucose.delete');
});

require __DIR__.'/auth.php';
