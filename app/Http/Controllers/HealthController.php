<?php

namespace App\Http\Controllers;

use App\Models\GlucoseReading;
use App\Support\GlucoseRange;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        if (! $user->diabetes_type) return redirect()->route('profile.edit')->with('status', 'profile-setup');
        $recentReadings = $user->glucoseReadings()->latest('recorded_at')->limit(30)->get(); $latest = $recentReadings->first(); $weekReadings = $recentReadings->filter(fn (GlucoseReading $reading) => $reading->recorded_at->greaterThanOrEqualTo(now()->subDays(7))); $glucosePoints = $weekReadings->sortBy('recorded_at')->values(); $chartCoordinates = $this->coordinates($glucosePoints);
        $bmi = $user->height_cm && $user->weight_kg ? round($user->weight_kg / (($user->height_cm / 100) ** 2), 1) : null;
        $exerciseImpacts = $user->activityLogs()->with(['preGlucoseReading', 'postGlucoseReading'])->whereNotNull('pre_glucose_reading_id')->whereNotNull('post_glucose_reading_id')->latest('logged_at')->limit(3)->get();
        return view('dashboard', ['title' => 'Dashboard · DiaCare', 'latestReading' => $latest?->value, 'averageReading' => $weekReadings->isNotEmpty() ? round($weekReadings->avg('value')) : null, 'bmi' => $bmi, 'activityMinutes' => $user->activityLogs()->whereDate('logged_at', today())->sum('duration_minutes'), 'medications' => $user->medications()->where('active', true)->orderBy('scheduled_at')->get()->map(fn ($m) => ['name' => $m->name, 'dose' => $m->dose, 'time' => optional($m->scheduled_at)->format('g:i A') ?: 'Scheduled'])->all(), 'dashboardAlerts' => $this->buildAlerts($user)->take(3), 'glucosePoints' => $glucosePoints, 'chartPath' => $chartCoordinates->map(fn (array $p, int $i) => ($i === 0 ? 'M' : 'L').$p['x'].' '.$p['y'])->implode(' '), 'chartCoordinates' => $chartCoordinates, 'exerciseImpacts' => $exerciseImpacts]);
    }

    public function glucose(Request $request)
    {
        $user = $request->user();
        $defaultRange = config('glucose.time_slots.fasting');
        $trendDays = in_array((int) $request->query('trend_days', 7), [1, 3, 7, 30], true) ? (int) $request->query('trend_days', 7) : 7;
        $from = now()->startOfDay()->subDays($trendDays - 1);
        $manualReadings = $user->glucoseReadings()->latest('recorded_at')->limit(50)->get()->map(fn (GlucoseReading $reading) => ['id' => $reading->id, 'period' => $reading->context ?: 'Glucose reading', 'date' => $reading->recorded_at->format('M d - g:i A'), 'timestamp' => $reading->recorded_at->timestamp, 'value' => (float) $reading->value, 'tag' => GlucoseRange::status($reading->value, $reading->context), 'deletable' => true]);
        $mealReadings = $user->mealPhotos()->where('status', 'complete')->latest()->limit(50)->get()->flatMap(function ($meal) {
            $analysis = $meal->analysis ?: []; $items = collect();
            foreach ([['pre_meal_glucose', 'Before meal'], ['estimated_post_meal_glucose', '2 hours after meal']] as [$field, $context]) {
                if (is_numeric(data_get($analysis, $field))) $items->push(['id' => null, 'period' => $context, 'date' => $meal->created_at->format('M d - g:i A'), 'timestamp' => $meal->created_at->timestamp, 'value' => (float) data_get($analysis, $field), 'tag' => GlucoseRange::status((float) data_get($analysis, $field), $context), 'deletable' => false]);
            }
            return $items;
        });
        $readings = $manualReadings->merge($mealReadings)->sortByDesc('timestamp')->take(50)->values();
        $combined = $this->combinedGlucoseValues($user, $from);
        $currentTrend = $combined->groupBy(fn ($item) => $item['date'].'|'.$item['context'])->map(function ($items) { $first = $items->first(); return (object) ['date' => $first['date'], 'label' => Carbon::parse($first['date'])->format('M d').' - '.$first['context'], 'value' => round($items->avg('value')), 'count' => $items->count()]; })->sortBy(fn ($point) => $point->date.'|'.$point->label)->values();
        $sessions = $user->activityLogs()->with(['preGlucoseReading', 'postGlucoseReading'])->latest('logged_at')->limit(20)->get();
        return view('health.glucose', ['title' => 'Glucose - DiaCare', 'targetMin' => $defaultRange['min'], 'targetMax' => $defaultRange['max'], 'timeSlots' => config('glucose.time_slots'), 'readings' => $readings->all(), 'trendDays' => $trendDays, 'trendFrom' => $from, 'trendTo' => now(), 'trendAverage' => $combined->isNotEmpty() ? round($combined->avg('value')) : null, 'trendStatus' => $combined->isEmpty() ? 'No data' : 'Mixed contexts', 'trendCount' => $combined->count(), 'currentTrend' => $currentTrend, 'currentTrendCoordinates' => $this->coordinates($currentTrend), 'openExerciseSessions' => $sessions->filter(fn ($session) => ! $session->post_glucose_reading_id)->values(), 'exerciseComparisons' => $sessions->filter(fn ($session) => $session->preGlucoseReading && $session->postGlucoseReading)->map(fn ($session) => ['activity' => $session, 'change' => $session->postGlucoseReading->value - $session->preGlucoseReading->value])->values(), 'selectedExerciseId' => $request->integer('activity_log_id')]);

        $user = $request->user(); $min = $user->glucose_target_min ?: 80; $max = $user->glucose_target_max ?: 130; $manualReadings = $user->glucoseReadings()->latest('recorded_at')->limit(50)->get()->map(fn (GlucoseReading $r) => ['id' => $r->id, 'period' => $r->context ?: 'Glucose reading', 'date' => $r->recorded_at->format('M d · g:i A'), 'timestamp' => $r->recorded_at->timestamp, 'value' => (float) $r->value, 'tag' => $this->glucoseStatus($r->value, $min, $max), 'deletable' => true]); $mealReadings = $user->mealPhotos()->where('status', 'complete')->latest()->limit(50)->get()->flatMap(function ($meal) use ($min, $max) { $analysis = $meal->analysis ?: []; $items = collect(); foreach ([['pre_meal_glucose', 'Before meal'], ['estimated_post_meal_glucose', 'After meal']] as [$field, $label]) { if (is_numeric(data_get($analysis, $field))) { $value = (float) data_get($analysis, $field); $items->push(['id' => null, 'period' => $label, 'date' => $meal->created_at->format('M d · g:i A'), 'timestamp' => $meal->created_at->timestamp + ($label === 'After meal' ? 1 : 0), 'value' => $value, 'tag' => $this->glucoseStatus($value, $min, $max), 'deletable' => false]); } } return $items; }); $readings = collect($manualReadings->all())->merge($mealReadings->all())->sortByDesc('timestamp')->values()->take(50)->values(); $trendDays = in_array((int) $request->query('trend_days', 7), [1, 3, 7, 30], true) ? (int) $request->query('trend_days', 7) : 7; $from = now()->startOfDay()->subDays($trendDays - 1);
        $combined = $this->combinedGlucoseValues($user, $from); $currentTrend = $combined->groupBy(fn ($item) => $item['date'].'|'.$item['context'])->map(function ($items) { $first = $items->first(); return (object) ['date' => $first['date'], 'label' => Carbon::parse($first['date'])->format('M d').' · '.$first['context'], 'value' => round($items->avg('value')), 'count' => $items->count()]; })->sortBy(fn ($point) => $point->date.'|'.$point->label)->values(); $trendAverage = $combined->isNotEmpty() ? round($combined->avg('value')) : null;
        $exerciseSessions = $user->activityLogs()->with(['preGlucoseReading', 'postGlucoseReading'])->latest('logged_at')->limit(20)->get();
        $openExerciseSessions = $exerciseSessions->filter(fn ($activity) => ! $activity->post_glucose_reading_id)->values();
        $exerciseComparisons = $exerciseSessions->filter(fn ($activity) => $activity->preGlucoseReading && $activity->postGlucoseReading)->map(fn ($activity) => ['activity' => $activity, 'change' => $activity->postGlucoseReading->value - $activity->preGlucoseReading->value])->values();
        return view('health.glucose', ['title' => 'Glucose · DiaCare', 'targetMin' => $min, 'targetMax' => $max, 'readings' => $readings->all(), 'trendDays' => $trendDays, 'trendFrom' => $from, 'trendTo' => now(), 'trendAverage' => $trendAverage, 'trendStatus' => $trendAverage === null ? 'No data' : $this->glucoseStatus($trendAverage, $min, $max), 'trendCount' => $combined->count(), 'currentTrend' => $currentTrend, 'currentTrendCoordinates' => $this->coordinates($currentTrend), 'openExerciseSessions' => $openExerciseSessions, 'exerciseComparisons' => $exerciseComparisons, 'selectedExerciseId' => $request->integer('activity_log_id')]);
    }

    public function meals(Request $request) { return view('health.meals', ['title' => 'Diet & Meals · DiaCare', 'meals' => $request->user()->mealPhotos()->where('status', 'complete')->latest()->get()]); }

    public function activity(Request $request)
    {
        $activities = $request->user()->activityLogs()->with(['preGlucoseReading', 'postGlucoseReading'])->latest('logged_at')->limit(50)->get(); $daily = $activities->filter(fn ($a) => $a->logged_at->greaterThanOrEqualTo(now()->subDays(6)->startOfDay()))->groupBy(fn ($a) => $a->logged_at->toDateString())->map(fn ($items, $date) => ['label' => Carbon::parse($date)->format('M d'), 'minutes' => $items->sum('duration_minutes'), 'calories' => $items->sum('calories_burned')])->values();
        return view('health.activity', ['title' => 'Activity · DiaCare', 'activities' => $activities, 'dailyActivity' => $daily]);
    }

    public function mealPhoto(Request $request) { return view('health.meal-photo', ['title' => 'Meal Photo AI · DiaCare', 'mealPhotos' => $request->user()->mealPhotos()->latest()->limit(12)->get(), 'latestGlucose' => $request->user()->glucoseReadings()->latest('recorded_at')->first()]); }
    public function medications(Request $request) { return view('health.medications', ['title' => 'Medications · DiaCare', 'medications' => $request->user()->medications()->where('active', true)->orderBy('scheduled_at')->get()]); }

    public function reports(Request $request)
    {
        $days = in_array((int) $request->query('days', 7), [1, 3, 7, 30], true) ? (int) $request->query('days', 7) : 7; $user = $request->user(); $from = now()->startOfDay()->subDays($days - 1); $combined = $this->combinedGlucoseValues($user, $from); $daily = $combined->groupBy('date')->map(fn ($items, $date) => ['date' => $date, 'label' => Carbon::parse($date)->format('M d'), 'value' => round($items->avg('value')), 'count' => $items->count()])->sortBy('date')->values(); $coords = $this->coordinates($daily->map(fn ($p) => (object) ['value' => $p['value']])->values()); $average = $combined->isNotEmpty() ? round($combined->avg('value')) : null; $bmi = $user->height_cm && $user->weight_kg ? round($user->weight_kg / (($user->height_cm / 100) ** 2), 1) : null;
        return view('health.reports', ['title' => 'Reports · DiaCare', 'days' => $days, 'from' => $from, 'to' => now(), 'readings' => $combined, 'daily' => $daily, 'coordinates' => $coords, 'average' => $average, 'trendStatus' => $average === null ? 'No data' : $this->glucoseStatus($average, $user->glucose_target_min ?: 80, $user->glucose_target_max ?: 130), 'activities' => $user->activityLogs()->where('logged_at', '>=', $from)->get(), 'meals' => $user->mealPhotos()->where('created_at', '>=', $from)->where('status', 'complete')->get(), 'medications' => $user->medications()->where('active', true)->get(), 'bmi' => $bmi]);
    }

    public function alerts(Request $request)
    {
        $readKeys = collect($request->session()->get('read_alerts', []));
        $all = $this->buildAlerts($request->user())->map(fn (array $alert) => $alert + ['read' => $readKeys->contains($alert['key'])]);
        $filter = $request->query('filter', 'all');
        if (! in_array($filter, ['all', 'unread', 'important'], true)) $filter = 'all';
        $alerts = $filter === 'unread' ? $all->where('read', false)->values() : ($filter === 'important' ? $all->whereIn('type', ['critical', 'warning'])->values() : $all->values());
        return view('health.alerts', ['title' => 'Alerts · DiaCare', 'alerts' => $alerts, 'filter' => $filter, 'unreadCount' => $all->where('read', false)->count(), 'totalCount' => $all->count()]);
    }

    public function markAlertRead(Request $request, string $alertKey)
    {
        $readKeys = collect($request->session()->get('read_alerts', []))->push($alertKey)->unique()->values()->all();
        $request->session()->put('read_alerts', $readKeys);
        return back()->with('status', 'alert-read');
    }

    private function buildAlerts($user)
    {
        $min = $user->glucose_target_min ?: 80; $max = $user->glucose_target_max ?: 130; $alerts = collect(); $readings = $user->glucoseReadings()->where('recorded_at', '>=', now()->subDays(6)->startOfDay())->orderBy('recorded_at')->get();
        foreach ($readings->groupBy(fn ($r) => $r->recorded_at->toDateString()) as $date => $items) { $average = round($items->avg('value')); $important = $average < $min || $average > $max; $alerts->push(['key' => 'glucose-'.$date, 'type' => $important ? 'critical' : 'information', 'color' => $important ? 'orange' : 'blue', 'title' => 'Glucose · '.Carbon::parse($date)->format('M d'), 'message' => "Daily average was {$average} mg/dL (target {$min}–{$max}).", 'date' => Carbon::parse($date)->format('M d, Y'), 'time' => Carbon::parse($date)->format('M d')]); } $mealPhotos = $user->mealPhotos()->where('status', 'complete')->where('created_at', '>=', now()->subDays(6)->startOfDay())->latest()->get(); foreach ($mealPhotos as $meal) { $analysis = $meal->analysis ?: []; foreach ([['pre_meal_glucose', 'Before meal'], ['estimated_post_meal_glucose', 'After meal']] as [$field, $label]) { $value = data_get($analysis, $field); if (is_numeric($value) && ((float) $value < $min || (float) $value > $max)) { $value = round((float) $value); $alerts->push(['key' => 'meal-glucose-'.$meal->id.'-'.strtolower(str_replace(' ', '-', $label)), 'type' => 'critical', 'color' => 'orange', 'title' => 'Glucose out of range · '.$label, 'message' => "{$label} estimate was {$value} mg/dL (target {$min}–{$max}).", 'date' => $meal->created_at->format('M d, Y'), 'time' => $meal->created_at->format('M d · g:i A')]); } } }
        foreach ($user->activityLogs()->where('logged_at', '>=', now()->subDays(6)->startOfDay())->get()->groupBy(fn ($a) => $a->logged_at->toDateString()) as $date => $items) { $alerts->push(['key' => 'activity-'.$date, 'type' => 'information', 'color' => 'blue', 'title' => 'Activity · '.Carbon::parse($date)->format('M d'), 'message' => $items->sum('duration_minutes').' minutes · '.$items->sum('calories_burned').' kcal logged.', 'date' => Carbon::parse($date)->format('M d, Y'), 'time' => Carbon::parse($date)->format('M d')]); }
        foreach ($user->medications()->where('active', true)->whereNotNull('scheduled_at')->get() as $medication) { $due = $medication->scheduled_at; if ($due->isPast()) $alerts->push(['key' => 'medication-overdue-'.$medication->id, 'type' => 'warning', 'color' => 'orange', 'title' => 'Medication overdue', 'message' => $medication->name.' was scheduled for '.$due->format('M d · g:i A').'.', 'date' => $due->format('M d, Y'), 'time' => $due->format('M d · g:i A')]); else if ($due->lessThanOrEqualTo(now()->addHours(2))) $alerts->push(['key' => 'medication-due-'.$medication->id, 'type' => 'reminder', 'color' => 'blue', 'title' => 'Medication due soon', 'message' => $medication->name.' is scheduled for '.$due->format('g:i A').'.', 'date' => $due->format('M d, Y'), 'time' => $due->format('M d · g:i A')]); }
        return $alerts->isEmpty() ? collect([['key' => 'no-alerts', 'type' => 'information', 'color' => 'blue', 'title' => 'No alerts yet', 'message' => 'Add glucose, activity, or medication data to receive daily reminders.', 'date' => now()->format('M d, Y'), 'time' => 'Today']]) : $alerts->sortByDesc('time')->values();
    }

    private function combinedGlucoseValues($user, $from)
    {
        $manual = $user->glucoseReadings()->where('recorded_at', '>=', $from)->get()->map(fn ($reading) => ['date' => $reading->recorded_at->toDateString(), 'value' => (float) $reading->value, 'source' => 'Manual reading', 'context' => $reading->context ?: 'Glucose']);
        $meals = $user->mealPhotos()->where('status', 'complete')->where('created_at', '>=', $from)->get()->flatMap(function ($meal) { $analysis = $meal->analysis ?: []; $items = collect(); if (is_numeric(data_get($analysis, 'pre_meal_glucose'))) $items->push(['date' => $meal->created_at->toDateString(), 'value' => (float) data_get($analysis, 'pre_meal_glucose'), 'source' => 'Meal analysis', 'context' => 'Before meal']); if (is_numeric(data_get($analysis, 'estimated_post_meal_glucose'))) $items->push(['date' => $meal->created_at->toDateString(), 'value' => (float) data_get($analysis, 'estimated_post_meal_glucose'), 'source' => 'Meal analysis', 'context' => 'After meal']); return $items; });
        return collect($manual->all())->merge($meals->values());
    }

    private function glucoseStatus(?float $value, int|float $min, int|float $max): string
    {
        return $value === null ? 'No data' : ($value < $min ? 'Low' : ($value > $max ? 'High' : 'In range'));
    }

    private function coordinates($points)
    {
        return collect($points)->values()->map(function ($point, int $index) use ($points) { $count = count($points); return ['x' => $count <= 1 ? 480 : round($index * 960 / ($count - 1), 1), 'y' => round(190 - (max(40, min(300, $point->value)) - 40) * 155 / 260, 1), 'value' => $point->value]; });
    }
}
