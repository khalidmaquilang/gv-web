<?php

declare(strict_types=1);

namespace App\Features\Chat\Tests\Actions;

use App\Features\Chat\Actions\GetUnreadCountAction;
use App\Features\Chat\Models\Chat;
use App\Features\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetUnreadCountActionTest extends TestCase
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

    public function test_it_counts_unread_messages_for_user(): void
    {
        // Create unread messages for user
        Chat::create([
            'sender_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'Unread 1',
            'is_read' => false,
        ]);

        Chat::create([
            'sender_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'Unread 2',
            'is_read' => false,
        ]);

        $this->actingAs($this->user);

        $action = new GetUnreadCountAction;
        $count = $action->handle();

        $this->assertEquals(2, $count);
    }

    public function test_it_excludes_read_messages_from_count(): void
    {
        // Create unread message
        Chat::create([
            'sender_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'Unread',
            'is_read' => false,
        ]);

        // Create read message
        Chat::create([
            'sender_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'Read',
            'is_read' => true,
            'read_at' => now(),
        ]);

        $this->actingAs($this->user);

        $action = new GetUnreadCountAction;
        $count = $action->handle();

        $this->assertEquals(1, $count);
    }

    public function test_it_excludes_messages_sent_by_user(): void
    {
        // Create unread message sent by user
        Chat::create([
            'sender_id' => $this->user->id,
            'receiver_id' => $this->otherUser->id,
            'message' => 'Sent message',
            'is_read' => false,
        ]);

        // Create unread message received by user
        Chat::create([
            'sender_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'Received message',
            'is_read' => false,
        ]);

        $this->actingAs($this->user);

        $action = new GetUnreadCountAction;
        $count = $action->handle();

        $this->assertEquals(1, $count);
    }

    public function test_it_returns_zero_when_no_unread_messages(): void
    {
        $this->actingAs($this->user);

        $action = new GetUnreadCountAction;
        $count = $action->handle();

        $this->assertEquals(0, $count);
    }

    public function test_it_requires_authentication(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $action = new GetUnreadCountAction;
        $action->handle();
    }

    public function test_it_counts_unread_messages_from_multiple_senders(): void
    {
        $thirdUser = User::factory()->create();

        // Create unread messages from different senders
        Chat::create([
            'sender_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'From other user',
            'is_read' => false,
        ]);

        Chat::create([
            'sender_id' => $thirdUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'From third user',
            'is_read' => false,
        ]);

        $this->actingAs($this->user);

        $action = new GetUnreadCountAction;
        $count = $action->handle();

        $this->assertEquals(2, $count);
    }

    public function test_it_handles_large_number_of_unread_messages(): void
    {
        // Create 100 unread messages
        for ($i = 1; $i <= 100; $i++) {
            Chat::create([
                'sender_id' => $this->otherUser->id,
                'receiver_id' => $this->user->id,
                'message' => 'Message '.$i,
                'is_read' => false,
            ]);
        }

        $this->actingAs($this->user);

        $action = new GetUnreadCountAction;
        $count = $action->handle();

        $this->assertEquals(100, $count);
    }
}
