<?php

declare(strict_types=1);

namespace App\Features\Comment\Controllers;

use App\Features\Comment\Actions\GetCommentsAction;
use App\Features\Comment\Models\Comment;
use App\Http\Controllers\Controller;
use Binafy\LaravelReaction\Enums\LaravelReactionTypeEnum;
use Illuminate\Http\JsonResponse;

class ReactCommentController extends Controller
{
    public function __construct(protected GetCommentsAction $get_comments_action) {}

    public function __invoke(string $comment_id): JsonResponse
    {
        $comment = Comment::findOrFail($comment_id);

        if ($comment->isReacted()) {
            $comment->removeReaction(LaravelReactionTypeEnum::REACTION_LOVE);
        } else {
            $comment->reaction(LaravelReactionTypeEnum::REACTION_LOVE);
        }

        return response()->json(['message' => 'success']);
    }
}
