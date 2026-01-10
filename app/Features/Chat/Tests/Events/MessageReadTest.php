<?php

declare(strict_types=1);

namespace App\Features\Chat\Tests\Events;

use App\Features\Chat\Events\MessageRead;
use App\Features\Chat\Models\Chat;
use App\Features\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MessageReadTest extends TestCase
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
        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
            'is_read' => true,
            'read_at' => now(),
        ]);

        $event = new MessageRead($chat);

        $this->assertInstanceOf(\Illuminate\Contracts\Broadcasting\ShouldBroadcast::class, $event);
    }

    public function test_it_broadcasts_on_private_sender_channel(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
            'is_read' => true,
            'read_at' => now(),
        ]);

        $event = new MessageRead($chat);
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(\Illuminate\Broadcasting\PrivateChannel::class, $channels[0]);
        $this->assertEquals('private-chat.user.'.$this->sender->id, $channels[0]->name);
    }

    public function test_it_has_correct_broadcast_name(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
            'is_read' => true,
            'read_at' => now(),
        ]);

        $event = new MessageRead($chat);

        $this->assertEquals('message.read', $event->broadcastAs());
    }

    public function test_it_broadcasts_with_correct_data(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
            'is_read' => true,
            'read_at' => now(),
        ]);

        $event = new MessageRead($chat);
        $data = $event->broadcastWith();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('is_read', $data);
        $this->assertArrayHasKey('read_at', $data);
        $this->assertArrayHasKey('receiver_id', $data);

        $this->assertEquals($chat->id, $data['id']);
        $this->assertTrue($data['is_read']);
        $this->assertEquals($this->receiver->id, $data['receiver_id']);
    }

    public function test_read_at_is_formatted_as_iso_string(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
            'is_read' => true,
            'read_at' => now(),
        ]);

        $event = new MessageRead($chat);
        $data = $event->broadcastWith();

        $this->assertIsString($data['read_at']);
        // ISO 8601 format check
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $data['read_at']);
    }

    public function test_broadcast_data_does_not_include_message_content(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
            'is_read' => true,
            'read_at' => now(),
        ]);

        $event = new MessageRead($chat);
        $data = $event->broadcastWith();

        $this->assertArrayNotHasKey('message', $data);
        $this->assertArrayNotHasKey('sender', $data);
    }

    public function test_it_only_broadcasts_essential_read_receipt_data(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
            'is_read' => true,
            'read_at' => now(),
        ]);

        $event = new MessageRead($chat);
        $data = $event->broadcastWith();

        // Should only have 4 keys: id, is_read, read_at, receiver_id
        $this->assertCount(4, $data);
    }
}
