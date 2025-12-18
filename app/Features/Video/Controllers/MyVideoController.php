<?php

declare(strict_types=1);

namespace App\Features\Video\Controllers;

use App\Features\Video\Actions\GetMyVideosAction;
use App\Features\Video\Data\ListVideoData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyVideoController extends Controller
{
    public function __construct(protected GetMyVideosAction $get_my_videos_action) {}

    public function __invoke(Request $request): JsonResponse
    {
        $videos = $this->get_my_videos_action->handle();

        return response()->json([
            'data' => ListVideoData::collect($videos),
        ]);
    }
}
