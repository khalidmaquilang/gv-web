<?php

declare(strict_types=1);

namespace App\Features\Live\Tests\Actions;

use App\Features\Feed\Enums\FeedPrivacyEnum;
use App\Features\Feed\Enums\FeedStatusEnum;
use App\Features\Feed\Models\Feed;
use App\Features\Live\Actions\EndLiveAction;
use App\Features\Live\Models\Live;
use App\Features\User\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EndLiveActionTest extends TestCase
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

    public function test_it_ends_a_live_stream_successfully(): void
    {
        // Create a live stream with started_at
        $live = Live::create([
            'stream_key' => 'test-key',
            'started_at' => now()->subMinutes(5),
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

        $action = new EndLiveAction;
        $action->handle($live->id);

        $live->refresh();

        $this->assertNotNull($live->ended_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $live->ended_at);
    }

    public function test_it_prevents_ending_live_stream_of_another_user(): void
    {
        $this->expectException(ModelNotFoundException::class);

        // Create a live stream owned by other user
        $live = Live::create([
            'stream_key' => 'test-key',
            'started_at' => now()->subMinutes(5),
        ]);
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

        $action = new EndLiveAction;
        $action->handle($live->id);
    }

    public function test_it_fails_when_live_stream_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->actingAs($this->user);

        $action = new EndLiveAction;
        $action->handle('non-existent-id');
    }

    public function test_it_fails_when_user_is_not_authenticated(): void
    {
        $this->expectException(\Exception::class);

        $live = Live::create([
            'stream_key' => 'test-key',
            'started_at' => now()->subMinutes(5),
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

        $action = new EndLiveAction;
        $action->handle($live->id);
    }

    public function test_it_fails_when_trying_to_end_already_ended_live_stream(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $live = Live::create([
            'stream_key' => 'test-key',
            'started_at' => now()->subHour(),
            'ended_at' => now()->subMinutes(30),
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

        $action = new EndLiveAction;
        $action->handle($live->id);
    }

    public function test_it_validates_ownership_through_feed_relationship(): void
    {
        $this->expectException(ModelNotFoundException::class);

        // Create live without feed (orphaned)
        $live = Live::create([
            'stream_key' => 'orphaned-key',
            'started_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($this->user);

        $action = new EndLiveAction;
        $action->handle($live->id);
    }

    public function test_it_fails_when_trying_to_end_not_started_live_stream(): void
    {
        $this->expectException(ModelNotFoundException::class);

        // Create live stream without started_at
        $live = Live::create(['stream_key' => 'not-started-key']);
        $feed = new Feed([
            'user_id' => $this->user->id,
            'title' => 'Not Started Stream',
            'allow_comments' => true,
            'privacy' => FeedPrivacyEnum::PublicView,
            'status' => FeedStatusEnum::Approved,
        ]);
        $feed->content()->associate($live);
        $feed->saveQuietly();

        $this->actingAs($this->user);

        $action = new EndLiveAction;
        $action->handle($live->id);
    }
}
