<?php

namespace App\Http\Controllers;

use App\Http\Requests\GlucosePhotoAnalysisRequest;
use App\Models\GlucosePhotoAnalysis;
use App\Services\AiHealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GlucosePhotoController extends Controller
{
    public function analyze(GlucosePhotoAnalysisRequest $request, AiHealthService $ai): JsonResponse
    {
        $photo = $request->file('photo');
        $record = $request->user()->glucosePhotoAnalyses()->create([
            'photo_path' => $photo->store('glucose-photos'),
            'status' => 'analyzing',
        ]);

        try {
            $analysis = $ai->analyzeGlucosePhoto($photo);
            $record->update([
                'status' => 'complete',
                'value' => $analysis['value'] ?? null,
                'unit' => $analysis['unit'] ?? 'mg/dL',
                'confidence' => $analysis['confidence'] ?? null,
                'analysis' => $analysis,
            ]);

            return response()->json(['id' => $record->id, 'status' => $record->status, 'analysis' => $analysis]);
        } catch (Throwable $exception) {
            $record->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);
            report($exception);
            return response()->json(['message' => 'The glucose photo could not be analyzed. Please check your Gemini API connection and try again.', 'id' => $record->id], 422);
        }
    }

    public function confirm(Request $request, GlucosePhotoAnalysis $glucosePhotoAnalysis): JsonResponse
    {
        abort_unless($glucosePhotoAnalysis->user_id === $request->user()->id, 404);
        abort_unless($glucosePhotoAnalysis->status === 'complete' && $glucosePhotoAnalysis->value, 422);

        $validated = $request->validate([
            'value' => ['required', 'integer', 'between:20,600'],
            'context' => ['nullable', 'string', 'max:60'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        $reading = $request->user()->glucoseReadings()->create([
            'value' => $validated['value'],
            'context' => $validated['context'] ?? 'Photo reading',
            'recorded_at' => now(),
            'notes' => 'Confirmed from glucose-meter photo analysis.',
        ]);

        $glucosePhotoAnalysis->update(['status' => 'confirmed']);

        return response()->json(['reading' => $reading, 'status' => 'confirmed']);
    }

    public function destroy(Request $request, GlucosePhotoAnalysis $glucosePhotoAnalysis): JsonResponse
    {
        abort_unless($glucosePhotoAnalysis->user_id === $request->user()->id, 404);
        Storage::delete($glucosePhotoAnalysis->photo_path);
        $glucosePhotoAnalysis->delete();

        return response()->json(['status' => 'deleted']);
    }
}
