<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends Controller
{
    public function recommendation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'glucose' => ['nullable', 'integer', 'between:20,600'],
            'symptoms' => ['required', 'boolean'],
            'complications' => ['required', 'boolean'],
            'fitness' => ['required', 'in:new,regular'],
            'minutes' => ['required', 'integer', 'between:5,120'],
        ]);
        $user = $request->user(); $min = $user->glucose_target_min ?: 90; $max = $user->glucose_target_max ?: 130;
        if ($data['symptoms']) return response()->json(['level' => 'stop', 'title' => 'Do not begin exercise now', 'message' => 'Because you reported concerning symptoms, seek urgent medical advice instead of relying on an exercise recommendation.']);
        if ($data['complications']) return response()->json(['level' => 'caution', 'title' => 'Use a clinician-approved plan', 'message' => 'Choose only activity already approved by your healthcare professional. Avoid starting a new or vigorous routine from this tool.']);
        if ($data['glucose'] && ($data['glucose'] < $min || $data['glucose'] > $max)) return response()->json(['level' => 'caution', 'title' => 'Check your care plan first', 'message' => "Your reading is outside your configured {$min}–{$max} mg/dL range. Do not start a new or vigorous workout based on this suggestion; follow your agreed glucose plan and recheck as advised."]);
        $activity = $data['fitness'] === 'new' ? 'a gentle walk or easy stationary cycling' : 'moderate walking, cycling, or light resistance exercise';
        return response()->json(['level' => 'go', 'title' => 'Suggested activity', 'message' => "Try {$activity} for {$data['minutes']} minutes. Record an actual glucose reading before and after. Stop if you feel unwell.", 'video_url' => 'https://www.youtube.com/watch?v=ji32VS9gros']);
    }
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'activity' => ['required', 'string', 'max:120'],
            'intensity' => ['required', 'in:light,moderate,vigorous'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        ]);
        $activity = strtolower($data['activity']);
        $baseMet = str_contains($activity, 'run') || str_contains($activity, 'jog') ? 8.3 : (str_contains($activity, 'cycle') || str_contains($activity, 'bike') ? 7.5 : (str_contains($activity, 'swim') ? 7 : (str_contains($activity, 'walk') ? 3.5 : 4.5)));
        $met = $baseMet * match ($data['intensity']) { 'light' => 0.8, 'vigorous' => 1.2, default => 1 };
        $weight = (float) ($request->user()->weight_kg ?: 70);
        $calories = (int) round($met * 3.5 * $weight / 200 * $data['duration_minutes']);
        $loggedAt = now();
        $preReading = $request->user()->glucoseReadings()->where('recorded_at', '<=', $loggedAt)->latest('recorded_at')->first();
        $request->user()->activityLogs()->create([...$data, 'calories_burned' => $calories, 'logged_at' => $loggedAt, 'pre_glucose_reading_id' => $preReading?->id]);
        return back()->with('status', 'Activity saved. You can now add an actual post-exercise glucose reading.');
    }
    public function destroy(Request $request, ActivityLog $activityLog): RedirectResponse
    {
        abort_unless($activityLog->user_id === $request->user()->id, 404);
        $activityLog->delete();
        return back()->with('status', 'Activity removed.');
    }
}
