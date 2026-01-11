<?php

declare(strict_types=1);

namespace App\Features\Chat\Actions;

use App\Features\Chat\Models\Chat;
use Illuminate\Contracts\Pagination\CursorPaginator;

class GetChatsAction
{
    /**
     * Get chat messages for a conversation with another user
     *
     * @return CursorPaginator<Chat>
     */
    public function handle(string $other_user_id): CursorPaginator
    {
        /** @var ?string $user_id */
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        // Get chats where the conversation includes both users
        return Chat::query()
            ->whereHas('conversation.users', fn ($q) => $q->where('user_id', $user_id))
            ->whereHas('conversation.users', fn ($q) => $q->where('user_id', $other_user_id))
            ->with(['sender', 'receiver'])
            ->latest()
            ->cursorPaginate(20);
    }
}
