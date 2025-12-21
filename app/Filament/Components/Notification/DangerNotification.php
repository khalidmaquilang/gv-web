<?php

declare(strict_types=1);

namespace App\Filament\Components\Notification;

use Illuminate\Support\Str;

class DangerNotification extends Notification
{
    public static function make(?string $id = null): static
    {
        $static = app(static::class, ['id' => $id ?? Str::orderedUuid()]);
        $static->configure();
        $static->danger();

        return $static;
    }
}
