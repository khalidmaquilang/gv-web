<?php

declare(strict_types=1);

namespace App\Features\Live\Tests\Actions;

use App\Features\Feed\Enums\FeedPrivacyEnum;
use App\Features\Feed\Enums\FeedStatusEnum;
use App\Features\Feed\Models\Feed;
use App\Features\Live\Actions\CreateLiveAction;
use App\Features\Live\Data\CreateLiveData;
use App\Features\Live\Models\Live;
use App\Features\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateLiveActionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_it_creates_a_live_stream_with_feed(): void
    {
        $data = new CreateLiveData(title: 'My Live Stream');

        $this->actingAs($this->user);

        $action = new CreateLiveAction;
        $liveId = $action->handle($data);

        // Assert Live was created
        $this->assertIsString($liveId);
        $live = Live::find($liveId);
        $this->assertNotNull($live);
        $this->assertNotEmpty($live->stream_key);
        $this->assertNull($live->started_at);
        $this->assertNull($live->ended_at);

        // Assert Feed was created and linked
        $feed = Feed::where('content_type', Live::class)
            ->where('content_id', $liveId)
            ->first();

        $this->assertNotNull($feed);
        $this->assertEquals($this->user->id, $feed->user_id);
        $this->assertEquals('My Live Stream', $feed->title);
        $this->assertTrue($feed->allow_comments);
        $this->assertEquals(FeedPrivacyEnum::PublicView, $feed->privacy);
        $this->assertEquals(FeedStatusEnum::Approved, $feed->status);

        // Assert polymorphic relationship
        $this->assertInstanceOf(Live::class, $feed->content);
        $this->assertEquals($liveId, $feed->content->id);
        $this->assertInstanceOf(Feed::class, $live->feed);
        $this->assertEquals($feed->id, $live->feed->id);
    }

    public function test_it_creates_a_live_stream_without_title(): void
    {
        $data = new CreateLiveData;

        $this->actingAs($this->user);

        $action = new CreateLiveAction;
        $liveId = $action->handle($data);

        $feed = Feed::where('content_type', Live::class)
            ->where('content_id', $liveId)
            ->first();

        $this->assertEquals('', $feed->title);
    }

    public function test_it_generates_unique_stream_keys(): void
    {
        $data = new CreateLiveData(title: 'Test Stream');

        $this->actingAs($this->user);

        $action = new CreateLiveAction;
        $liveId1 = $action->handle($data);
        $liveId2 = $action->handle($data);

        $live1 = Live::find($liveId1);
        $live2 = Live::find($liveId2);

        $this->assertNotEquals($live1->stream_key, $live2->stream_key);
    }

    public function test_it_fails_when_user_is_not_authenticated(): void
    {
        $this->expectException(\Exception::class);

        $data = new CreateLiveData(title: 'Test Stream');

        $action = new CreateLiveAction;
        $action->handle($data);
    }

    public function test_it_sets_correct_feed_attributes(): void
    {
        $data = new CreateLiveData(title: 'Attribute Test');

        $this->actingAs($this->user);

        $action = new CreateLiveAction;
        $liveId = $action->handle($data);

        $feed = Feed::where('content_id', $liveId)->first();

        // Verify all feed attributes are set correctly
        $this->assertEquals($this->user->id, $feed->user_id);
        $this->assertTrue($feed->allow_comments);
        $this->assertEquals(FeedPrivacyEnum::PublicView->value, $feed->privacy->value);
        $this->assertEquals(FeedStatusEnum::Approved->value, $feed->status->value);
        $this->assertEquals(0, $feed->views);
    }
}
