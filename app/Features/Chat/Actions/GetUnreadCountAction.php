<?php

declare(strict_types=1);

namespace App\Features\Chat\Actions;

use App\Features\Chat\Models\Chat;
use Illuminate\Support\Facades\DB;

class GetUnreadCountAction
{
    /**
     * Get the count of unread messages for the authenticated user
     * Based on messages created after user's last_read_at in each conversation
     */
    public function handle(): int
    {
        /** @var ?string $user_id */
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        // Count messages where:
        // 1. User is not the sender
        // 2. Message was created after the user's last_read_at for that conversation
        return Chat::query()
            ->where('sender_id', '!=', $user_id)
            ->whereExists(function ($query) use ($user_id): void {
                $query->select(DB::raw(1))
                    ->from('conversation_user')
                    ->whereColumn('conversation_user.conversation_id', 'chats.conversation_id')
                    ->where('conversation_user.user_id', $user_id)
                    ->where(function ($q): void {
                        $q->whereNull('conversation_user.last_read_at')
                            ->orWhereColumn('chats.created_at', '>', 'conversation_user.last_read_at');
                    });
            })
            ->count();
    }
}
