<?php

declare(strict_types=1);

namespace App\Features\Webhook\Enums;

use App\Features\Feed\Enums\FeedStatusEnum;
use App\Features\Music\Enums\MusicStatusEnum;

enum WebhookEnum: string
{
    case Success = 'success';
    case Error = 'error';

    public function toMusic(): MusicStatusEnum
    {
        return match ($this) {
            self::Success => MusicStatusEnum::Processed,
            self::Error => MusicStatusEnum::Failed,
        };
    }

    public function toFeed(): FeedStatusEnum
    {
        return match ($this) {
            self::Success => FeedStatusEnum::Processed,
            self::Error => FeedStatusEnum::Failed,
        };
    }
}
