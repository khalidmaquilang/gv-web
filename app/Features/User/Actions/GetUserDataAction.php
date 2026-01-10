<?php

declare(strict_types=1);

namespace App\Features\User\Actions;

use App\Features\Feed\Models\Feed;
use App\Features\Reaction\Models\Reaction;
use App\Features\User\Data\UserData;
use App\Features\User\Models\User;

class GetUserDataAction
{
    public function handle(User $user): UserData
    {
        $current_user = auth()->user();
        abort_if($current_user === null, 404);

        $is_following = $current_user->isFollowing($user);
        $is_followed_by = $current_user->isFollowedBy($user);

        $followers_count = $user->followers()->count();
        $following_count = $user->followings()->count();

        $likes_count = Reaction::query()
            ->whereHasMorph('reactable', [Feed::class], function ($query, string $type) use ($user): void {
                if ($type === Feed::class) {
                    $query->where('user_id', $user->id);
                }
            })->count();

        return new UserData(
            id: $user->id,
            name: $user->name,
            username: $user->username,
            avatar: $user->avatar,
            is_following: $is_following,
            you_are_followed: $is_followed_by,
            followers_count: $followers_count,
            following_count: $following_count,
            likes_count: $likes_count,
            allow_live: $user->allow_live,
            balance: $current_user->id === $user->id ? $user->getGvCoins() : 0,
            bio: $user->bio,
        );
    }
}
