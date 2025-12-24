<?php

declare(strict_types=1);

namespace App\Features\Comment\Controllers;

use App\Features\Comment\Actions\GetCommentsAction;
use App\Features\Comment\Data\CommentData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CommentsController extends Controller
{
    public function __construct(protected GetCommentsAction $get_comments_action) {}

    public function __invoke(string $feed_id): JsonResponse
    {
        $comments = $this->get_comments_action->handle($feed_id);

        return response()->json(CommentData::collect($comments));
    }
}
