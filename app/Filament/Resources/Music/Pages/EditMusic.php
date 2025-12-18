<?php

declare(strict_types=1);

namespace App\Filament\Resources\Music\Pages;

use App\Features\Music\Enums\MusicStatusEnum;
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

    protected bool $new_audio = false;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, string>  $data
     * @return array<string, string>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $path = $this->data['path'] ?? [];
        $data_path = reset($path);

        if ($data_path !== $this->record->path) {
            $this->new_audio = true;
            $data['status'] = MusicStatusEnum::Processing;
        }

        return $data;
    }

    public function afterSave(): void
    {
        if (! $this->new_audio) {
            return;
        }

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
