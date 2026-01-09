<?php

declare(strict_types=1);

namespace App\Features\Music\Models;

use App\Features\Music\Enums\MusicStatusEnum;
use App\Features\Music\Enums\MusicTypeEnum;
use App\Features\Webhook\Enums\WebhookEnum;
use App\Features\Webhook\Models\Interfaces\FfmpegInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 * @property string $artist
 * @property string $path
 * @property string|null $thumbnail
 * @property bool $active
 * @property int|null $duration
 * @property MusicStatusEnum $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property MusicTypeEnum $type
 * @property-read string $duration_formatted
 *
 * @method static Builder<static>|Music newModelQuery()
 * @method static Builder<static>|Music newQuery()
 * @method static Builder<static>|Music published()
 * @method static Builder<static>|Music query()
 * @method static Builder<static>|Music whereActive($value)
 * @method static Builder<static>|Music whereArtist($value)
 * @method static Builder<static>|Music whereCreatedAt($value)
 * @method static Builder<static>|Music whereDuration($value)
 * @method static Builder<static>|Music whereId($value)
 * @method static Builder<static>|Music whereName($value)
 * @method static Builder<static>|Music wherePath($value)
 * @method static Builder<static>|Music whereStatus($value)
 * @method static Builder<static>|Music whereThumbnail($value)
 * @method static Builder<static>|Music whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Music extends Model implements FfmpegInterface
{
    use HasUuids;

    protected $casts = [
        'active' => 'boolean',
        'status' => MusicStatusEnum::class,
        'type' => MusicTypeEnum::class,
    ];

    public function getDurationFormattedAttribute(): string
    {
        $duration = (int) ($this->duration ?? 0);

        if ($duration <= 0) {
            return '00:00';
        }

        $hours = floor($duration / 3600);
        $minutes = ((int) ($duration / 60)) % 60;
        $seconds = $duration % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * @return Builder<Music>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('active', true)
            ->where('status', MusicStatusEnum::Processed);
    }

    public static function updateMediaStatus(string $model_id, WebhookEnum $status, int $duration, string $path, string $thumbnail_path): void
    {
        $music = static::find($model_id);
        if ($music === null) {
            return;
        }

        $music->update([
            'status' => $status->toMusic(),
            'duration' => $duration,
            'path' => blank($path) ? $music->path : $path,
        ]);
    }
}
