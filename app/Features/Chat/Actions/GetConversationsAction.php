<?php

declare(strict_types=1);

namespace App\Features\Chat\Actions;

use App\Features\Chat\Models\Conversation;
use Illuminate\Contracts\Pagination\CursorPaginator;

class GetConversationsAction
{
    /**
     * @return CursorPaginator<Conversation>
     */
    public function handle(): CursorPaginator
    {
        /** @var ?string $user_id */
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        return Conversation::query()
            ->forUser($user_id)
            ->with(['latestMessage.sender', 'latestMessage.receiver', 'users'])
            ->orderBy('updated_at', 'desc')
            ->cursorPaginate(10);
    }
}
