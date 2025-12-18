<?php

declare(strict_types=1);

namespace App\Features\Video\Data;

use App\Features\Music\Data\MusicData;
use App\Features\User\UserData;
use App\Features\Video\Enums\VideoPrivacyEnum;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class VideoData extends Data
{
    public function __construct(
        public string|Optional $title,
        public string $description,
        public MusicData|Optional $music,
        public UserData|Optional $user,
        public string $thumbnail,
        public string|Optional $video_path,
        public array|Optional $images,
        public bool $allow_comments,
        public VideoPrivacyEnum $privacy,
    ) {
        if ($video_path) {
            $this->video_path = Storage::url($video_path);
        }
    }
}
