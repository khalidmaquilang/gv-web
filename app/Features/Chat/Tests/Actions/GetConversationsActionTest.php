<?php

declare(strict_types=1);

namespace App\Features\Chat\Tests\Actions;

use App\Features\Chat\Actions\GetConversationsAction;
use App\Features\Chat\Models\Chat;
use App\Features\Chat\Models\Conversation;
use App\Features\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetConversationsActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_cursor_paginated_conversations_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create conversations for user
        for ($i = 0; $i < 3; $i++) {
            $conversation = Conversation::create(['type' => 'direct']);
            $conversation->users()->attach($user->id, ['joined_at' => now()]);
        }

        // Create conversation for another user
        $otherConversation = Conversation::create(['type' => 'direct']);
        $otherConversation->users()->attach(User::factory()->create()->id, ['joined_at' => now()]);

        $action = new GetConversationsAction;
        $result = $action->handle();

        $this->assertCount(3, $result->items());
        $this->assertNotNull($result->path());
    }

    /** @test */
    public function it_orders_conversations_by_updated_at_desc(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $old = Conversation::create(['type' => 'direct', 'updated_at' => now()->subDays(2)]);
        $old->users()->attach($user->id, ['joined_at' => now()]);

        $recent = Conversation::create(['type' => 'direct', 'updated_at' => now()]);
        $recent->users()->attach($user->id, ['joined_at' => now()]);

        $middle = Conversation::create(['type' => 'direct', 'updated_at' => now()->subDay()]);
        $middle->users()->attach($user->id, ['joined_at' => now()]);

        $action = new GetConversationsAction;
        $result = $action->handle();

        $ids = collect($result->items())->pluck('id')->toArray();

        $this->assertEquals($recent->id, $ids[0]);
        $this->assertEquals($middle->id, $ids[1]);
        $this->assertEquals($old->id, $ids[2]);
    }

    /** @test */
    public function it_eager_loads_latest_message_and_users(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);

        $conversation = Conversation::create(['type' => 'direct']);
        $conversation->users()->attach([
            $user->id => ['joined_at' => now()],
            $otherUser->id => ['joined_at' => now()],
        ]);

        Chat::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'receiver_id' => $otherUser->id,
            'message' => 'Latest message',
        ]);

        $action = new GetConversationsAction;
        $result = $action->handle();

        $conv = $result->items()[0];

        $this->assertNotNull($conv->latestMessage);
        $this->assertEquals('Latest message', $conv->latestMessage->message);
        $this->assertCount(2, $conv->users);
    }

    /** @test */
    public function it_respects_per_page_parameter(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create 25 conversations
        for ($i = 0; $i < 25; $i++) {
            $conversation = Conversation::create(['type' => 'direct']);
            $conversation->users()->attach($user->id, ['joined_at' => now()]);
        }

        $action = new GetConversationsAction;
        $result = $action->handle();

        $this->assertCount(10, $result->items());
    }

    /** @test */
    public function it_handles_cursor_pagination(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create conversations
        for ($i = 0; $i < 5; $i++) {
            $conversation = Conversation::create(['type' => 'direct']);
            $conversation->users()->attach($user->id, ['joined_at' => now()]);
        }

        $action = new GetConversationsAction;
        $firstPage = $action->handle();

        $this->assertCount(3, $firstPage->items());

        // Get next page using cursor
        if ($firstPage->hasMorePages()) {
            $cursor = $firstPage->nextCursor()?->encode();
            $secondPage = $action->handle();

            $this->assertCount(2, $secondPage->items());

            // Ensure no overlap
            $firstIds = collect($firstPage->items())->pluck('id');
            $secondIds = collect($secondPage->items())->pluck('id');

            $this->assertTrue($firstIds->intersect($secondIds)->isEmpty());
        }
    }

    /** @test */
    public function it_requires_authentication(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $action = new GetConversationsAction;
        $action->handle();
    }
}
