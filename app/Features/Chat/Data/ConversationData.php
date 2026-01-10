<?php

declare(strict_types=1);

namespace App\Features\Chat\Data;

use App\Features\Chat\Models\Conversation;
use App\Features\User\Data\UserData;
use Spatie\LaravelData\Data;

class ConversationData extends Data
{
    public function __construct(
        public string $id,
        public string $type,
        public ?string $name,
        /** @var array<int, UserData> */
        public array $participants,
        public ?ChatData $last_message,
        public int $unread_count,
        public string $updated_at,
    ) {}

    public static function fromModel(Conversation $conversation, string $currentUserId): self
    {
        // Get other participant(s)
        $participants = $conversation->users
            ->map(fn ($user): \App\Features\User\Data\UserData => UserData::from($user))
            ->toArray();

        // Get last message
        $lastMessage = $conversation->latestMessage
             ? ChatData::from($conversation->latestMessage)
             : null;

        // Get unread count (messages created after user's last_read_at)
        $userPivot = $conversation->users()
            ->where('user_id', $currentUserId)
            ->first()
            ?->pivot;

        $unreadCount = 0;
        if ($userPivot) {
            $unreadCount = $conversation->messages()
                ->where('sender_id', '!=', $currentUserId)
                ->where(function ($query) use ($userPivot): void {
                    if ($userPivot->last_read_at) {
                        $query->where('created_at', '>', $userPivot->last_read_at);
                    }
                })
                ->count();
        }

        return new self(
            id: $conversation->id,
            type: $conversation->type,
            name: $conversation->name,
            participants: $participants,
            last_message: $lastMessage,
            unread_count: $unreadCount,
            updated_at: $conversation->updated_at->toISOString(),
        );
    }
}
