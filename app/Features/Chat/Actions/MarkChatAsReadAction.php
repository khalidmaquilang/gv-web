<?php

declare(strict_types=1);

namespace App\Features\Chat\Actions;

use App\Features\Chat\Events\MessageRead;
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
            ->with('conversation')
            ->firstOrFail();

        // Update the user's last_read_at timestamp in the conversation_user pivot
        $chat->conversation->users()->updateExistingPivot($user_id, [
            'last_read_at' => now(),
        ]);

        // Also mark the individual message as read for backward compatibility
        $chat->markAsRead();

        // Broadcast the read receipt to the sender
        broadcast(new MessageRead($chat))->toOthers();
    }
}
