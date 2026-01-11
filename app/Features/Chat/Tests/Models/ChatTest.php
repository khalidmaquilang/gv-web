<?php

declare(strict_types=1);

namespace App\Features\Chat\Tests\Models;

use App\Features\Chat\Models\Chat;
use App\Features\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ChatTest extends TestCase
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

    public function test_it_has_sender_relationship(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
        ]);

        $this->assertInstanceOf(User::class, $chat->sender);
        $this->assertEquals($this->sender->id, $chat->sender->id);
    }

    public function test_it_has_receiver_relationship(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
        ]);

        $this->assertInstanceOf(User::class, $chat->receiver);
        $this->assertEquals($this->receiver->id, $chat->receiver->id);
    }

    public function test_it_uses_uuid_for_primary_key(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
        ]);

        $this->assertIsString($chat->id);
        $this->assertTrue(strlen($chat->id) === 36); // UUID length
    }

    public function test_it_casts_is_read_to_boolean(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
            'is_read' => false,
        ]);

        $this->assertIsBool($chat->is_read);
        $this->assertFalse($chat->is_read);
    }

    public function test_it_casts_read_at_to_datetime(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
            'is_read' => true,
            'read_at' => now(),
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $chat->read_at);
    }

    public function test_mark_as_read_method_updates_status(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
            'is_read' => false,
        ]);

        $this->assertFalse($chat->is_read);
        $this->assertNull($chat->read_at);

        $chat->markAsRead();
        $chat->refresh();

        $this->assertTrue($chat->is_read);
        $this->assertNotNull($chat->read_at);
    }

    public function test_mark_as_read_method_is_idempotent(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
            'is_read' => false,
        ]);

        $chat->markAsRead();
        $chat->refresh();

        $firstReadAt = $chat->read_at;

        // Mark as read again
        $chat->markAsRead();
        $chat->refresh();

        // read_at should not change
        $this->assertEquals($firstReadAt->timestamp, $chat->read_at->timestamp);
    }

    public function test_it_has_timestamps(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
        ]);

        $this->assertNotNull($chat->created_at);
        $this->assertNotNull($chat->updated_at);
    }

    public function test_it_allows_mass_assignment_of_fillable_attributes(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => 'Test message',
            'is_read' => true,
            'read_at' => now(),
        ]);

        $this->assertEquals($this->sender->id, $chat->sender_id);
        $this->assertEquals($this->receiver->id, $chat->receiver_id);
        $this->assertEquals('Test message', $chat->message);
        $this->assertTrue($chat->is_read);
        $this->assertNotNull($chat->read_at);
    }

    public function test_it_stores_long_messages(): void
    {
        $longMessage = str_repeat('This is a test message. ', 500);

        $chat = Chat::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'message' => $longMessage,
        ]);

        $this->assertEquals($longMessage, $chat->message);
    }
}
