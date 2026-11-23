<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MedicationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'dose' => ['nullable', 'string', 'max:120'], 'instructions' => ['nullable', 'string', 'max:255'], 'scheduled_at' => ['nullable', 'date']]);
        $request->user()->medications()->create([...$data, 'active' => true]);
        return back()->with('status', 'Medication saved.');
    }
    public function destroy(Request $request, Medication $medication): RedirectResponse
    {
        abort_unless($medication->user_id === $request->user()->id, 404);
        $medication->delete();
        return back()->with('status', 'Medication removed.');
    }
}
