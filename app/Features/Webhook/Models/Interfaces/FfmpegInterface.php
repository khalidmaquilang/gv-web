<?php

declare(strict_types=1);

namespace App\Features\Webhook\Models\Interfaces;

use App\Features\Webhook\Enums\WebhookEnum;

interface FfmpegInterface
{
    public static function updateMediaStatus(string $model_id, WebhookEnum $status, int $duration, string $path): void;
}
