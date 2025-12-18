<?php

declare(strict_types=1);

namespace App\Features\Video\Controllers;

use App\Features\Video\Actions\GetVideoAction;
use App\Features\Video\Data\VideoData;
use Illuminate\Http\JsonResponse;

class VideoController
{
    public function __construct(protected GetVideoAction $get_video_action) {}

    public function __invoke(string $video_id): JsonResponse
    {
        $video = $this->get_video_action->handle($video_id);

        auth()->user()->can('view', $video);

        return response()->json($video instanceof \App\Features\Video\Models\Video ? VideoData::from($video) : null);
    }
}
