<?php

declare(strict_types=1);

namespace App\Features\User\Data;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $username,
        public ?string $avatar,
    ) {
        if ($avatar) {
            $this->avatar = Storage::url($avatar);
        }
    }
}
