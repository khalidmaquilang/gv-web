<?php

declare(strict_types=1);

namespace App\Features\Comment\Data;

use App\Features\User\Data\UserData;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class CommentData extends Data
{
    public function __construct(
        public UserData $user,
        public string $message,
        public Carbon $created_at
    ) {}
}
