<?php

declare(strict_types=1);

namespace App\Features\Comment\Data;

use App\Features\User\Data\UserData;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class CommentData extends Data
{
    public function __construct(
        public string $id,
        public UserData $user,
        public string $message,
        public bool $is_reacted_by_user = false,
        public int $reactions_count = 0,
        public ?Carbon $created_at = null,
        public ?string $formatted_created_at = null,
    ) {
        if ($created_at instanceof Carbon) {
            $this->formatted_created_at = $created_at->diffForHumans();
        }
    }
}
