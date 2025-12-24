<?php

declare(strict_types=1);

namespace App\Features\Feed\Actions;

use Illuminate\Support\Facades\Cache;

class SetFeedLockAction
{
    public function handle(string $feed_id, string $finger_print): bool
    {
        $lock_key = sprintf('view_lock:%s:%s', $feed_id, $finger_print);

        return Cache::add($lock_key, true, 30);
    }
}
