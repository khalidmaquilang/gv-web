<?php

declare(strict_types=1);

namespace App\Features\Chat\Tests\Actions;

use App\Features\Chat\Actions\GetUnreadCountAction;
use App\Features\Chat\Models\Chat;
use App\Features\Chat\Models\Conversation;
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
        $this->actingAs($this->user);

        // Create conversation
        $conversation = Conversation::create(['type' => 'direct']);
        $conversation->users()->attach([
            $this->user->id => [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'joined_at' => now(),
            ],
            $this->otherUser->id => [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'joined_at' => now(),
            ],
        ]);

        // Create 2 unread messages
        Chat::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'Message 1',
        ]);

        Chat::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'message' => 'Message 2',
        ]);

        $action = new GetUnreadCountAction;
        $count = $action->handle();

        $this->assertEquals(2, $count);
    }

    public function test_it_requires_authentication(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $action = new GetUnreadCountAction;
        $action->handle();
    }
}
