<?php

declare(strict_types=1);

namespace App\Features\Video\Enums;

use App\Features\Shared\Enums\Traits\EnumArrayTrait;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum VideoPrivacyEnum: string implements HasColor, HasLabel
{
    use EnumArrayTrait;

    case PublicView = 'public';
    case PrivateView = 'private';
    case FriendsView = 'friends';

    public function getColor(): string
    {
        return match ($this) {
            self::PublicView => 'info',
            self::PrivateView => 'success',
            self::FriendsView => 'danger',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PublicView => 'Public',
            self::PrivateView => 'Private',
            self::FriendsView => 'Friends',
        };
    }
}
