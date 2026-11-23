<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiHealthService
{
    public function analyzeMeal(UploadedFile $photo): array
    {
        $settings = $this->settings('vision');
        $key = $settings['key'];
        $model = $settings['model'];
        $provider = $settings['provider'];

        if (! $key) throw new RuntimeException('Photo analysis is not configured. Add AI_VISION_API_KEY to your .env file.');

        $prompt = 'Analyze this meal photo for general nutrition tracking. Return valid JSON only with meal_name (string), confidence (number from 0 to 1), calories (integer), carbohydrates_g (number), protein_g (number), fat_g (number), fiber_g (number), ingredients (array of strings), and advice (string). Identify visible foods even if the photo is imperfect; provide a conservative estimate rather than refusing when some ingredients or portions are uncertain. Use confidence below 0.5 when visibility is limited and explain the uncertainty in advice. Say that this is not medical advice, and do not diagnose or recommend medication changes.';
        $mime = $photo->getMimeType() ?: 'image/jpeg';
        $contents = [[
            'role' => 'user',
            'parts' => [
                ['text' => $prompt],
                ['inline_data' => ['mime_type' => $mime, 'data' => base64_encode((string) file_get_contents($photo->getRealPath()))]],
            ],
        ]];

        $payload = $provider === 'gemini'
            ? $this->geminiRequest($key, $model, $contents, ['responseMimeType' => 'application/json', 'temperature' => 0.2, 'maxOutputTokens' => 450])
            : $this->openAiRequest($key, $settings['base_url'], $model, [[
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => $prompt],
                    ['type' => 'image_url', 'image_url' => ['url' => 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($photo->getRealPath())), 'detail' => 'low']],
                ],
            ]]);

        $content = $provider === 'gemini'
            ? data_get($payload, 'candidates.0.content.parts.0.text')
            : data_get($payload, 'choices.0.message.content');
        return $this->normalizeMealAnalysis((string) $content);
    }

    private function normalizeMealAnalysis(string $content): array
    {
        $clean = $this->cleanJson($content);
        $analysis = json_decode($clean, true);
        if (! is_array($analysis) && preg_match('/\{.*\}/s', $clean, $match)) $analysis = json_decode($match[0], true);
        if (! is_array($analysis)) throw new RuntimeException('The meal photo could not be read clearly. Please try a well-lit photo showing the whole meal.');
        $analysis['meal_name'] = (string) ($analysis['meal_name'] ?? 'Meal estimate');
        $analysis['confidence'] = is_numeric($analysis['confidence'] ?? null) ? (float) $analysis['confidence'] : 0.5;
        foreach (['calories', 'carbohydrates_g', 'protein_g', 'fat_g', 'fiber_g'] as $field) $analysis[$field] = is_numeric($analysis[$field] ?? null) ? (float) $analysis[$field] : 0;
        $analysis['ingredients'] = is_array($analysis['ingredients'] ?? null) ? $analysis['ingredients'] : [];
        $analysis['advice'] = (string) ($analysis['advice'] ?? 'Nutrition values are estimates based on the visible portion.');
        return $analysis;
    }

    public function analyzeGlucosePhoto(UploadedFile $photo): array
    {
        $settings = $this->settings('vision');
        $key = $settings['key'];
        $model = $settings['model'];
        $provider = $settings['provider'];

        if (! $key) throw new RuntimeException('Photo analysis is not configured. Add AI_VISION_API_KEY to your .env file.');

        $prompt = 'Read the digital glucose meter or continuous glucose monitor display in this image. Return JSON only with value (integer in mg/dL), unit (mg/dL or mmol/L), confidence (number from 0 to 1), visible_text (string), and notes (array of strings). If no glucose number is clearly visible, return value as null and explain why in notes. Do not infer a value from context, do not diagnose, do not give medication advice, and remind the user to verify the reading on the device. If the visible unit is mmol/L, preserve it and do not convert unless the image clearly provides a conversion.';
        $mime = $photo->getMimeType() ?: 'image/jpeg';
        $contents = [[
            'role' => 'user',
            'parts' => [
                ['text' => $prompt],
                ['inline_data' => ['mime_type' => $mime, 'data' => base64_encode((string) file_get_contents($photo->getRealPath()))]],
            ],
        ]];

        $payload = $provider === 'gemini'
            ? $this->geminiRequest($key, $model, $contents, ['responseMimeType' => 'application/json', 'temperature' => 0.1, 'maxOutputTokens' => 220])
            : $this->openAiRequest($key, $settings['base_url'], $model, [[
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => $prompt],
                    ['type' => 'image_url', 'image_url' => ['url' => 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($photo->getRealPath())), 'detail' => 'high']],
                ],
            ]]);

        $content = $provider === 'gemini'
            ? data_get($payload, 'candidates.0.content.parts.0.text')
            : data_get($payload, 'choices.0.message.content');
        $analysis = json_decode($this->cleanJson((string) $content), true);

        if (! is_array($analysis) || ! array_key_exists('value', $analysis)) {
            throw new RuntimeException('The AI provider returned an invalid glucose photo analysis.');
        }

        if ($analysis['value'] !== null && (! is_numeric($analysis['value']) || (int) $analysis['value'] < 20 || (int) $analysis['value'] > 600)) {
            throw new RuntimeException('The glucose value in the photo was unclear or outside the supported range.');
        }

        $analysis['value'] = $analysis['value'] === null ? null : (int) $analysis['value'];
        $analysis['unit'] = $analysis['unit'] ?? 'mg/dL';
        $analysis['confidence'] = isset($analysis['confidence']) ? (float) $analysis['confidence'] : null;

        return $analysis;
    }

    public function chat(array $messages): string
    {
        $settings = $this->settings('text');
        $key = $settings['key'];
        $model = $settings['model'];
        $provider = $settings['provider'];

        if (! $key) throw new RuntimeException('Text assistant is not configured. Add AI_TEXT_API_KEY to your .env file.');

        $system = 'You are DiaCare Assistant, a careful and helpful diabetes wellness assistant. Respond in the same language as the user (if they write Burmese, answer in clear natural Burmese; if they write English, answer in plain English). Answer the exact question directly and completely in simple, natural language. Explain the main idea briefly when it helps understanding, but do not add unsolicited next steps, action plans, or generic safety notes. Use short paragraphs or simple bullet points only when they make the answer easier to read. Do not stop mid-sentence or omit the conclusion. Do not invent readings, diagnoses, medication schedules, lab values, or facts that are not in the conversation. If information is missing, say what is unknown and ask at most one useful follow-up question. Explain technical terms in everyday language. You may explain glucose logging, food and carbohydrate awareness, activity, medication reminders, appointments, and questions for a clinician. Never diagnose, prescribe, recommend starting/stopping/changing medication, or calculate personalized insulin doses. Do not treat a photo-derived glucose value as definitive. Only mention urgent medical help when the user describes serious or emergency symptoms. Clearly separate general education from personal medical advice when that distinction matters.';

        if ($provider === 'gemini') {
            $contents = array_map(fn (array $message) => [
                'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $message['content']]],
            ], $messages);
            $payload = $this->geminiRequest($key, $model, $contents, ['temperature' => 0.2, 'maxOutputTokens' => 1400], $system);
            return trim((string) data_get($payload, 'candidates.0.content.parts.0.text'));
        }

        $payload = $this->openAiRequest($key, $settings['base_url'], $model, array_merge([
            ['role' => 'system', 'content' => $system],
        ], $messages));

        return trim((string) data_get($payload, 'choices.0.message.content'));
    }

    private function geminiRequest(string $key, string $model, array $contents, array $generationConfig = [], ?string $system = null): array
    {
        $body = ['contents' => $contents];
        if ($generationConfig) $body['generationConfig'] = $generationConfig;
        if ($system) $body['systemInstruction'] = ['parts' => [['text' => $system]]];

        $response = Http::acceptJson()->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])->connectTimeout(6)->timeout(30)->retry(2, 700, throw: false)->post('https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent?key='.urlencode($key), $body);
        if ($response->failed()) {
            $providerMessage = $response->json('error.message');
            throw new RuntimeException('Gemini could not respond. Check the API key, selected model, internet connection, and Google AI API access.'.($providerMessage ? ' Provider: '.$providerMessage : ''));
        }
        return $response->json();
    }

    private function openAiRequest(string $key, ?string $baseUrl, string $model, array $messages): array
    {
        $payload = ['model' => $model, 'messages' => $messages, 'max_tokens' => 700, 'temperature' => 0.7];
        if (str_starts_with($model, 'qwen/')) {
            $payload['reasoning_effort'] = 'none';
            $payload['reasoning_format'] = 'hidden';
            if (collect($messages)->contains(fn (array $message) => is_array($message['content'] ?? null))) {
                $payload['response_format'] = ['type' => 'json_object'];
            }
        }
        $response = Http::withToken($key)->acceptJson()->connectTimeout(8)->timeout(50)->post(rtrim((string) $baseUrl, '/').'/chat/completions', $payload);
        if ($response->failed()) {
            $providerMessage = $response->json('error.message');
            $retryAfter = $response->header('retry-after');
            $retryMessage = $retryAfter ? ' Retry after '.$retryAfter.' seconds.' : '';
            throw new RuntimeException('The AI provider could not complete the request (HTTP '.$response->status().').'.($providerMessage ? ' Provider: '.$providerMessage : '').$retryMessage);
        }
        return $response->json();
    }

    private function settings(string $type): array
    {
        $settings = config('services.ai.'.$type, []);
        return [
            'provider' => $settings['provider'] ?? 'gemini',
            'base_url' => $settings['base_url'] ?? 'https://api.openai.com/v1',
            'key' => $settings['key'] ?? null,
            'model' => $settings['model'] ?? 'gemini-2.5-flash',
        ];
    }

    private function cleanJson(string $content): string
    {
        return preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($content)) ?: trim($content);
    }
}
