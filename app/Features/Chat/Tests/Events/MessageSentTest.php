<?php

declare(strict_types=1);

namespace App\Features\Chat\Tests\Events;

use App\Features\Chat\Events\MessageSent;
use App\Features\Chat\Models\Chat;
use App\Features\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MessageSentTest extends TestCase
{
    use RefreshDatabase;

    protected User $sender;

    protected User $receiver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sender = User::factory()->create();
        $this->receiver = User::factory()->create();
    }

    public function test_it_implements_should_broadcast_interface(): void
    {
        $chat = Chat::create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
        ]);

        $event = new MessageSent($chat);

        $this->assertInstanceOf(\Illuminate\Contracts\Broadcasting\ShouldBroadcast::class, $event);
    }

    public function test_it_broadcasts_on_private_receiver_channel(): void
    {
        $chat = Chat::create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
        ]);

        $event = new MessageSent($chat);
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(\Illuminate\Broadcasting\PrivateChannel::class, $channels[0]);
        $this->assertEquals('private-chat.user.'.$this->receiver->id, $channels[0]->name);
    }

    public function test_it_has_correct_broadcast_name(): void
    {
        $chat = Chat::create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
        ]);

        $event = new MessageSent($chat);

        $this->assertEquals('message.sent', $event->broadcastAs());
    }

    public function test_it_broadcasts_with_correct_data(): void
    {
        $conversation = \App\Features\Chat\Models\Conversation::create(['type' => 'direct']);

        $chat = Chat::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
            'is_read' => false,
        ]);

        $chat->load(['sender', 'receiver']);

        $event = new MessageSent($chat);
        $data = $event->broadcastWith();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('sender_id', $data);
        $this->assertArrayHasKey('receiver_id', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('is_read', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('sender', $data);

        $this->assertEquals($chat->id, $data['id']);
        $this->assertEquals($this->sender->id, $data['sender_id']);
        $this->assertEquals($this->receiver->id, $data['receiver_id']);
        $this->assertEquals('Test message', $data['message']);
        $this->assertFalse($data['is_read']);
    }

    public function test_broadcast_data_includes_sender_information(): void
    {
        $chat = Chat::create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
        ]);

        $chat->load(['sender', 'receiver']);

        $event = new MessageSent($chat);
        $data = $event->broadcastWith();

        $this->assertArrayHasKey('sender', $data);
        $this->assertArrayHasKey('id', $data['sender']);
        $this->assertArrayHasKey('username', $data['sender']);
        $this->assertArrayHasKey('name', $data['sender']);

        $this->assertEquals($this->sender->id, $data['sender']['id']);
        $this->assertEquals($this->sender->username, $data['sender']['username']);
        $this->assertEquals($this->sender->name, $data['sender']['name']);
    }

    public function test_created_at_is_formatted_as_iso_string(): void
    {
        $chat = Chat::create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
        ]);

        $chat->load(['sender', 'receiver']);

        $event = new MessageSent($chat);
        $data = $event->broadcastWith();

        $this->assertIsString($data['created_at']);
        // ISO 8601 format check
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $data['created_at']);
    }
}
