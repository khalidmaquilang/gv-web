<?php

declare(strict_types=1);

namespace App\Features\Live\Actions;

use App\Features\Live\Models\Live;
use Illuminate\Database\Eloquent\Builder;

class StartLiveAction
{
    public function handle(string $live_id): void
    {
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        $live = Live::query()
            ->where('id', $live_id)
            ->whereNull('started_at')
            ->whereHas('feed', function (Builder $query) use ($user_id): void {
                $query->where('user_id', $user_id);
            })
            ->firstOrFail();

        $live->update([
            'started_at' => now(),
        ]);
    }
}
