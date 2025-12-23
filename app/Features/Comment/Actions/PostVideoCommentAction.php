<?php

declare(strict_types=1);

namespace App\Features\Comment\Actions;

use App\Features\Comment\Data\PostVideoCommentData;
use App\Features\Feed\Enums\FeedPrivacyEnum;
use App\Features\Feed\Models\Feed;
use Illuminate\Database\Eloquent\Builder;

class PostVideoCommentAction
{
    public function handle(PostVideoCommentData $data, string $feed_id): void
    {
        /** @var ?string $user_id */
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        $feed = Feed::query()
            ->where('id', $feed_id)
            ->where(function (Builder $query) use ($user_id): void {
                $query->where(function (Builder $q): void {
                    $q->where('privacy', FeedPrivacyEnum::PublicView)
                        ->where('allow_comments', true);
                })
                    ->orWhere('user_id', $user_id);
            })
            ->firstOrFail();

        $feed->comments()->create([
            'user_id' => $user_id,
            'message' => $data->message,
        ]);
    }
}
