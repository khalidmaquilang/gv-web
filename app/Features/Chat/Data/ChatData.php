<?php

declare(strict_types=1);

namespace App\Features\Chat\Data;

use App\Features\User\Data\UserData;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class ChatData extends Data
{
    public function __construct(
        public string $id,
        public UserData $sender,
        public UserData $receiver,
        public string $message,
        public bool $is_read = false,
        public ?Carbon $read_at = null,
        public ?Carbon $created_at = null,
        public ?string $formatted_created_at = null,
    ) {
        if ($created_at instanceof Carbon) {
            $this->formatted_created_at = $created_at->diffForHumans();
        }
    }
}
