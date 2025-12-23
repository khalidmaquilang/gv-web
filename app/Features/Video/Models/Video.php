<?php

declare(strict_types=1);

namespace App\Features\Video\Models;

use App\Features\Feed\Models\Feed;
use App\Features\Music\Models\Music;
use App\Features\Webhook\Enums\WebhookEnum;
use App\Features\Webhook\Models\Interfaces\FfmpegInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property string $id
 * @property string|null $music_id
 * @property string|null $thumbnail
 * @property string|null $video_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Feed|null $feed
 * @property-read Music|null $music
 *
 * @method static Builder<static>|Video newModelQuery()
 * @method static Builder<static>|Video newQuery()
 * @method static Builder<static>|Video query()
 * @method static Builder<static>|Video whereCreatedAt($value)
 * @method static Builder<static>|Video whereId($value)
 * @method static Builder<static>|Video whereMusicId($value)
 * @method static Builder<static>|Video whereThumbnail($value)
 * @method static Builder<static>|Video whereUpdatedAt($value)
 * @method static Builder<static>|Video whereVideoPath($value)
 *
 * @mixin \Eloquent
 */
class Video extends Model implements FfmpegInterface
{
    use HasUuids;

    /**
     * @return BelongsTo<Music, $this>
     */
    public function music(): BelongsTo
    {
        return $this->belongsTo(Music::class);
    }

    /**
     * @return MorphOne<Feed, $this>
     */
    public function feed(): MorphOne
    {
        return $this->morphOne(Feed::class, 'content');
    }

    public static function updateMediaStatus(string $model_id, WebhookEnum $status, int $duration, string $path, string $thumbnail_path): void
    {
        $video = static::find($model_id);
        if ($video === null) {
            return;
        }

        $video->update([
            'video_path' => blank($path) ? $video->video_path : $path,
            'thumbnail' => $thumbnail_path,
        ]);

        /** @var Feed $feed */
        $feed = $video->feed;
        $feed->update([
            'status' => $status->toFeed(),
        ]);
    }

    public static function getVideoPath(string $user_id): string
    {
        return $user_id.'/videos';
    }
}
