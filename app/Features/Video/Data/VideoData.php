<?php

declare(strict_types=1);

namespace App\Features\Video\Data;

use App\Features\Feed\Data\FeedData;
use App\Features\Music\Data\MusicData;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class VideoData extends Data
{
    public function __construct(
        public string|Optional $thumbnail,
        public FeedData $feed,
        public MusicData|Optional $music,
        public string|Optional $video_path,
    ) {
        if ($thumbnail && ! $thumbnail instanceof Optional) {
            $this->thumbnail = Storage::url($thumbnail);
        }

        if ($video_path && ! $video_path instanceof Optional) {
            $this->video_path = Storage::url($video_path);
        }
    }
}
