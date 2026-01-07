<?php

declare(strict_types=1);

namespace App\Features\Live\Tests\Actions;

use App\Features\Feed\Enums\FeedPrivacyEnum;
use App\Features\Feed\Enums\FeedStatusEnum;
use App\Features\Feed\Models\Feed;
use App\Features\Live\Actions\StartLiveAction;
use App\Features\Live\Models\Live;
use App\Features\User\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StartLiveActionTest extends TestCase
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

    public function test_it_starts_a_live_stream_successfully(): void
    {
        // Create a live stream without started_at
        $live = Live::create(['stream_key' => 'test-key']);
        $feed = new Feed([
            'user_id' => $this->user->id,
            'title' => 'Test Stream',
            'allow_comments' => true,
            'privacy' => FeedPrivacyEnum::PublicView,
            'status' => FeedStatusEnum::Approved,
        ]);
        $feed->content()->associate($live);
        $feed->saveQuietly();

        $this->actingAs($this->user);

        // Verify it doesn't have started_at
        $this->assertNull($live->started_at);

        $action = new StartLiveAction;
        $action->handle($live->id);

        $live->refresh();

        $this->assertNotNull($live->started_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $live->started_at);
    }

    public function test_it_prevents_starting_live_stream_of_another_user(): void
    {
        $this->expectException(ModelNotFoundException::class);

        // Create a live stream owned by other user
        $live = Live::create(['stream_key' => 'test-key']);
        $feed = new Feed([
            'user_id' => $this->otherUser->id,
            'title' => 'Other User Stream',
            'allow_comments' => true,
            'privacy' => FeedPrivacyEnum::PublicView,
            'status' => FeedStatusEnum::Approved,
        ]);
        $feed->content()->associate($live);
        $feed->saveQuietly();

        $this->actingAs($this->user);

        $action = new StartLiveAction;
        $action->handle($live->id);
    }

    public function test_it_fails_when_live_stream_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->actingAs($this->user);

        $action = new StartLiveAction;
        $action->handle('non-existent-id');
    }

    public function test_it_fails_when_user_is_not_authenticated(): void
    {
        $this->expectException(\Exception::class);

        $live = Live::create(['stream_key' => 'test-key']);
        $feed = new Feed([
            'user_id' => $this->user->id,
            'title' => 'Test Stream',
            'allow_comments' => true,
            'privacy' => FeedPrivacyEnum::PublicView,
            'status' => FeedStatusEnum::Approved,
        ]);
        $feed->content()->associate($live);
        $feed->saveQuietly();

        $action = new StartLiveAction;
        $action->handle($live->id);
    }

    public function test_it_fails_when_trying_to_start_already_started_live_stream(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $live = Live::create([
            'stream_key' => 'test-key',
            'started_at' => now()->subMinutes(10),
        ]);
        $feed = new Feed([
            'user_id' => $this->user->id,
            'title' => 'Test Stream',
            'allow_comments' => true,
            'privacy' => FeedPrivacyEnum::PublicView,
            'status' => FeedStatusEnum::Approved,
        ]);
        $feed->content()->associate($live);
        $feed->saveQuietly();

        $this->actingAs($this->user);

        $action = new StartLiveAction;
        $action->handle($live->id);
    }

    public function test_it_validates_ownership_through_feed_relationship(): void
    {
        $this->expectException(ModelNotFoundException::class);

        // Create live without feed (orphaned)
        $live = Live::create(['stream_key' => 'orphaned-key']);

        $this->actingAs($this->user);

        $action = new StartLiveAction;
        $action->handle($live->id);
    }
}
