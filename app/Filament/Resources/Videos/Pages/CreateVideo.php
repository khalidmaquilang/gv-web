<?php

declare(strict_types=1);

namespace App\Filament\Resources\Videos\Pages;

use App\Features\Shared\Actions\FfmpegAction;
use App\Features\Shared\Filament\Traits\RedirectTrait;
use App\Features\Video\Models\Video;
use App\Filament\Resources\Videos\VideoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVideo extends CreateRecord
{
    use RedirectTrait;

    protected static string $resource = VideoResource::class;

    public function afterCreate(): void
    {
        /** @var Video $video */
        $video = $this->record;

        app(FfmpegAction::class)
            ->handle(
                model_id: $video->id,
                model_type: Video::class,
                file_path: $video->video_path,
                is_video: true,
                music_path: $video->music->path ?? null,
            );
    }
}
