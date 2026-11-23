<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\GlucoseReading;
use App\Models\MealPhoto;
use App\Models\Medication;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminHealthManagementController extends Controller
{
    public function createActivity(Request $request): RedirectResponse
    {
        ActivityLog::create($request->validate(['user_id' => ['required', 'exists:users,id'], 'activity' => ['required', 'string', 'max:120'], 'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'], 'calories_burned' => ['nullable', 'numeric', 'min:0'], 'logged_at' => ['required', 'date']] ) + ['created_by_admin' => true]);
        return back()->with('status', 'Exercise record created.');
    }

    public function createMedication(Request $request): RedirectResponse
    {
        Medication::create($request->validate(['user_id' => ['required', 'exists:users,id'], 'name' => ['required', 'string', 'max:120'], 'dose' => ['nullable', 'string', 'max:120'], 'instructions' => ['nullable', 'string', 'max:500'], 'scheduled_at' => ['nullable', 'date'], 'active' => ['required', 'boolean']] ) + ['created_by_admin' => true]);
        return back()->with('status', 'Medication record created.');
    }

    public function createGlucose(Request $request): RedirectResponse
    {
        GlucoseReading::create($request->validate(['user_id' => ['required', 'exists:users,id'], 'value' => ['required', 'numeric', 'min:1', 'max:1000'], 'context' => ['required', 'string', 'max:80'], 'recorded_at' => ['required', 'date'], 'notes' => ['nullable', 'string', 'max:500']]));
        return back()->with('status', 'Glucose reading created.');
    }

    public function updateActivity(Request $request, ActivityLog $activity): RedirectResponse
    {
        $activity->update($request->validate(['activity' => ['required', 'string', 'max:120'], 'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'], 'calories_burned' => ['nullable', 'numeric', 'min:0'], 'logged_at' => ['required', 'date']]));
        return back()->with('status', 'Exercise record updated.');
    }

    public function deleteActivity(ActivityLog $activity): RedirectResponse
    {
        $activity->delete();
        return back()->with('status', 'Exercise record deleted.');
    }

    public function updateMedication(Request $request, Medication $medication): RedirectResponse
    {
        $medication->update($request->validate(['name' => ['required', 'string', 'max:120'], 'dose' => ['nullable', 'string', 'max:120'], 'instructions' => ['nullable', 'string', 'max:500'], 'scheduled_at' => ['nullable', 'date'], 'active' => ['required', 'boolean']]));
        return back()->with('status', 'Medication record updated.');
    }

    public function deleteMedication(Medication $medication): RedirectResponse
    {
        $medication->delete();
        return back()->with('status', 'Medication record deleted.');
    }

    public function updateGlucose(Request $request, GlucoseReading $reading): RedirectResponse
    {
        $reading->update($request->validate(['value' => ['required', 'numeric', 'min:1', 'max:1000'], 'context' => ['required', 'string', 'max:80'], 'recorded_at' => ['required', 'date'], 'notes' => ['nullable', 'string', 'max:500']]));
        return back()->with('status', 'Glucose reading updated.');
    }

    public function deleteGlucose(GlucoseReading $reading): RedirectResponse
    {
        $reading->delete();
        return back()->with('status', 'Glucose reading deleted.');
    }

    public function deleteMeal(MealPhoto $meal): RedirectResponse
    {
        $meal->delete();
        return back()->with('status', 'Meal analysis deleted.');
    }

    public function nutrition(): View
    {
        return view('admin.nutrition', [
            'title' => 'Food & Nutrition Management · DiaCare',
            'meals' => MealPhoto::with('user')->where('status', 'complete')->latest()->paginate(20),
        ]);
    }

    public function activityMedication(): View
    {
        return view('admin.activity-medication', [
            'title' => 'Exercise & Medication Management · DiaCare',
            'activities' => ActivityLog::with('user')->latest('logged_at')->paginate(15, ['*'], 'activities'),
            'medications' => Medication::with('user')->latest()->paginate(15, ['*'], 'medications'),
        ]);
    }

    public function reports(): View
    {
        return view('admin.reports', [
            'title' => 'Report Management · DiaCare',
            'userCount' => User::count(),
            'mealCount' => MealPhoto::where('status', 'complete')->count(),
            'readingCount' => GlucoseReading::count() + MealPhoto::where('status', 'complete')->get()->sum(fn (MealPhoto $meal): int => collect(['pre_meal_glucose', 'estimated_post_meal_glucose'])->filter(fn (string $field): bool => is_numeric(data_get($meal->analysis ?? [], $field)))->count()),
            'activityCount' => ActivityLog::count(),
            'medicationCount' => Medication::where('active', true)->count(),
            'readings' => GlucoseReading::with('user')->latest('recorded_at')->limit(30)->get(),
            'recentUsers' => User::with(['mealPhotos:id,user_id,analysis'])->withCount(['mealPhotos', 'activityLogs', 'medications', 'glucoseReadings'])->latest()->limit(20)->get()->each(function (User $user): void {
                $mealGlucoseCount = $user->mealPhotos->sum(function (MealPhoto $meal): int {
                    return collect(['pre_meal_glucose', 'estimated_post_meal_glucose'])->filter(fn (string $field): bool => is_numeric(data_get($meal->analysis, $field)))->count();
                });
                $user->glucose_count = $user->glucose_readings_count + $mealGlucoseCount;
            }),
        ]);
    }

    public function alerts(): View
    {
        $alerts = collect();
        User::with(['glucoseReadings', 'mealPhotos'])->get()->each(function (User $user) use ($alerts): void {
            $min = $user->glucose_target_min ?: 80;
            $max = $user->glucose_target_max ?: 130;
            $user->glucoseReadings->each(function (GlucoseReading $reading) use ($user, $min, $max, $alerts): void {
                if ($reading->value < $min || $reading->value > $max) $alerts->push(['user' => $user->name, 'type' => 'Glucose reading', 'value' => $reading->value.' mg/dL', 'date' => $reading->recorded_at, 'message' => "Outside target {$min}–{$max} mg/dL"]);
            });
            $user->mealPhotos->where('status', 'complete')->each(function ($meal) use ($user, $min, $max, $alerts): void {
                foreach ([['pre_meal_glucose', 'Before meal'], ['estimated_post_meal_glucose', 'After meal']] as [$field, $type]) { $value = data_get($meal->analysis, $field); if (is_numeric($value) && ((float) $value < $min || (float) $value > $max)) $alerts->push(['user' => $user->name, 'type' => $type, 'value' => round((float) $value).' mg/dL', 'date' => $meal->created_at, 'message' => "Outside target {$min}–{$max} mg/dL"]); }
            });
        });
        return view('admin.alerts', ['title' => 'Alert Management · DiaCare', 'alerts' => $alerts->sortByDesc('date')->values()]);
    }
}
