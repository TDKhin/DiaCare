<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGlucoseReadingRequest;
use App\Models\GlucoseReading;
use Illuminate\Http\RedirectResponse;

class GlucoseReadingController extends Controller
{
    public function store(StoreGlucoseReadingRequest $request): RedirectResponse
    {
        $request->user()->glucoseReadings()->create([
            'value' => $request->integer('value'),
            'context' => $request->input('context'),
            'recorded_at' => $request->input('recorded_at') ?: now(),
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('glucose')->with('status', 'Glucose reading saved.');
    }

    public function destroy(GlucoseReading $glucoseReading): RedirectResponse
    {
        abort_unless($glucoseReading->user_id === auth()->id(), 404);
        $glucoseReading->delete();

        return back()->with('status', 'Glucose reading deleted.');
    }
}
