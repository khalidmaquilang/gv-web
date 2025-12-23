<?php

declare(strict_types=1);

namespace App\Features\User\Data;

use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $username,
        public ?string $avatar,
    ) {}
}
