<?php

declare(strict_types=1);

namespace App\Features\Live\Data;

use Spatie\LaravelData\Data;

class CreateLiveData extends Data
{
    public function __construct(public ?string $title = null) {}
}
