<?php

declare(strict_types=1);

namespace App\Features\Chat\Tests\Actions;

use App\Features\Chat\Actions\GetChatsAction;
use App\Features\Chat\Models\Chat;
use App\Features\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetChatsActionTest extends TestCase
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

    public function test_it_retrieves_chats_between_two_users(): void
    {
        // Create chats between users
        $chat1 = Chat::create([
            'sender_id' => $this->user->id,
            'receiver_id' => $this->otherUser->id,
            'message' => 'Hello from user',
        ]);

        $chat2 = Chat::create([
            'sender_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'Hello back',
        ]);

        // Create chat with third user (should be excluded)
        $thirdUser = User::factory()->create();
        Chat::create([
            'sender_id' => $this->user->id,
            'receiver_id' => $thirdUser->id,
            'message' => 'Not included',
        ]);

        $this->actingAs($this->user);

        $action = new GetChatsAction;
        $result = $action->handle($this->otherUser->id);

        $this->assertCount(2, $result->items());
        $chatIds = collect($result->items())->pluck('id')->toArray();
        $this->assertContains($chat1->id, $chatIds);
        $this->assertContains($chat2->id, $chatIds);
    }

    public function test_it_eager_loads_sender_and_receiver_relationships(): void
    {
        Chat::create([
            'sender_id' => $this->user->id,
            'receiver_id' => $this->otherUser->id,
            'message' => 'Test message',
        ]);

        $this->actingAs($this->user);

        $action = new GetChatsAction;
        $result = $action->handle($this->otherUser->id);

        $chatItem = $result->items()[0];

        // Check relationships are loaded
        $this->assertTrue($chatItem->relationLoaded('sender'));
        $this->assertTrue($chatItem->relationLoaded('receiver'));
        $this->assertInstanceOf(User::class, $chatItem->sender);
        $this->assertInstanceOf(User::class, $chatItem->receiver);
    }

    public function test_it_returns_results_in_descending_order_by_created_at(): void
    {
        // Create multiple chats with different timestamps
        $chat1 = Chat::create([
            'sender_id' => $this->user->id,
            'receiver_id' => $this->otherUser->id,
            'message' => 'Old message',
        ]);
        $chat1->created_at = now()->subHours(2);
        $chat1->save();

        Chat::create([
            'sender_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'New message',
        ]);

        $this->actingAs($this->user);

        $action = new GetChatsAction;
        $result = $action->handle($this->otherUser->id);

        $this->assertCount(2, $result->items());
        $this->assertEquals('New message', $result->items()[0]->message);
        $this->assertEquals('Old message', $result->items()[1]->message);
    }

    public function test_it_paginates_results_with_cursor(): void
    {
        // Create 25 chats (more than default 20 per page)
        for ($i = 1; $i <= 25; $i++) {
            Chat::create([
                'sender_id' => $this->user->id,
                'receiver_id' => $this->otherUser->id,
                'message' => 'Message '.$i,
            ]);
        }

        $this->actingAs($this->user);

        $action = new GetChatsAction;
        $result = $action->handle($this->otherUser->id);

        $this->assertCount(20, $result->items());
        $this->assertTrue($result->hasMorePages());
    }

    public function test_it_returns_empty_result_when_no_chats_available(): void
    {
        $this->actingAs($this->user);

        $action = new GetChatsAction;
        $result = $action->handle($this->otherUser->id);

        $this->assertCount(0, $result->items());
        $this->assertFalse($result->hasMorePages());
    }

    public function test_it_requires_authentication(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $action = new GetChatsAction;
        $action->handle($this->otherUser->id);
    }

    public function test_it_retrieves_chats_with_correct_read_status(): void
    {
        Chat::create([
            'sender_id' => $this->user->id,
            'receiver_id' => $this->otherUser->id,
            'message' => 'Unread message',
            'is_read' => false,
        ]);

        Chat::create([
            'sender_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'Read message',
            'is_read' => true,
            'read_at' => now(),
        ]);

        $this->actingAs($this->user);

        $action = new GetChatsAction;
        $result = $action->handle($this->otherUser->id);

        $this->assertCount(2, $result->items());

        $readChat = collect($result->items())->firstWhere('message', 'Read message');
        $unreadChat = collect($result->items())->firstWhere('message', 'Unread message');

        $this->assertTrue($readChat->is_read);
        $this->assertNotNull($readChat->read_at);
        $this->assertFalse($unreadChat->is_read);
        $this->assertNull($unreadChat->read_at);
    }
}
