<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatMessageRequest;
use App\Services\AiHealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ChatbotController extends Controller
{
    public function history(Request $request): JsonResponse
    {
        if (! $request->user()) return response()->json(['messages' => []]);
        return response()->json(['messages' => $request->user()->chatMessages()->latest('id')->limit(30)->get()->reverse()->values()]);
    }

    public function send(ChatMessageRequest $request, AiHealthService $ai): JsonResponse
    {
        $user = $request->user();
        $content = $request->string('message')->toString();
        $history = $user
            ? $user->chatMessages()->latest('id')->limit(4)->get()->reverse()->map(fn ($message) => ['role' => $message->role, 'content' => mb_substr($message->content, 0, 1200)])->values()->all()
            : collect($request->input('history', []))->map(fn ($message) => ['role' => $message['role'], 'content' => $message['content']])->values()->all();
        if ($user) $user->chatMessages()->create(['role' => 'user', 'content' => $content]);
        $history[] = ['role' => 'user', 'content' => $content];
        try {
            $reply = $ai->chat($history);
            $assistant = $user ? $user->chatMessages()->create(['role' => 'assistant', 'content' => $reply]) : (object) ['id' => 'guest-'.now()->timestamp, 'role' => 'assistant', 'content' => $reply];
            return response()->json(['message' => $assistant]);
        } catch (Throwable $exception) {
            report($exception);
            $details = $exception->getMessage();
            $message = str_contains($details, 'not configured')
                ? 'The text assistant is not configured. Add GROQ_TEXT_API_KEY to .env and run php artisan optimize:clear.'
                : (str_contains($details, 'HTTP 401')
                    ? 'Groq rejected the text API key. Check GROQ_TEXT_API_KEY in .env.'
                    : (str_contains($details, 'HTTP 404')
                        ? 'Groq rejected the configured text model or endpoint. Check AI_TEXT_MODEL and AI_TEXT_BASE_URL.'
                        : (str_contains($details, 'HTTP 429')
                            ? 'The Groq text API rate limit was reached. Please wait and try again.'
                            : 'The assistant could not connect to the AI provider. Please try again.')));
            return response()->json(['message' => $message], 422);
        }
    }

    public function clear(Request $request): JsonResponse
    {
        if ($request->user()) $request->user()->chatMessages()->delete();
        return response()->json(['status' => 'cleared']);
    }
}
