<?php

declare(strict_types=1);

namespace App\Features\Chat\Actions;

use App\Features\Chat\Models\Chat;
use Illuminate\Contracts\Pagination\CursorPaginator;

class GetChatsAction
{
    /**
     * Get chat messages between two users
     *
     * @return CursorPaginator<Chat>
     */
    public function handle(string $other_user_id): CursorPaginator
    {
        /** @var ?string $user_id */
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        return Chat::query()
            ->where(function ($query) use ($user_id, $other_user_id): void {
                $query->where('sender_id', $user_id)
                    ->where('receiver_id', $other_user_id);
            })
            ->orWhere(function ($query) use ($user_id, $other_user_id): void {
                $query->where('sender_id', $other_user_id)
                    ->where('receiver_id', $user_id);
            })
            ->with(['sender', 'receiver'])
            ->latest()
            ->cursorPaginate(20);
    }
}
