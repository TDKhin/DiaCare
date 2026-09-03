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
        return response()->json([
            'messages' => $request->user()->chatMessages()->latest()->limit(30)->get()->reverse()->values(),
        ]);
    }

    public function send(ChatMessageRequest $request, AiHealthService $ai): JsonResponse
    {
        $user = $request->user();
        $user->chatMessages()->create(['role' => 'user', 'content' => $request->string('message')->toString()]);
        $history = $user->chatMessages()->latest()->limit(12)->get()->reverse()->map(fn ($message) => [
            'role' => $message->role,
            'content' => $message->content,
        ])->values()->all();

        try {
            $reply = $ai->chat($history);
            $assistant = $user->chatMessages()->create(['role' => 'assistant', 'content' => $reply]);

            return response()->json(['message' => $assistant]);
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function clear(Request $request): JsonResponse
    {
        $request->user()->chatMessages()->delete();

        return response()->json(['status' => 'cleared']);
    }
}
