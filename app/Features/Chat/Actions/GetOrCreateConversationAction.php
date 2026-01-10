<?php

declare(strict_types=1);

namespace App\Features\Chat\Actions;

use App\Features\Chat\Models\Conversation;

class GetOrCreateConversationAction
{
    /**
     * Get or create a direct conversation between two users
     */
    public function handle(string $user1Id, string $user2Id): Conversation
    {
        return Conversation::findOrCreateDirectConversation($user1Id, $user2Id);
    }
}
