<?php

declare(strict_types=1);

namespace App\Features\Chat\Actions;

use App\Features\Chat\Models\Chat;

class GetUnreadCountAction
{
    /**
     * Get the count of unread messages for the authenticated user
     */
    public function handle(): int
    {
        /** @var ?string $user_id */
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        return Chat::query()
            ->where('receiver_id', $user_id)
            ->where('is_read', false)
            ->count();
    }
}
