<?php

declare(strict_types=1);

namespace App\Features\User\Actions;

use App\Features\User\Data\UserData;
use App\Features\User\Models\User;

class GetUserDataAction
{
    public function handle(User $user): UserData
    {
        $currentUser = auth()->user();

        $isFollowing = false;
        if ($currentUser && $currentUser->id !== $user->id && method_exists($currentUser, 'isFollowing')) {
            $isFollowing = $currentUser->isFollowing($user);
        }

        $followersCount = $user->followers()->count();
        $followingCount = $user->following()->count();

        // TODO: Calculate likes from user's videos when ready
        // $likesCount = $user->videos()->withCount('reactions')->get()->sum('reactions_count');
        $likesCount = 0;

        return new UserData(
            id: $user->id,
            name: $user->name,
            username: $user->username,
            avatar: $user->avatar,
            is_following: $isFollowing,
            followers_count: $followersCount,
            following_count: $followingCount,
            likes_count: $likesCount,
            allow_live: $user->allow_live,
            bio: $user->bio,
        );
    }
}
