<?php

declare(strict_types=1);

namespace App\Features\Music\Data;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Data;

class MusicData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $artist,
        public string $path,
        public string $thumbnail,
        public string $duration_formatted,
    ) {
        $this->path = Storage::url($path);
        $this->thumbnail = Storage::url($thumbnail);
    }
}
