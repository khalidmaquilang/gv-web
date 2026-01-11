<?php

declare(strict_types=1);

namespace App\Features\User\Data;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UserData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $username,
        public ?string $avatar,
        public bool|Optional $is_following = false,
        public bool|Optional $you_are_followed = false,
        public int|Optional $followers_count = 0,
        public int|Optional $following_count = 0,
        public int|Optional $likes_count = 0,
        public bool|Optional $allow_live = false,
        public float|Optional $gv_coins = 0,
        public string|null|Optional $bio = null,
        #[Computed]
        public ?string $formatted_followers_count = null,
        #[Computed]
        public ?string $formatted_following_count = null,
        #[Computed]
        public ?string $formatted_likes_count = null,
    ) {
        if ($avatar) {
            $this->avatar = Storage::url($avatar);
        }

        // Format numbers similar to FeedData
        $this->formatted_followers_count = Number::abbreviate($this->followers_count);
        $this->formatted_following_count = Number::abbreviate($this->following_count);
        $this->formatted_likes_count = Number::abbreviate($this->likes_count);
    }
}
