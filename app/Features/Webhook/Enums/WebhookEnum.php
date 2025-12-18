<?php

declare(strict_types=1);

namespace App\Features\Webhook\Enums;

use App\Features\Music\Enums\MusicStatusEnum;
use App\Features\Video\Enums\VideoStatusEnum;

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

    public function toVideo(): VideoStatusEnum
    {
        return match ($this) {
            self::Success => VideoStatusEnum::Processed,
            self::Error => VideoStatusEnum::Failed,
        };
    }
}
