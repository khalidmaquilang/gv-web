<?php

declare(strict_types=1);

namespace App\Features\Comment\Actions;

use App\Features\Comment\Models\Comment;
use App\Features\Feed\Enums\FeedPrivacyEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class GetCommentsAction
{
    /**
     * @return LengthAwarePaginator<Comment>
     */
    public function handle(string $feed_id): LengthAwarePaginator
    {
        /** @var ?string $user_id */
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        return Comment::query()
            ->where('feed_id', $feed_id)
            ->whereHas('feed', function (Builder $query) use ($user_id): void {
                $query->where(function (Builder $query) use ($user_id): void {
                    $query->where(function (Builder $q): void {
                        $q->where('privacy', FeedPrivacyEnum::PublicView)
                            ->where('allow_comments', true);
                    })
                        ->orWhere('user_id', $user_id);
                });
            })
            ->latest()
            ->paginate(10);
    }
}
