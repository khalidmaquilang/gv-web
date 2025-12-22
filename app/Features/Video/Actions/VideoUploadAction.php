<?php

declare(strict_types=1);

namespace App\Features\Video\Actions;

use App\Features\Feed\Enums\FeedPrivacyEnum;
use App\Features\Feed\Enums\FeedStatusEnum;
use App\Features\Feed\Models\Feed;
use App\Features\Shared\Actions\FfmpegAction;
use App\Features\Video\Data\VideoUploadData;
use App\Features\Video\Models\Video;
use Illuminate\Support\Facades\DB;
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

        DB::transaction(function () use ($data, $path, $user_id): void {
            $video = Video::create([
                'music_id' => $data->music_id ?? null,
                'video_path' => $path,
            ]);

            $feed = new Feed([
                'user_id' => $user_id,
                'title' => $data->description ?? '',
                'allow_comments' => $data->allow_comments ?? false,
                'privacy' => $data->privacy ?? FeedPrivacyEnum::PublicView,
                'status' => FeedStatusEnum::Processing,
            ]);

            $feed->content()->associate($video);
            $feed->save();

            app(FfmpegAction::class)
                ->handle(
                    model_id: $video->id,
                    model_type: Video::class,
                    file_path: $video->video_path,
                    is_video: true,
                    music_path: $video->music->path ?? null,
                    user_id: $user_id,
                );
        });
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
