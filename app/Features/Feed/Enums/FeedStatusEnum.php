<?php

declare(strict_types=1);

namespace App\Features\Feed\Enums;

use App\Features\Shared\Enums\Traits\EnumArrayTrait;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FeedStatusEnum: string implements HasColor, HasLabel
{
    use EnumArrayTrait;

    case Processing = 'processing';
    case Processed = 'processed';
    case Failed = 'failed';
    case Banned = 'banned';
    case Approved = 'approved';

    public function getColor(): string
    {
        return match ($this) {
            self::Processing => 'info',
            self::Processed => 'success',
            self::Failed => 'danger',
            self::Banned => 'danger',
            self::Approved => 'success',
        };
    }

    public function getLabel(): string
    {
        return $this->name;
    }
}
