<?php

declare(strict_types=1);

namespace App\Features\User\Actions;

use App\Features\Video\Models\Video;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class GetUserVideosAction
{
    public function handle(string $userId, string $view_user_id): LengthAwarePaginator
    {
        return Video::query()
            ->whereHas('feed', function (Builder $query) use ($userId, $view_user_id): void {
                $query
                    ->where('user_id', $userId)
                    ->accessible($view_user_id)
                    ->with('feed.user', 'music')
                    ->withCount('reactions')
                    ->withExists(['reactions as is_reacted_by_user' => function ($query) use ($view_user_id): void {
                        $query->where('user_id', $view_user_id);
                    }]);
            })
            ->with(['feed'])
            ->latest()
            ->paginate(10);
    }
}
