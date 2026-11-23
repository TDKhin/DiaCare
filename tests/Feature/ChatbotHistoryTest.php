<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_load_only_their_chat_history(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        ChatMessage::create(['user_id' => $user->id, 'role' => 'user', 'content' => 'How do I track glucose?']);
        ChatMessage::create(['user_id' => $user->id, 'role' => 'assistant', 'content' => 'Log the time, reading, and context.']);
        ChatMessage::create(['user_id' => $otherUser->id, 'role' => 'user', 'content' => 'This message belongs to another user.']);

        $response = $this->actingAs($user)->getJson(route('assistant.history'));

        $response->assertOk()
            ->assertJsonCount(2, 'messages')
            ->assertJsonPath('messages.0.content', 'How do I track glucose?')
            ->assertJsonPath('messages.1.content', 'Log the time, reading, and context.');
    }

    public function test_an_authenticated_user_can_clear_only_their_chat_history(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        ChatMessage::create(['user_id' => $user->id, 'role' => 'user', 'content' => 'Delete this message.']);
        ChatMessage::create(['user_id' => $otherUser->id, 'role' => 'user', 'content' => 'Keep this message.']);

        $this->actingAs($user)
            ->deleteJson(route('assistant.clear'))
            ->assertOk()
            ->assertJsonPath('status', 'cleared');

        $this->assertDatabaseMissing('chat_messages', ['user_id' => $user->id]);
        $this->assertDatabaseHas('chat_messages', ['user_id' => $otherUser->id, 'content' => 'Keep this message.']);
    }
}
