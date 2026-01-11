<?php

declare(strict_types=1);

namespace App\Features\Chat\Tests\Models;

use App\Features\Chat\Models\Chat;
use App\Features\Chat\Models\Conversation;
use App\Features\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ConversationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_direct_conversation(): void
    {
        $conversation = Conversation::create(['type' => 'direct']);

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'type' => 'direct',
        ]);
    }

    /** @test */
    public function it_can_create_a_group_conversation(): void
    {
        $conversation = Conversation::create([
            'type' => 'group',
            'name' => 'Test Group',
        ]);

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'type' => 'group',
            'name' => 'Test Group',
        ]);
    }

    /** @test */
    public function it_has_many_to_many_relationship_with_users(): void
    {
        $conversation = Conversation::create(['type' => 'direct']);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation->users()->attach([
            $user1->id => ['joined_at' => now()],
            $user2->id => ['joined_at' => now()],
        ]);

        $this->assertCount(2, $conversation->users);
        $this->assertTrue($conversation->users->contains($user1));
        $this->assertTrue($conversation->users->contains($user2));
    }

    /** @test */
    public function it_stores_pivot_data_for_users(): void
    {
        $conversation = Conversation::create(['type' => 'direct']);
        $user = User::factory()->create();

        $conversation->users()->attach($user->id, [
            'joined_at' => now(),
            'is_muted' => true,
            'is_archived' => false,
            'last_read_at' => now()->subHours(2),
        ]);

        $pivotData = $conversation->users()->first()?->pivot;

        $this->assertNotNull($pivotData);
        $this->assertTrue($pivotData->is_muted);
        $this->assertFalse($pivotData->is_archived);
        $this->assertNotNull($pivotData->last_read_at);
    }

    /** @test */
    public function it_has_messages_relationship(): void
    {
        $conversation = Conversation::create(['type' => 'direct']);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Chat::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user1->id,
            'receiver_id' => $user2->id,
        ]);

        Chat::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user2->id,
            'receiver_id' => $user1->id,
        ]);

        $this->assertCount(2, $conversation->messages);
    }

    /** @test */
    public function it_has_latest_message_relationship(): void
    {
        $conversation = Conversation::create(['type' => 'direct']);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Chat::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user1->id,
            'receiver_id' => $user2->id,
            'message' => 'First message',
            'created_at' => now()->subHours(2),
        ]);

        $latestChat = Chat::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user2->id,
            'receiver_id' => $user1->id,
            'message' => 'Latest message',
            'created_at' => now(),
        ]);

        $this->assertEquals('Latest message', $conversation->latestMessage->message);
        $this->assertEquals($latestChat->id, $conversation->latestMessage->id);
    }

    /** @test */
    public function it_filters_direct_conversations(): void
    {
        Conversation::create(['type' => 'direct']);
        Conversation::create(['type' => 'direct']);
        Conversation::create(['type' => 'group', 'name' => 'Group']);

        $directConversations = Conversation::direct()->get();

        $this->assertCount(2, $directConversations);
    }

    /** @test */
    public function it_filters_group_conversations(): void
    {
        Conversation::create(['type' => 'direct']);
        Conversation::create(['type' => 'group', 'name' => 'Group 1']);
        Conversation::create(['type' => 'group', 'name' => 'Group 2']);

        $groupConversations = Conversation::group()->get();

        $this->assertCount(2, $groupConversations);
    }

    /** @test */
    public function it_filters_conversations_for_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $userConversation = Conversation::create(['type' => 'direct']);
        $userConversation->users()->attach($user->id, ['joined_at' => now()]);

        $otherConversation = Conversation::create(['type' => 'direct']);
        $otherConversation->users()->attach($otherUser->id, ['joined_at' => now()]);

        $userConversations = Conversation::forUser($user->id)->get();

        $this->assertCount(1, $userConversations);
        $this->assertEquals($userConversation->id, $userConversations->first()->id);
    }

    /** @test */
    public function it_finds_existing_direct_conversation_between_two_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create existing conversation
        $existing = Conversation::create(['type' => 'direct']);
        $existing->users()->attach([
            $user1->id => ['joined_at' => now()],
            $user2->id => ['joined_at' => now()],
        ]);

        $found = Conversation::findOrCreateDirectConversation($user1->id, $user2->id);

        $this->assertEquals($existing->id, $found->id);
        $this->assertCount(1, Conversation::all()); // Should not create new one
    }

    /** @test */
    public function it_creates_new_direct_conversation_if_none_exists(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->assertCount(0, Conversation::all());

        $conversation = Conversation::findOrCreateDirectConversation($user1->id, $user2->id);

        $this->assertCount(1, Conversation::all());
        $this->assertEquals('direct', $conversation->type);
        $this->assertCount(2, $conversation->users);
        $this->assertTrue($conversation->users->contains($user1));
        $this->assertTrue($conversation->users->contains($user2));
    }

    /** @test */
    public function it_finds_conversation_regardless_of_user_order(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation1 = Conversation::findOrCreateDirectConversation($user1->id, $user2->id);
        $conversation2 = Conversation::findOrCreateDirectConversation($user2->id, $user1->id);

        $this->assertEquals($conversation1->id, $conversation2->id);
        $this->assertCount(1, Conversation::all());
    }

    /** @test */
    public function it_soft_deletes_conversations(): void
    {
        $conversation = Conversation::create(['type' => 'direct']);

        $conversation->delete();

        $this->assertSoftDeleted('conversations', ['id' => $conversation->id]);
        $this->assertCount(0, Conversation::all());
        $this->assertCount(1, Conversation::withTrashed()->get());
    }
}
