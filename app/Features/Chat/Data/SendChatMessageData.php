<?php

declare(strict_types=1);

namespace App\Features\Chat\Data;

use Spatie\LaravelData\Data;

class SendChatMessageData extends Data
{
    public function __construct(
        public string $receiver_id,
        public string $message,
    ) {}
}
