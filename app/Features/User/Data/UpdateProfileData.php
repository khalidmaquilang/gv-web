<?php

declare(strict_types=1);

namespace App\Features\User\Data;

use Spatie\LaravelData\Attributes\Validation\AlphaDash;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

class UpdateProfileData extends Data
{
    public function __construct(
        #[Max(255)]
        public string $name,
        #[AlphaDash]
        #[Max(255)]
        public string $username,
        #[Max(500)]
        public ?string $bio = null,
    ) {}
}
