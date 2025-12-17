<?php

declare(strict_types=1);

namespace App\Features\Webhook\Data;

use App\Features\Webhook\Enums\WebhookEnum;
use Spatie\LaravelData\Data;

class FfmpegData extends Data
{
    public function __construct(
        public string $model_id,
        public string $model_type,
        public int $duration,
        public WebhookEnum $status,
        public string $path,
    ) {}
}
