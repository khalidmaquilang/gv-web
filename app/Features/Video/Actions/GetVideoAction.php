<?php

declare(strict_types=1);

namespace App\Features\Video\Actions;

use App\Features\Video\Models\Video;

class GetVideoAction
{
    public function handle(string $video_id): ?Video
    {
        return Video::query()
            ->with(['user', 'music'])
            ->published()
            ->where('id', $video_id)
            ->first();
    }
}
