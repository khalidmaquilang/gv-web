<?php

declare(strict_types=1);

namespace App\Features\Chat\Actions;

use App\Features\Chat\Models\Chat;

class MarkChatAsReadAction
{
    public function handle(string $chat_id): void
    {
        /** @var ?string $user_id */
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        $chat = Chat::query()
            ->where('id', $chat_id)
            ->where('receiver_id', $user_id)
            ->firstOrFail();

        $chat->markAsRead();
    }
}
