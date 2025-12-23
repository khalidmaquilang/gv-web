<?php

declare(strict_types=1);

namespace App\Features\Feed\Data;

use App\Features\Feed\Enums\FeedPrivacyEnum;
use App\Features\Feed\Enums\FeedStatusEnum;
use App\Features\User\Data\UserData;
use App\Features\Video\Data\VideoData;
use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class FeedData extends Data
{
    public function __construct(
        public string $id,
        public UserData $user,
        public VideoData|Optional $content,
        public ?string $title,
        public bool $allow_comments,
        public FeedPrivacyEnum $privacy,
        public FeedStatusEnum $status,
        public int $views,
        public bool $is_reacted_by_user = false,
        public int $reactions_count = 0,
        public ?Carbon $created_at = null,
    ) {}
}
