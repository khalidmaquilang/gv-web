<?php

declare(strict_types=1);

namespace App\Features\Video\Actions;

use App\Features\Video\Models\Video;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetMyVideosAction
{
    public function handle(): LengthAwarePaginator
    {
        $user = auth()->user();
        abort_if($user === null, 404);

        return Video::query()
            ->with('music')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);
    }
}
