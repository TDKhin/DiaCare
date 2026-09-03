<?php

namespace App\Http\Controllers;

use App\Models\GlucoseReading;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $recentReadings = $user->glucoseReadings()->latest('recorded_at')->limit(30)->get();
        $latest = $recentReadings->first();
        $weekReadings = $recentReadings->filter(fn (GlucoseReading $reading) => $reading->recorded_at->greaterThanOrEqualTo(now()->subDays(7)));

        $glucosePoints = $weekReadings->sortBy('recorded_at')->values();
        $chartCoordinates = $glucosePoints->map(function (GlucoseReading $reading, int $index) use ($glucosePoints) {
            $x = $glucosePoints->count() <= 1 ? 380 : round($index * 760 / ($glucosePoints->count() - 1), 1);
            $y = round(170 - (max(50, min(250, $reading->value)) - 50) * 138 / 200, 1);
            return ['x' => $x, 'y' => $y, 'value' => $reading->value];
        });
        $chartPath = $chartCoordinates->map(fn (array $point, int $index) => ($index === 0 ? 'M' : 'L').$point['x'].' '.$point['y'])->implode(' ');

        return view('dashboard', [
            'title' => 'Dashboard · DiaCare',
            'latestReading' => $latest?->value,
            'averageReading' => $weekReadings->isNotEmpty() ? round($weekReadings->avg('value')) : null,
            'activityMinutes' => 0,
            'medications' => [],
            'glucosePoints' => $glucosePoints,
            'chartPath' => $chartPath,
            'chartCoordinates' => $chartCoordinates,
        ]);
    }

    public function glucose(Request $request)
    {
        $readings = $request->user()->glucoseReadings()->latest('recorded_at')->limit(50)->get();

        return view('health.glucose', [
            'title' => 'Glucose · DiaCare',
            'readings' => $readings->map(fn (GlucoseReading $reading) => [
                'period' => $reading->context ?: 'Glucose reading',
                'date' => $reading->recorded_at->format('M d · g:i A'),
                'value' => $reading->value,
                'tag' => $reading->value < 70 ? 'Low' : ($reading->value > 130 ? 'High' : 'In range'),
            ])->all(),
        ]);
    }

    public function meals()
    {
        return view('health.meals', ['title' => 'Meals · DiaCare', 'meals' => []]);
    }

    public function activity()
    {
        return view('health.activity', ['title' => 'Activity · DiaCare']);
    }

    public function mealPhoto()
    {
        return view('health.meal-photo', ['title' => 'Meal Photo AI · DiaCare']);
    }

    public function medications()
    {
        return view('health.medications', ['title' => 'Medications · DiaCare', 'medications' => []]);
    }

    public function reports()
    {
        return view('health.reports', ['title' => 'Reports · DiaCare']);
    }

    public function alerts()
    {
        return view('health.alerts', ['title' => 'Alerts · DiaCare', 'alerts' => []]);
    }
}
