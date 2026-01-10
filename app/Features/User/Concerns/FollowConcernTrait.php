<?php

declare(strict_types=1);

namespace App\Features\User\Concerns;

use Overtrue\LaravelFollow\Traits\Followable;
use Overtrue\LaravelFollow\Traits\Follower;

trait FollowConcernTrait
{
    use Followable;
    use Follower;
}
