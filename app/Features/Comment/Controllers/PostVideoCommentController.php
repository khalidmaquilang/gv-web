<?php

declare(strict_types=1);

namespace App\Features\Comment\Controllers;

use App\Features\Comment\Actions\PostVideoCommentAction;
use App\Features\Comment\Data\PostVideoCommentData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PostVideoCommentController extends Controller
{
    public function __construct(protected PostVideoCommentAction $post_video_comment_action) {}

    public function __invoke(PostVideoCommentData $data, string $feed_id): JsonResponse
    {
        $id = $this->post_video_comment_action->handle($data, $feed_id);

        return response()->json([
            'message' => 'success',
            'id' => $id,
        ]);
    }
}
