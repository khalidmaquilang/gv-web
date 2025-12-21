<?php

declare(strict_types=1);

namespace App\Features\Shared\Enums\Traits;

trait EnumArrayTrait
{
    /**
     * @return array<int, string|int>
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
