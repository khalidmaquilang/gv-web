<?php

declare(strict_types=1);

namespace App\Features\User\Actions;

use App\Features\User\Models\User;

class UnfollowUserAction
{
    public function handle(string $userId): User
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        abort_if($currentUser === null, 401, 'Unauthenticated');

        $userToUnfollow = User::findOrFail($userId);

        // Unfollow if currently following
        if ($currentUser->isFollowing($userToUnfollow)) {
            $currentUser->following()->detach($userToUnfollow->id);
        }

        return $userToUnfollow->fresh();
    }
}
