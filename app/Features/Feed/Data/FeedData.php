<?php

declare(strict_types=1);

namespace App\Features\Feed\Data;

use App\Features\User\Data\UserData;
use App\Features\Video\Data\VideoData;
use App\Features\Video\Enums\FeedPrivacyEnum;
use App\Features\Video\Enums\FeedStatusEnum;
use Spatie\LaravelData\Data;

class FeedData extends Data
{
    public function __construct(
        public string $id,
        public UserData $user,
        public VideoData $content,
        public ?string $title,
        public bool $allow_comments,
        public FeedPrivacyEnum $privacy,
        public FeedStatusEnum $status,
        public int $views,
    ) {}
}
