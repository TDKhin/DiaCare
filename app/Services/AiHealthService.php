<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiHealthService
{
    public function analyzeMeal(UploadedFile $photo): array
    {
        $baseUrl = rtrim(config('services.ai.base_url'), '/');
        $apiKey = config('services.ai.key');
        $model = config('services.ai.vision_model', 'gpt-4o-mini');

        if (! $baseUrl || ! $apiKey) {
            throw new RuntimeException('AI analysis is not configured. Add AI_API_BASE_URL and AI_API_KEY to your .env file.');
        }

        $dataUrl = 'data/'.$photo->getMimeType().';base64,'.base64_encode((string) file_get_contents($photo->getRealPath()));

        $response = Http::withToken($apiKey)->acceptJson()->timeout(60)->post($baseUrl.'/chat/completions', [
            'model' => $model,
            'temperature' => 0.2,
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'meal_analysis',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'meal_name' => ['type' => 'string'],
                            'confidence' => ['type' => 'number'],
                            'calories' => ['type' => 'integer'],
                            'carbohydrates_g' => ['type' => 'number'],
                            'protein_g' => ['type' => 'number'],
                            'fat_g' => ['type' => 'number'],
                            'fiber_g' => ['type' => 'number'],
                            'ingredients' => ['type' => 'array', 'items' => ['type' => 'string']],
                            'advice' => ['type' => 'string'],
                        ],
                        'required' => ['meal_name', 'confidence', 'calories', 'carbohydrates_g', 'protein_g', 'fat_g', 'fiber_g', 'ingredients', 'advice'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'messages' => [[
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => 'Analyze this meal photo for general nutrition tracking. Return estimated values only, clearly state that this is not medical advice, and do not diagnose or recommend medication changes.'],
                    ['type' => 'image_url', 'image_url' => ['url' => $dataUrl, 'detail' => 'auto']],
                ],
            ]],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('The AI provider could not analyze this image.');
        }

        $content = data_get($response->json(), 'choices.0.message.content');
        $analysis = json_decode((string) $content, true);

        if (! is_array($analysis)) {
            throw new RuntimeException('The AI provider returned an invalid analysis.');
        }

        return $analysis;
    }

    public function chat(array $messages): string
    {
        $baseUrl = rtrim(config('services.ai.base_url'), '/');
        $apiKey = config('services.ai.key');
        $model = config('services.ai.chat_model', 'gpt-5-mini');

        if (! $baseUrl || ! $apiKey) {
            throw new RuntimeException('AI chat is not configured. Add AI_API_BASE_URL and AI_API_KEY to your .env file.');
        }

        $response = Http::withToken($apiKey)->acceptJson()->timeout(45)->post($baseUrl.'/chat/completions', [
            'model' => $model,
            'temperature' => 0.4,
            'messages' => array_merge([
                ['role' => 'system', 'content' => 'You are DiaCare Assistant. Give calm, concise wellness-tracking guidance. Do not diagnose, prescribe, interpret emergencies, or recommend changing medication. Encourage the user to contact a qualified clinician for medical decisions and emergency services for urgent symptoms.'],
            ], $messages),
        ]);

        if ($response->failed()) {
            throw new RuntimeException('The AI provider could not answer right now.');
        }

        return trim((string) data_get($response->json(), 'choices.0.message.content'));
    }
}
