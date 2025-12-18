<?php

declare(strict_types=1);

namespace App\Features\Video\Models;

use App\Features\Music\Models\Music;
use App\Features\User\Models\User;
use App\Features\Video\Enums\VideoPrivacyEnum;
use App\Features\Video\Enums\VideoStatusEnum;
use App\Features\Webhook\Enums\WebhookEnum;
use App\Features\Webhook\Models\Interfaces\FfmpegInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model implements FfmpegInterface
{
    use HasUuids;

    protected $casts = [
        'images' => 'array',
        'allow_comments' => 'boolean',
        'privacy' => VideoPrivacyEnum::class,
        'status' => VideoStatusEnum::class,
    ];

    protected static function boot(): void
    {
        static::creating(function (Video $video): void {
            $user_id = auth()->id();
            abort_if($user_id === null, 404);

            $video->user_id = $user_id;
            $video->status = VideoStatusEnum::Processing;
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Music, $this>
     */
    public function music(): BelongsTo
    {
        return $this->belongsTo(Music::class);
    }

    public static function updateMediaStatus(string $model_id, WebhookEnum $status, int $duration, string $path): void
    {
        $video = static::find($model_id);
        if ($video === null) {
            return;
        }

        $video->update([
            'status' => $status->toVideo(),
            'video_path' => blank($path) ? $video->video_path : $path,
        ]);
    }
}
