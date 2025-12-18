<?php

declare(strict_types=1);

namespace App\Features\Shared\Actions;

use Aws\Lambda\LambdaClient;
use Log;

class FfmpegAction
{
    public function handle(string $model_id, string $model_type, string $file_path, bool $is_video, ?string $music_path = null): bool
    {
        $client = new LambdaClient([
            'version' => 'latest',
            'region' => config('services.aws.region'), // Your Lambda region
            'credentials' => [
                'key' => config('services.aws.key'),
                'secret' => config('services.aws.secret'),
            ],
        ]);

        $payload = [
            'modelId' => $model_id,
            'modelType' => $model_type,
            'filename' => $file_path,
            'isVideo' => $is_video,
            'musicFilename' => $music_path,
        ];

        try {
            $client->invoke([
                'FunctionName' => 'ffmpeg-audio',
                'InvocationType' => 'Event',
                'Payload' => json_encode($payload),
            ]);

            Log::info('Lambda triggered for: '.$payload['modelId']);

            return true;
        } catch (\Exception $e) {
            Log::error('Lambda Trigger Failed: '.$e->getMessage());

            return false;
        }
    }
}
