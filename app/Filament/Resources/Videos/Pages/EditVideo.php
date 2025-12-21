<?php

declare(strict_types=1);

namespace App\Filament\Resources\Videos\Pages;

use App\Features\Shared\Filament\Traits\RedirectTrait;
use App\Filament\Resources\Videos\VideoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVideo extends EditRecord
{
    use RedirectTrait;

    protected static string $resource = VideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
