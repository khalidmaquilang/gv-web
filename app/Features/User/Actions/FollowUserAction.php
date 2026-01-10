<?php

declare(strict_types=1);

namespace App\Features\User\Actions;

use App\Features\User\Models\User;

class FollowUserAction
{
    public function handle(string $userId): User
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        abort_if($currentUser === null, 401, 'Unauthenticated');

        $userToFollow = User::findOrFail($userId);

        // Prevent following yourself
        abort_if($currentUser->id === $userToFollow->id, 400, 'Cannot follow yourself');

        // Follow if not already following
        if (! $currentUser->isFollowing($userToFollow)) {
            $currentUser->follow($userToFollow);
        }

        return $userToFollow->fresh();
    }
}
