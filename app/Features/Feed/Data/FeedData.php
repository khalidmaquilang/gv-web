<?php

declare(strict_types=1);

namespace App\Features\Feed\Data;

use App\Features\Feed\Enums\FeedPrivacyEnum;
use App\Features\Feed\Enums\FeedStatusEnum;
use App\Features\Feed\Models\Feed;
use App\Features\Live\Data\LiveData;
use App\Features\Live\Models\Live;
use App\Features\User\Data\UserData;
use App\Features\Video\Data\VideoData;
use App\Features\Video\Models\Video;
use Carbon\Carbon;
use Illuminate\Support\Number;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class FeedData extends Data
{
    public function __construct(
        public string $id,
        public UserData $user,
        public VideoData|LiveData|Optional $content,
        public ?string $title,
        public bool $allow_comments,
        public FeedPrivacyEnum $privacy,
        public FeedStatusEnum $status,
        public int $views,
        public bool $is_reacted_by_user = false,
        public bool $is_author_followed_by_user = false,
        public int $reactions_count = 0,
        public ?string $formatted_views = null,
        public ?string $formatted_reactions_count = null,
        public ?Carbon $created_at = null,
    ) {
        $this->formatted_reactions_count = Number::abbreviate($this->reactions_count);
        $this->formatted_views = Number::abbreviate($this->views);
    }

    public static function fromModel(Feed $feed): self
    {
        $is_loaded = $feed->relationLoaded('content');

        return new self(
            id: $feed->id,
            user: UserData::from($feed->user),

            // Manual Polymorphic Logic
            content: match ($feed->content_type) {
                Live::class => $is_loaded ? LiveData::from($feed->content) : Optional::create(),
                Video::class => $is_loaded ? VideoData::from($feed->content) : Optional::create(),
                default => Optional::create(),
            },

            title: $feed->title,
            allow_comments: $feed->allow_comments,
            privacy: $feed->privacy,
            status: $feed->status,
            views: $feed->views,
            is_reacted_by_user: (bool) ($feed->is_reacted_by_user ?? false),
            is_author_followed_by_user: (bool) ($feed->is_author_followed_by_user ?? false),
            reactions_count: ($feed->reactions_count ?? 0),
            created_at: $feed->created_at,
        );
    }
}
