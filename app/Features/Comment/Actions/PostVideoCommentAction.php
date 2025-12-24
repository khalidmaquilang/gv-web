<?php

declare(strict_types=1);

namespace App\Features\Comment\Actions;

use App\Features\Comment\Data\PostVideoCommentData;
use App\Features\Comment\Models\Comment;
use App\Features\Feed\Models\Feed;

class PostVideoCommentAction
{
    public function handle(PostVideoCommentData $data, string $feed_id): string
    {
        /** @var ?string $user_id */
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        $feed = Feed::query()
            ->where('id', $feed_id)
            ->where('allow_comments', true)
            ->accessible($user_id)
            ->firstOrFail();

        $comment = Comment::create([
            'feed_id' => $feed->id,
            'user_id' => $user_id,
            'message' => $data->message,
        ]);

        return $comment->id;
    }
}
