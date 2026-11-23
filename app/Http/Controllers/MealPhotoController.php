<?php

namespace App\Http\Controllers;

use App\Http\Requests\MealPhotoAnalysisRequest;
use App\Models\MealPhoto;
use App\Services\AiHealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MealPhotoController extends Controller
{
    public function analyze(MealPhotoAnalysisRequest $request, AiHealthService $ai): JsonResponse
    {
        try {
            return response()->json(['status' => 'complete', 'analysis' => $ai->analyzeMeal($request->file('photo'))]);
        } catch (Throwable $exception) {
            report($exception);
            $details = $exception->getMessage();
            $message = str_contains($details, 'API key')
                ? 'The photo AI API key is missing. Add GROQ_VISION_API_KEY to your .env file and run php artisan optimize:clear.'
                : (str_contains($details, 'could not respond')
                    ? 'The AI nutrition service did not respond. Check your internet connection and Groq model/API settings, then try again.'
                    : (str_contains($details, 'could not be read clearly') || str_contains($details, 'invalid meal photo analysis')
                        ? 'The meal could not be identified confidently. Try a brighter photo showing the whole plate from above.'
                        : 'The meal photo could not be analyzed right now. Please try again with a brighter, sharper photo.'));
            return response()->json(['message' => $message], 422);
        }
    }

    public function storeManual(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'meal_name' => ['required', 'string', 'max:120'],
            'calories' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'carbohydrates_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'protein_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'fat_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'fiber_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'glucose_level' => ['nullable', 'numeric', 'min:20', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:120'],
        ]);
        $request->user()->mealPhotos()->create([
            'photo_path' => null,
            'status' => 'complete',
            'analysis' => array_merge($validated, ['source' => 'manual']),
        ]);
        return back()->with('status', 'Manual meal added.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'], 'analysis' => ['required', 'json']]);
        $analysis = json_decode($validated['analysis'], true);
        if (! is_array($analysis)) return response()->json(['message' => 'The meal analysis is invalid.'], 422);
        $record = $request->user()->mealPhotos()->create(['photo_path' => $request->file('photo')->store('meal-photos'), 'status' => 'complete', 'analysis' => $analysis]);
        return response()->json(['id' => $record->id, 'status' => 'complete', 'analysis' => $analysis]);
    }

    public function show(Request $request, MealPhoto $mealPhoto): JsonResponse
    {
        abort_unless($mealPhoto->user_id === $request->user()->id, 404);
        return response()->json($mealPhoto);
    }

    public function destroy(Request $request, MealPhoto $mealPhoto): RedirectResponse
    {
        abort_unless($mealPhoto->user_id === $request->user()->id, 404);
        Storage::delete($mealPhoto->photo_path); $mealPhoto->delete();
        return back()->with('status', 'Meal photo deleted.');
    }
}
