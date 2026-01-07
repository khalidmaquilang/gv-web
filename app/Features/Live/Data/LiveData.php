<?php

declare(strict_types=1);

namespace App\Features\Live\Data;

use App\Features\Feed\Data\FeedData;
use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class LiveData extends Data
{
    public function __construct(
        public string $id,
        public string $stream_key,
        public FeedData|Optional $feed,
        public ?Carbon $started_at = null,
        public ?Carbon $ended_at = null,
    ) {}
}
