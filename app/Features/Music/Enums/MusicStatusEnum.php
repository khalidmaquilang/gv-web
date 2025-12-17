<?php

declare(strict_types=1);

namespace App\Features\Music\Enums;

use App\Features\Shared\Enums\Traits\EnumArrayTrait;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MusicStatusEnum: string implements HasColor, HasLabel
{
    use EnumArrayTrait;

    case Processing = 'processing';
    case Processed = 'processed';
    case Failed = 'failed';

    public function getColor(): string
    {
        return match ($this) {
            self::Processing => 'info',
            self::Processed => 'success',
            self::Failed => 'danger',
        };
    }

    public function getLabel(): string
    {
        return $this->name;
    }
}
