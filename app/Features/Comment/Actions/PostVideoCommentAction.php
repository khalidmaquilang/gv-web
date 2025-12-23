<?php

declare(strict_types=1);

namespace App\Features\Comment\Actions;

use App\Features\Comment\Data\PostVideoCommentData;
use App\Features\Feed\Models\Feed;

class PostVideoCommentAction
{
    public function handle(PostVideoCommentData $data, string $feed_id): void
    {
        /** @var ?string $user_id */
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        $feed = Feed::query()
            ->where('id', $feed_id)
            ->where('allow_comments', true)
            ->accessible()
            ->firstOrFail();

        $feed->comments()->create([
            'user_id' => $user_id,
            'message' => $data->message,
        ]);
    }
}
