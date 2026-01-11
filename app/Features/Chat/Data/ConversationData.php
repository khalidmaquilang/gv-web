<?php

declare(strict_types=1);

namespace App\Features\Chat\Data;

use App\Features\Chat\Enums\ConversationTypeEnum;
use App\Features\Chat\Models\Conversation;
use App\Features\User\Data\UserData;
use Spatie\LaravelData\Data;

class ConversationData extends Data
{
    public function __construct(
        public string $id,
        public ConversationTypeEnum $type,
        public ?string $name,
        /** @var array<int, UserData> */
        public array $participants,
        public ?ChatData $last_message,
        public int $unread_count,
        public string $updated_at,
    ) {}

    public static function fromModel(Conversation $conversation): self
    {
        $currentUserId = auth()->id();
        abort_if($currentUserId === null, 404);

        // Get other participant(s) - exclude current user, manually map to avoid wallet lazy loading
        $participants = $conversation->users
            ->filter(fn ($user): bool => $user->id !== $currentUserId)
            ->map(fn ($user): \App\Features\User\Data\UserData => new UserData(
                id: $user->id,
                name: $user->name,
                username: $user->username,
                avatar: $user->avatar,
            ))
            ->values()
            ->all();

        // Get last message - manually create to avoid wallet lazy loading
        $lastMessage = null;
        if ($conversation->latestMessage) {
            $chat = $conversation->latestMessage;
            $lastMessage = new ChatData(
                id: $chat->id,
                sender: new UserData(
                    id: $chat->sender->id,
                    name: $chat->sender->name,
                    username: $chat->sender->username,
                    avatar: $chat->sender->avatar,
                ),
                receiver: new UserData(
                    id: $chat->receiver->id,
                    name: $chat->receiver->name,
                    username: $chat->receiver->username,
                    avatar: $chat->receiver->avatar,
                ),
                message: $chat->message,
                is_read: $chat->is_read,
                read_at: $chat->read_at,
                created_at: $chat->created_at,
            );
        }

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
            id: (string) $conversation->id,
            type: $conversation->type,
            name: $conversation->name,
            participants: $participants,
            last_message: $lastMessage,
            unread_count: $unreadCount,
            updated_at: $conversation->updated_at->toISOString(),
        );
    }
}
