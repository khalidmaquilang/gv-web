<?php

declare(strict_types=1);

namespace App\Features\Chat\Tests\Actions;

use App\Features\Chat\Actions\GetOrCreateConversationAction;
use App\Features\Chat\Models\Conversation;
use App\Features\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetOrCreateConversationActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_new_conversation_when_none_exists(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->assertCount(0, Conversation::all());

        $action = new GetOrCreateConversationAction;
        $conversation = $action->handle($user1->id, $user2->id);

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertCount(1, Conversation::all());
        $this->assertEquals('direct', $conversation->type);
    }

    /** @test */
    public function it_returns_existing_conversation_when_found(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create existing conversation
        $existing = Conversation::create(['type' => 'direct']);
        $existing->users()->attach([
            $user1->id => ['joined_at' => now()],
            $user2->id => ['joined_at' => now()],
        ]);

        $action = new GetOrCreateConversationAction;
        $conversation = $action->handle($user1->id, $user2->id);

        $this->assertEquals($existing->id, $conversation->id);
        $this->assertCount(1, Conversation::all());
    }

    /** @test */
    public function it_attaches_both_users_to_new_conversation(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $action = new GetOrCreateConversationAction;
        $conversation = $action->handle($user1->id, $user2->id);

        $this->assertCount(2, $conversation->users);
        $this->assertTrue($conversation->users->contains($user1));
        $this->assertTrue($conversation->users->contains($user2));
    }

    /** @test */
    public function it_sets_joined_at_timestamp_for_participants(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $action = new GetOrCreateConversationAction;
        $conversation = $action->handle($user1->id, $user2->id);

        $pivot1 = $conversation->users()->where('user_id', $user1->id)->first()?->pivot;
        $pivot2 = $conversation->users()->where('user_id', $user2->id)->first()?->pivot;

        $this->assertNotNull($pivot1->joined_at);
        $this->assertNotNull($pivot2->joined_at);
    }

    /** @test */
    public function it_works_regardless_of_user_order(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $action = new GetOrCreateConversationAction;
        $conversation1 = $action->handle($user1->id, $user2->id);
        $conversation2 = $action->handle($user2->id, $user1->id);

        $this->assertEquals($conversation1->id, $conversation2->id);
        $this->assertCount(1, Conversation::all());
    }
}
