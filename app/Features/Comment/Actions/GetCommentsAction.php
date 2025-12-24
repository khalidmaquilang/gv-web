<?php

declare(strict_types=1);

namespace App\Features\Comment\Actions;

use App\Features\Comment\Models\Comment;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;

class GetCommentsAction
{
    /**
     * @return CursorPaginator<Comment>
     */
    public function handle(string $feed_id): CursorPaginator
    {
        /** @var ?string $user_id */
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        return Comment::query()
            ->where('feed_id', $feed_id)
            ->with('user')
            ->withCount('reactions')
            ->withExists(['reactions as is_reacted_by_user' => function (Builder $query) use ($user_id): void {
                $query->where('user_id', $user_id);
            }])
            ->whereHas('feed', function (Builder $query) use ($user_id): void {
                $query->accessible($user_id);
            })
            ->latest()
            ->cursorPaginate(10);
    }
}
