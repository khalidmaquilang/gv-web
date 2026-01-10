<?php

declare(strict_types=1);

namespace App\Features\Chat\Tests\Actions;

use App\Features\Chat\Actions\MarkChatAsReadAction;
use App\Features\Chat\Models\Chat;
use App\Features\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MarkChatAsReadActionTest extends TestCase
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

    public function test_it_marks_chat_as_read(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'Test message',
            'is_read' => false,
        ]);

        $this->actingAs($this->user);

        $action = new MarkChatAsReadAction;
        $action->handle($chat->id);

        $chat->refresh();

        $this->assertTrue($chat->is_read);
        $this->assertNotNull($chat->read_at);
    }

    public function test_it_only_allows_receiver_to_mark_as_read(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'Test message',
            'is_read' => false,
        ]);

        // Try to mark as read by sender (should fail)
        $this->actingAs($this->otherUser);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $action = new MarkChatAsReadAction;
        $action->handle($chat->id);
    }

    public function test_it_requires_authentication(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'Test message',
            'is_read' => false,
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $action = new MarkChatAsReadAction;
        $action->handle($chat->id);
    }

    public function test_it_handles_already_read_messages(): void
    {
        $originalReadTime = now()->subHour();

        $chat = Chat::factory()->create([
            'sender_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'Test message',
            'is_read' => true,
            'read_at' => $originalReadTime,
        ]);

        $this->actingAs($this->user);

        $action = new MarkChatAsReadAction;
        $action->handle($chat->id);

        $chat->refresh();

        // Should remain read with original timestamp
        $this->assertTrue($chat->is_read);
        $this->assertEquals($originalReadTime->timestamp, $chat->read_at->timestamp);
    }

    public function test_it_throws_exception_for_nonexistent_chat(): void
    {
        $this->actingAs($this->user);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $action = new MarkChatAsReadAction;
        $action->handle('nonexistent-id');
    }

    public function test_it_sets_read_at_timestamp(): void
    {
        $chat = Chat::factory()->create([
            'sender_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'Test message',
            'is_read' => false,
            'read_at' => null,
        ]);

        $this->actingAs($this->user);

        $action = new MarkChatAsReadAction;
        $action->handle($chat->id);

        $chat->refresh();

        // Verify read_at is set to a recent timestamp
        $this->assertNotNull($chat->read_at);
        $this->assertTrue($chat->read_at->isToday());
    }
}
