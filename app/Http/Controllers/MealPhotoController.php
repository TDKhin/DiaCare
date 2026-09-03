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
        $photo = $request->file('photo');
        $record = $request->user()->mealPhotos()->create([
            'photo_path' => $photo->store('meal-photos'),
            'status' => 'analyzing',
        ]);

        try {
            $analysis = $ai->analyzeMeal($photo);
            $record->update(['status' => 'complete', 'analysis' => $analysis]);

            return response()->json(['id' => $record->id, 'status' => $record->status, 'analysis' => $analysis]);
        } catch (Throwable $exception) {
            $record->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);

            return response()->json(['message' => $exception->getMessage(), 'id' => $record->id], 422);
        }
    }

    public function show(Request $request, MealPhoto $mealPhoto): JsonResponse
    {
        abort_unless($mealPhoto->user_id === $request->user()->id, 404);

        return response()->json($mealPhoto);
    }

    public function destroy(Request $request, MealPhoto $mealPhoto): RedirectResponse
    {
        abort_unless($mealPhoto->user_id === $request->user()->id, 404);
        Storage::delete($mealPhoto->photo_path);
        $mealPhoto->delete();

        return back()->with('status', 'Meal photo deleted.');
    }
}
