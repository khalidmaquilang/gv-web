<?php

declare(strict_types=1);

namespace App\Features\Feed\Actions;

use App\Features\Feed\Models\Feed;
use App\Features\Live\Models\Live;
use App\Features\User\Models\User;
use App\Features\Video\Models\Video;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Query\Builder;

class GetFeedsAction
{
    public function handle(string $user_id, bool $only_followed = false): CursorPaginator
    {
        return Feed::query()
            ->select('feeds.*')
            // OPTIMIZATION 1: Use a raw subquery with 'exists'.
            // This stops scanning immediately after finding 1 record (faster than count).
            ->selectRaw('exists(select * from `followables` where `followables`.`followable_id` = `feeds`.`user_id` and `followables`.`followable_type` = ? and `followables`.`user_id` = ?) as is_author_followed_by_user', [User::class, $user_id])
            ->with(['user', 'content'])
            ->accessible($user_id)
            ->where('user_id', '<>', $user_id)
            // OPTIMIZATION 2: Use whereExists instead of whereIn.
            // Handles large datasets better as it avoids creating a massive array of IDs.
            ->when($only_followed, function ($query) use ($user_id): void {
                $query->whereExists(function (Builder $subQuery) use ($user_id): void {
                    $subQuery->selectRaw(1)
                        ->from('followables')
                        ->whereColumn('followables.followable_id', 'feeds.user_id')
                        ->where('followables.followable_type', User::class)
                        ->where('followables.user_id', $user_id);
                });
            })
            ->whereHasMorph('content', [Video::class, Live::class], function ($query, string $type): void {
                if ($type === Live::class) {
                    $query->whereNull('ended_at');
                }
            })
            ->feedAlgorithm()
            ->withCount('reactions')
            ->withExists(['reactions as is_reacted_by_user' => function ($query) use ($user_id): void {
                $query->where('user_id', $user_id);
            }])
            ->cursorPaginate(10);
    }
}
