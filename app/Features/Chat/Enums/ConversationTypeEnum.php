<?php

declare(strict_types=1);

namespace App\Features\Chat\Enums;

use App\Features\Shared\Enums\Traits\EnumArrayTrait;

enum ConversationTypeEnum: string
{
    use EnumArrayTrait;

    case DIRECT = 'direct';
    case GROUP = 'group';
}
