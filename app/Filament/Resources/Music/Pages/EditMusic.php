<?php

declare(strict_types=1);

namespace App\Filament\Resources\Music\Pages;

use App\Features\Music\Models\Music;
use App\Features\Shared\Actions\FfmpegAction;
use App\Features\Shared\Filament\Traits\RedirectTrait;
use App\Filament\Resources\Music\MusicResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMusic extends EditRecord
{
    use RedirectTrait;

    protected static string $resource = MusicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function afterSave(): void
    {
        /** @var Music $music */
        $music = $this->record;

        app(FfmpegAction::class)
            ->handle(
                model_id: $music->id,
                model_type: Music::class,
                file_path: $music->path,
                is_video: false,
            );
    }
}
