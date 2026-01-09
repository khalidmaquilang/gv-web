<?php

declare(strict_types=1);

namespace App\Features\Music\Actions;

use App\Features\Video\Models\Video;
use Illuminate\Contracts\Pagination\CursorPaginator;

class GetVideoConnectedMusicAction
{
    public function handle(string $music_id): CursorPaginator
    {
        return Video::query()
            ->with('feed', 'feed.user', 'music')
            ->where('music_id', $music_id)
            ->cursorPaginate(10);
    }
}
