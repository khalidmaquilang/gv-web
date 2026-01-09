<?php

declare(strict_types=1);

namespace App\Features\Music\Controllers;

use App\Features\Music\Actions\GetVideoConnectedMusicAction;
use App\Features\Video\Data\VideoData;
use App\Http\Controllers\Controller;

class GetVideoConnectedMusicController extends Controller
{
    public function __construct(protected GetVideoConnectedMusicAction $get_video_connected_music_action) {}

    public function __invoke(string $music_id)
    {
        $videos = $this->get_video_connected_music_action->handle($music_id);

        return response()->json(VideoData::collect($videos));
    }
}
