<?php

declare(strict_types=1);

namespace App\Features\Music\Enums;

use App\Features\Shared\Enums\Traits\EnumArrayTrait;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MusicTypeEnum: string implements HasColor, HasLabel
{
    use EnumArrayTrait;

    case Community = 'community';
    case Portal = 'portal';

    public function getColor(): string
    {
        return match ($this) {
            self::Community => 'info',
            self::Portal => 'success',
        };
    }

    public function getLabel(): string
    {
        return $this->name;
    }
}
