<?php

declare(strict_types=1);

namespace App\Features\Video\Controllers;

use App\Features\Video\Actions\VideoUploadAction;
use App\Features\Video\Data\VideoUploadData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class VideoUploadController extends Controller
{
    public function __construct(protected VideoUploadAction $video_upload_action) {}

    public function __invoke(VideoUploadData $request): JsonResponse
    {
        $this->video_upload_action->handle($request);

        return response()->json(['message' => 'success']);
    }
}
