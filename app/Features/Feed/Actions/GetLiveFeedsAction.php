<?php

declare(strict_types=1);

namespace App\Features\Feed\Actions;

use App\Features\Feed\Models\Feed;
use App\Features\Live\Models\Live;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;

class GetLiveFeedsAction
{
    public function handle(string $user_id): CursorPaginator
    {
        return Feed::query()
            ->with(['user', 'content'])
            ->where('content_type', Live::class)
            ->whereHas('content', fn (Builder $query) => $query->whereNull('ended_at'))
            ->where('user_id', '<>', $user_id)
            ->accessible($user_id)
            ->latest()
            ->cursorPaginate(10);
    }
}
