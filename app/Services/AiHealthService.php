<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiHealthService
{
    public function analyzeMeal(UploadedFile $photo): array
    {
        $key = config('services.ai.key');
        $model = config('services.ai.vision_model', 'gemini-3.6-flash');
        $provider = config('services.ai.provider', 'gemini');

        if (! $key) {
            throw new RuntimeException('AI analysis is not configured. Add VISION_API_KEY to your .env file.');
        }

        $prompt = 'Analyze this meal photo for general nutrition tracking. Return JSON only with meal_name (string), confidence (number from 0 to 1), calories (integer), carbohydrates_g (number), protein_g (number), fat_g (number), fiber_g (number), ingredients (array of strings), and advice (string). Estimate values conservatively, say that this is not medical advice, and do not diagnose or recommend medication changes.';
        $mime = $photo->getMimeType() ?: 'image/jpeg';
        $contents = [[
            'role' => 'user',
            'parts' => [
                ['text' => $prompt],
                ['inline_data' => ['mime_type' => $mime, 'data' => base64_encode((string) file_get_contents($photo->getRealPath()))]],
            ],
        ]];

        $payload = $provider === 'gemini'
            ? $this->geminiRequest($key, $model, $contents, ['responseMimeType' => 'application/json', 'temperature' => 0.2])
            : $this->openAiRequest($key, config('services.ai.base_url'), $model, [[
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => $prompt],
                    ['type' => 'image_url', 'image_url' => ['url' => 'data/'.$mime.';base64,'.base64_encode((string) file_get_contents($photo->getRealPath())), 'detail' => 'auto']],
                ],
            ]]);

        $content = $provider === 'gemini'
            ? data_get($payload, 'candidates.0.content.parts.0.text')
            : data_get($payload, 'choices.0.message.content');
        $analysis = json_decode($this->cleanJson((string) $content), true);

        if (! is_array($analysis)) {
            throw new RuntimeException('The AI provider returned an invalid meal analysis.');
        }

        return $analysis;
    }

    public function chat(array $messages): string
    {
        $key = config('services.ai.key');
        $model = config('services.ai.chat_model', 'gemini-3.6-flash');
        $provider = config('services.ai.provider', 'gemini');

        if (! $key) {
            throw new RuntimeException('AI chat is not configured. Add VISION_API_KEY to your .env file.');
        }

        $system = 'You are DiaCare Assistant. Answer diabetes-health information questions in calm, plain language for general education and wellness tracking. Never diagnose, prescribe, interpret emergencies, or recommend changing medication. Encourage a qualified clinician for medical decisions and emergency services for urgent symptoms. Clearly distinguish general information from personal medical advice.';

        if ($provider === 'gemini') {
            $contents = array_map(fn (array $message) => [
                'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $message['content']]],
            ], $messages);
            $payload = $this->geminiRequest($key, $model, $contents, ['temperature' => 0.4], $system);
            return trim((string) data_get($payload, 'candidates.0.content.parts.0.text'));
        }

        $payload = $this->openAiRequest($key, config('services.ai.base_url'), $model, array_merge([
            ['role' => 'system', 'content' => $system],
        ], $messages));

        return trim((string) data_get($payload, 'choices.0.message.content'));
    }

    private function geminiRequest(string $key, string $model, array $contents, array $generationConfig = [], ?string $system = null): array
    {
        $body = ['contents' => $contents];
        if ($generationConfig) $body['generationConfig'] = $generationConfig;
        if ($system) $body['systemInstruction'] = ['parts' => [['text' => $system]]];

        $response = Http::acceptJson()->timeout(60)->post('https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent?key='.urlencode($key), $body);
        if ($response->failed()) {
            $providerMessage = $response->json('error.message');
            throw new RuntimeException('Gemini request failed (HTTP '.$response->status().'): '.($providerMessage ?: 'Check VISION_API_KEY, billing, permissions, and the selected model.'));
        }
        return $response->json();
    }

    private function openAiRequest(string $key, ?string $baseUrl, string $model, array $messages): array
    {
        $response = Http::withToken($key)->acceptJson()->timeout(60)->post(rtrim((string) $baseUrl, '/').'/chat/completions', ['model' => $model, 'messages' => $messages]);
        if ($response->failed()) throw new RuntimeException('The AI provider could not complete the request.');
        return $response->json();
    }

    private function cleanJson(string $content): string
    {
        return preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($content)) ?: trim($content);
    }
}
