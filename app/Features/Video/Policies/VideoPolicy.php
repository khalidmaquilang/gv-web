<?php

declare(strict_types=1);

namespace App\Features\Video\Policies;

use App\Features\User\Models\User;
use App\Features\Video\Enums\VideoPrivacyEnum;
use App\Features\Video\Models\Video;

class VideoPolicy
{
    public function view(User $user, Video $video): bool
    {
        // Public videos are viewable by anyone
        if ($video->privacy === VideoPrivacyEnum::PublicView) {
            return true;
        }

        // Private videos: only owner can view
        if ($video->privacy === VideoPrivacyEnum::PrivateView) {
            return $video->user_id === $user->id;
        }

        // Friends-only videos: only owner or friends
        //        if ($video->privacy === VideoPrivacyEnum::FriendsView) {
        //            return $video->user_id === $user->id || $user->isFriendWith($video->user);
        //        }

        return false;
    }
}
