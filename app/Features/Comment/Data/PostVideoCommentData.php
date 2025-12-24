<?php

declare(strict_types=1);

namespace App\Features\Comment\Data;

use Spatie\LaravelData\Data;

class PostVideoCommentData extends Data
{
    public function __construct(
        public string $message,
    ) {}
}
