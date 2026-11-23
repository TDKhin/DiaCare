<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGlucoseReadingRequest;
use App\Models\ActivityLog;
use App\Models\GlucoseReading;
use Illuminate\Http\RedirectResponse;

class GlucoseReadingController extends Controller
{
    public function store(StoreGlucoseReadingRequest $request): RedirectResponse
    {
        $activity = null;
        if ($request->filled('activity_log_id')) {
            $activity = $request->user()->activityLogs()->whereKey($request->integer('activity_log_id'))->firstOrFail();

            if ($activity->post_glucose_reading_id) {
                return back()->withErrors(['activity_log_id' => 'This exercise session already has a post-exercise glucose reading.']);
            }
        }

        $reading = $request->user()->glucoseReadings()->create([
            'value' => $request->integer('value'),
            'context' => $activity ? 'After exercise' : $request->input('context'),
            'recorded_at' => now(),
            'notes' => $request->input('notes'),
        ]);

        if ($activity) {
            $activity->update(['post_glucose_reading_id' => $reading->id]);
        }

        return redirect()->route('glucose')->with('status', $activity ? 'Post-exercise glucose reading saved.' : 'Glucose reading saved.');
    }

    public function destroy(GlucoseReading $glucoseReading): RedirectResponse
    {
        abort_unless($glucoseReading->user_id === auth()->id(), 404);
        $glucoseReading->delete();

        return back()->with('status', 'Glucose reading deleted.');
    }
}
