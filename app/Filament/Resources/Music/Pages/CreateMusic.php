<?php

declare(strict_types=1);

namespace App\Filament\Resources\Music\Pages;

use App\Features\Shared\Filament\Traits\RedirectTrait;
use App\Filament\Resources\Music\MusicResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMusic extends CreateRecord
{
    use RedirectTrait;

    protected static string $resource = MusicResource::class;
}
