<?php

declare(strict_types=1);

namespace App\Filament\Resources\Music\Pages;

use App\Filament\Resources\Music\MusicResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMusic extends CreateRecord
{
    protected static string $resource = MusicResource::class;
}
