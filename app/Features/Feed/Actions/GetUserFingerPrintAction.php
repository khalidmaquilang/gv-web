<?php

declare(strict_types=1);

namespace App\Features\Feed\Actions;

class GetUserFingerPrintAction
{
    public function handle(string $user_id): string
    {
        return 'user:'.$user_id;
    }
}
