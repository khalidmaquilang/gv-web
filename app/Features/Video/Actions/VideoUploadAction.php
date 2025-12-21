<?php

declare(strict_types=1);

namespace App\Features\Video\Actions;

use App\Features\Shared\Actions\FfmpegAction;
use App\Features\Video\Data\VideoUploadData;
use App\Features\Video\Enums\VideoPrivacyEnum;
use App\Features\Video\Enums\VideoStatusEnum;
use App\Features\Video\Models\Video;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VideoUploadAction
{
    public function handle(VideoUploadData $data): void
    {
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        $path = Video::getVideoPath($user_id).'/'.Str::uuid().'.mp4';
        $path = $this->uploadFile($data->video->getRealPath(), $path);

        $video = Video::create([
            'user_id' => $user_id,
            'music_id' => $data->music_id ?? null,
            'description' => $data->description ?? '',
            'video_path' => $path,
            'allow_comments' => $data->allow_comments ?? false,
            'privacy' => $data->privacy ?? VideoPrivacyEnum::PublicView,
            'status' => VideoStatusEnum::Processing,
        ]);

        app(FfmpegAction::class)
            ->handle(
                model_id: $video->id,
                model_type: Video::class,
                file_path: $video->video_path,
                is_video: true,
                music_path: $video->music->path ?? null,
                user_id: $user_id,
            );
    }

    protected function uploadFile(string $input_path, string $output_path): string
    {
        $stream = fopen($input_path, 'r');

        $key = config('filesystems.disks.bunny.api_key') ?? '';
        $region = config('filesystems.disks.bunny.region') ?? 'sg';
        $zone = config('filesystems.disks.bunny.storage_zone') ?? 'gv-dev';

        Http::withHeaders([
            'AccessKey' => $key,
            'Content-Type' => 'application/octet-stream',
        ])->withBody($stream, 'application/octet-stream')
            ->put(sprintf('https://%s.storage.bunnycdn.com/%s/%s', $region, $zone, $output_path));

        fclose($stream);

        return $output_path;
    }
}
