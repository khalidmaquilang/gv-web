<?php

declare(strict_types=1);

namespace App\Features\Feed\Actions;

use App\Features\Feed\Models\Feed;
use App\Features\Live\Models\Live;
use App\Features\Video\Models\Video;
use Illuminate\Contracts\Pagination\CursorPaginator;

class GetFeedsAction
{
    public function handle(string $user_id, bool $only_followed = false): CursorPaginator
    {
        // TODO: This is just for MVP, optimize this query
        return Feed::query()
            ->with(['user', 'content'])
            ->accessible($user_id)
            ->where('user_id', '<>', $user_id)
            ->when($only_followed, function ($query) use ($user_id): void {
                $query->whereIn('user_id', function ($query) use ($user_id): void {
                    $query->select('following_id')
                        ->from('followers')
                        ->where('follower_id', $user_id);
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
