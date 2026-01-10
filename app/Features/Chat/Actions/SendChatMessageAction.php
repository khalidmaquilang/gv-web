<?php

declare(strict_types=1);

namespace App\Features\Chat\Actions;

use App\Features\Chat\Data\SendChatMessageData;
use App\Features\Chat\Events\MessageSent;
use App\Features\Chat\Models\Chat;

class SendChatMessageAction
{
    public function handle(SendChatMessageData $data): string
    {
        /** @var ?string $user_id */
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        $chat = Chat::create([
            'sender_id' => $user_id,
            'receiver_id' => $data->receiver_id,
            'message' => $data->message,
            'is_read' => false,
        ]);

        // Load relationships for broadcasting
        $chat->load(['sender', 'receiver']);

        // Broadcast the message to the receiver
        broadcast(new MessageSent($chat))->toOthers();

        return $chat->id;
    }
}
