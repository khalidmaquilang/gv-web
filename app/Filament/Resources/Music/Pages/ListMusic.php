<?php

declare(strict_types=1);

namespace App\Filament\Resources\Music\Pages;

use App\Filament\Resources\Music\MusicResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMusic extends ListRecords
{
    protected static string $resource = MusicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
