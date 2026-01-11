<?php

declare(strict_types=1);

namespace App\Features\Chat\Actions;

use App\Features\Chat\Data\SendChatMessageData;
use App\Features\Chat\Events\MessageSent;
use App\Features\Chat\Models\Chat;

class SendChatMessageAction
{
    public function __construct(
        protected GetOrCreateConversationAction $get_or_create_conversation_action
    ) {}

    public function handle(SendChatMessageData $data): string
    {
        /** @var ?string $user_id */
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        // Get or create conversation
        $conversation = $this->get_or_create_conversation_action->handle(
            $user_id,
            $data->receiver_id
        );

        // Create the chat message
        $chat = Chat::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user_id,
            'receiver_id' => $data->receiver_id,
            'message' => $data->message,
            'is_read' => false,
        ]);

        // Update conversation timestamp
        $conversation->touch();

        // Load relationships for broadcasting
        $chat->load(['sender', 'receiver']);

        // Broadcast the message to the receiver
        broadcast(new MessageSent($chat))->toOthers();

        return $chat->id;
    }
}
