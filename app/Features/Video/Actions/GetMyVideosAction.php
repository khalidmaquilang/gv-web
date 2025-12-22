<?php

declare(strict_types=1);

namespace App\Features\Video\Actions;

use App\Features\Video\Models\Video;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class GetMyVideosAction
{
    public function handle(): LengthAwarePaginator
    {
        $user = auth()->user();
        abort_if($user === null, 404);

        return Video::query()
            ->whereHas('feed', fn (Builder $query) => $query->where('user_id', $user->id))
            ->with(['feed', 'music'])
            ->latest()
            ->paginate(10);
    }
}
