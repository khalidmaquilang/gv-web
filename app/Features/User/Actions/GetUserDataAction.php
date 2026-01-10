<?php

declare(strict_types=1);

namespace App\Features\User\Actions;

use App\Features\User\Data\UserData;
use App\Features\User\Models\User;

class GetUserDataAction
{
    public function handle(User $user): UserData
    {
        $current_user = auth()->user();

        $is_following = false;
        if ($current_user && $current_user->id !== $user->id && method_exists($current_user, 'is_following')) {
            $is_following = $current_user->is_following($user);
        }

        $followers_count = $user->followers()->count();
        $following_count = $user->following()->count();

        // TODO: Calculate likes from user's videos when ready
        // $likesCount = $user->videos()->withCount('reactions')->get()->sum('reactions_count');
        $likesCount = 0;

        return new UserData(
            id: $user->id,
            name: $user->name,
            username: $user->username,
            avatar: $user->avatar,
            is_following: $is_following,
            followers_count: $followers_count,
            following_count: $following_count,
            likes_count: $likesCount,
            allow_live: $user->allow_live,
            balance: $current_user->id === $user->id ? $user->getGvCoins() : 0,
            bio: $user->bio,
        );
    }
}
