<?php

declare(strict_types=1);

namespace App\Features\Video\Data;

use App\Features\Video\Enums\VideoPrivacyEnum;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Data;

class ListVideoData extends Data
{
    public function __construct(
        public string $thumbnail,
        public VideoPrivacyEnum $privacy,
        public int $views = 0
    ) {
        $this->thumbnail = Storage::url($thumbnail);
    }
}
