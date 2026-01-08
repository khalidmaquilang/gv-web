<?php

declare(strict_types=1);

namespace App\Features\Feed\Tests\Actions;

use App\Features\Feed\Actions\GetLiveFeedsAction;
use App\Features\Feed\Enums\FeedPrivacyEnum;
use App\Features\Feed\Enums\FeedStatusEnum;
use App\Features\Feed\Models\Feed;
use App\Features\Live\Models\Live;
use App\Features\User\Models\User;
use App\Features\Video\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetLiveFeedsActionTest extends TestCase
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

    public function test_it_retrieves_live_feeds_excluding_current_user(): void
    {
        // Create live feeds from other users
        $live1 = Live::create(['stream_key' => 'key1']);
        $feed1 = new Feed([
            'user_id' => $this->otherUser->id,
            'title' => 'Other User Live 1',
            'allow_comments' => true,
            'privacy' => FeedPrivacyEnum::PublicView,
            'status' => FeedStatusEnum::Approved,
        ]);
        $feed1->content()->associate($live1);
        $feed1->saveQuietly();

        // Create live feed from current user (should be excluded)
        $myLive = Live::create(['stream_key' => 'my-key']);
        $myFeed = new Feed([
            'user_id' => $this->user->id,
            'title' => 'My Live Stream',
            'allow_comments' => true,
            'privacy' => FeedPrivacyEnum::PublicView,
            'status' => FeedStatusEnum::Approved,
        ]);
        $myFeed->content()->associate($myLive);
        $myFeed->saveQuietly();

        $action = new GetLiveFeedsAction;
        $result = $action->handle($this->user->id);

        $this->assertCount(1, $result->items());
        $this->assertEquals($feed1->id, $result->items()[0]->id);
        $this->assertEquals($this->otherUser->id, $result->items()[0]->user_id);
    }

    public function test_it_only_retrieves_live_content_type(): void
    {
        // Create a live feed
        $live = Live::create(['stream_key' => 'live-key']);
        $liveFeed = new Feed([
            'user_id' => $this->otherUser->id,
            'title' => 'Live Stream',
            'allow_comments' => true,
            'privacy' => FeedPrivacyEnum::PublicView,
            'status' => FeedStatusEnum::Approved,
        ]);
        $liveFeed->content()->associate($live);
        $liveFeed->saveQuietly();

        // Create a video feed (should be excluded)
        $video = Video::create([
            'video_path' => 'test.mp4',
        ]);
        $videoFeed = new Feed([
            'user_id' => $this->otherUser->id,
            'title' => 'Video Post',
            'allow_comments' => true,
            'privacy' => FeedPrivacyEnum::PublicView,
            'status' => FeedStatusEnum::Approved,
        ]);
        $videoFeed->content()->associate($video);
        $videoFeed->saveQuietly();

        $action = new GetLiveFeedsAction;
        $result = $action->handle($this->user->id);

        $this->assertCount(1, $result->items());
        $this->assertEquals(Live::class, $result->items()[0]->content_type);
    }

    public function test_it_respects_privacy_settings(): void
    {
        // Create public live feed
        $publicLive = Live::create(['stream_key' => 'public-key']);
        $publicFeed = new Feed([
            'user_id' => $this->otherUser->id,
            'title' => 'Public Live',
            'allow_comments' => true,
            'privacy' => FeedPrivacyEnum::PublicView,
            'status' => FeedStatusEnum::Approved,
        ]);
        $publicFeed->content()->associate($publicLive);
        $publicFeed->saveQuietly();

        // Create friends only live feed (should be excluded)
        $privateLive = Live::create(['stream_key' => 'private-key']);
        $privateFeed = new Feed([
            'user_id' => $this->otherUser->id,
            'title' => 'Friends Live',
            'allow_comments' => true,
            'privacy' => FeedPrivacyEnum::FriendsView,
            'status' => FeedStatusEnum::Approved,
        ]);
        $privateFeed->content()->associate($privateLive);
        $privateFeed->saveQuietly();

        $action = new GetLiveFeedsAction;
        $result = $action->handle($this->user->id);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Public Live', $result->items()[0]->title);
    }

    public function test_it_eager_loads_user_and_content_relationships(): void
    {
        $live = Live::create(['stream_key' => 'test-key']);
        $feed = new Feed([
            'user_id' => $this->otherUser->id,
            'title' => 'Test Live',
            'allow_comments' => true,
            'privacy' => FeedPrivacyEnum::PublicView,
            'status' => FeedStatusEnum::Approved,
        ]);
        $feed->content()->associate($live);
        $feed->saveQuietly();

        $action = new GetLiveFeedsAction;
        $result = $action->handle($this->user->id);

        $feedItem = $result->items()[0];

        // Check relationships are loaded
        $this->assertTrue($feedItem->relationLoaded('user'));
        $this->assertTrue($feedItem->relationLoaded('content'));
        $this->assertInstanceOf(User::class, $feedItem->user);
        $this->assertInstanceOf(Live::class, $feedItem->content);
    }

    public function test_it_returns_results_in_descending_order_by_created_at(): void
    {
        // Create multiple live feeds with different timestamps
        $live1 = Live::create(['stream_key' => 'key1']);
        $feed1 = new Feed([
            'user_id' => $this->otherUser->id,
            'title' => 'Old Live',
            'allow_comments' => true,
            'privacy' => FeedPrivacyEnum::PublicView,
            'status' => FeedStatusEnum::Approved,
        ]);
        $feed1->content()->associate($live1);
        $feed1->saveQuietly();
        $feed1->created_at = now()->subHours(2);
        $feed1->save();

        $live2 = Live::create(['stream_key' => 'key2']);
        $feed2 = new Feed([
            'user_id' => $this->otherUser->id,
            'title' => 'New Live',
            'allow_comments' => true,
            'privacy' => FeedPrivacyEnum::PublicView,
            'status' => FeedStatusEnum::Approved,
        ]);
        $feed2->content()->associate($live2);
        $feed2->saveQuietly();

        $action = new GetLiveFeedsAction;
        $result = $action->handle($this->user->id);

        $this->assertCount(2, $result->items());
        $this->assertEquals('New Live', $result->items()[0]->title);
        $this->assertEquals('Old Live', $result->items()[1]->title);
    }

    public function test_it_paginates_results_with_cursor(): void
    {
        // Create 15 live feeds (more than default 10 per page)
        for ($i = 1; $i <= 15; $i++) {
            $live = Live::create(['stream_key' => 'key-'.$i]);
            $feed = new Feed([
                'user_id' => $this->otherUser->id,
                'title' => 'Live '.$i,
                'allow_comments' => true,
                'privacy' => FeedPrivacyEnum::PublicView,
                'status' => FeedStatusEnum::Approved,
            ]);
            $feed->content()->associate($live);
            $feed->saveQuietly();
        }

        $action = new GetLiveFeedsAction;
        $result = $action->handle($this->user->id);

        $this->assertCount(10, $result->items());
        $this->assertTrue($result->hasMorePages());
    }

    public function test_it_filters_by_approved_and_processed_status_in_accessible_scope(): void
    {
        // Create approved live feed
        $approvedLive = Live::create(['stream_key' => 'approved-key']);
        $approvedFeed = new Feed([
            'user_id' => $this->otherUser->id,
            'title' => 'Approved Live',
            'allow_comments' => true,
            'privacy' => FeedPrivacyEnum::PublicView,
            'status' => FeedStatusEnum::Approved,
        ]);
        $approvedFeed->content()->associate($approvedLive);
        $approvedFeed->saveQuietly();

        // Create processing live feed (should be excluded)
        $processingLive = Live::create(['stream_key' => 'processing-key']);
        $processingFeed = new Feed([
            'user_id' => $this->otherUser->id,
            'title' => 'Processing Live',
            'allow_comments' => true,
            'privacy' => FeedPrivacyEnum::PublicView,
            'status' => FeedStatusEnum::Processing,
        ]);
        $processingFeed->content()->associate($processingLive);
        $processingFeed->saveQuietly();

        $action = new GetLiveFeedsAction;
        $result = $action->handle($this->user->id);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Approved Live', $result->items()[0]->title);
    }

    public function test_it_returns_empty_result_when_no_live_feeds_available(): void
    {
        $action = new GetLiveFeedsAction;
        $result = $action->handle($this->user->id);

        $this->assertCount(0, $result->items());
        $this->assertFalse($result->hasMorePages());
    }
}
