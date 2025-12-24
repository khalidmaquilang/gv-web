<?php

declare(strict_types=1);

namespace App\Features\Feed\Actions;

use Illuminate\Support\Facades\Cache;

class CheckBotViewFeedAction
{
    public function handle(string $finger_print): bool
    {
        if (Cache::has('shadow_ban:'.$finger_print)) {
            return true;
        }

        $key = 'global_velocity:'.$finger_print;

        $requests = Cache::add($key, 1, 60) ? 1 : Cache::increment($key);

        if ($requests > 60) {
            Cache::put('shadow_ban:'.$finger_print, true, 3600);

            return true;
        }

        return false;
    }
}
