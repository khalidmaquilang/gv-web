<?php

declare(strict_types=1);

namespace App\Features\Chat\Tests\Actions;

use App\Features\Chat\Actions\GetOrCreateConversationAction;
use App\Features\Chat\Actions\SendChatMessageAction;
use App\Features\Chat\Data\SendChatMessageData;
use App\Features\Chat\Models\Chat;
use App\Features\Chat\Models\Conversation;
use App\Features\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SendChatMessageActionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    public function test_it_creates_a_chat_message(): void
    {
        $this->actingAs($this->user);

        $data = new SendChatMessageData(
            receiver_id: $this->otherUser->id,
            message: 'Hello there!'
        );

        $action = new SendChatMessageAction(new GetOrCreateConversationAction);
        $chatId = $action->handle($data);

        $this->assertNotNull($chatId);
        $this->assertDatabaseHas('chats', [
            'id' => $chatId,
            'sender_id' => $this->user->id,
            'receiver_id' => $this->otherUser->id,
            'message' => 'Hello there!',
            'is_read' => false,
        ]);
    }

    public function test_it_sets_is_read_to_false_by_default(): void
    {
        $this->actingAs($this->user);

        $data = new SendChatMessageData(
            receiver_id: $this->otherUser->id,
            message: 'Test message'
        );

        $action = new SendChatMessageAction(new GetOrCreateConversationAction);
        $chatId = $action->handle($data);

        $chat = Chat::find($chatId);

        $this->assertFalse($chat->is_read);
        $this->assertNull($chat->read_at);
    }

    public function test_it_uses_authenticated_user_as_sender(): void
    {
        $this->actingAs($this->user);

        $data = new SendChatMessageData(
            receiver_id: $this->otherUser->id,
            message: 'Test message'
        );

        $action = new SendChatMessageAction(new GetOrCreateConversationAction);
        $chatId = $action->handle($data);

        $chat = Chat::find($chatId);

        $this->assertEquals($this->user->id, $chat->sender_id);
    }

    public function test_it_requires_authentication(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $data = new SendChatMessageData(
            receiver_id: $this->otherUser->id,
            message: 'Test message'
        );

        $action = new SendChatMessageAction(new GetOrCreateConversationAction);
        $action->handle($data);
    }

    public function test_it_returns_created_chat_id(): void
    {
        $this->actingAs($this->user);

        $data = new SendChatMessageData(
            receiver_id: $this->otherUser->id,
            message: 'Test message'
        );

        $action = new SendChatMessageAction(new GetOrCreateConversationAction);
        $chatId = $action->handle($data);

        $this->assertIsString($chatId);
        $this->assertTrue(Chat::where('id', $chatId)->exists());
    }

    public function test_it_creates_chat_with_long_message(): void
    {
        $this->actingAs($this->user);

        $longMessage = str_repeat('This is a very long message. ', 100);

        $data = new SendChatMessageData(
            receiver_id: $this->otherUser->id,
            message: $longMessage
        );

        $action = new SendChatMessageAction(new GetOrCreateConversationAction);
        $chatId = $action->handle($data);

        $chat = Chat::find($chatId);

        $this->assertEquals($longMessage, $chat->message);
    }

    public function test_it_creates_or_gets_conversation_before_sending_message(): void
    {
        $this->actingAs($this->user);

        $data = new SendChatMessageData(
            receiver_id: $this->otherUser->id,
            message: 'Hello!'
        );

        $this->assertCount(0, Conversation::all());

        $action = new SendChatMessageAction(new GetOrCreateConversationAction);
        $action->handle($data);

        $this->assertCount(1, Conversation::all());

        // Send another message - should use same conversation
        $data2 = new SendChatMessageData(
            receiver_id: $this->otherUser->id,
            message: 'Second message'
        );

        $action->handle($data2);

        $this->assertCount(1, Conversation::all()); // Still only 1 conversation
        $this->assertCount(2, Chat::all()); // But 2 messages
    }

    public function test_it_associates_message_with_conversation(): void
    {
        $this->actingAs($this->user);

        $data = new SendChatMessageData(
            receiver_id: $this->otherUser->id,
            message: 'Test message'
        );

        $action = new SendChatMessageAction(new GetOrCreateConversationAction);
        $chatId = $action->handle($data);

        $chat = Chat::find($chatId);

        $this->assertNotNull($chat->conversation_id);
        $this->assertInstanceOf(Conversation::class, $chat->conversation);
    }
}
