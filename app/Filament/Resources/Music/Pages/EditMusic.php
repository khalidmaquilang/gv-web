<?php

declare(strict_types=1);

namespace App\Filament\Resources\Music\Pages;

use App\Filament\Resources\Music\MusicResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMusic extends EditRecord
{
    protected static string $resource = MusicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
