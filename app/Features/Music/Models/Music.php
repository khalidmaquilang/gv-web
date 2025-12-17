<?php

declare(strict_types=1);

namespace App\Features\Music\Models;

use App\Features\Music\Enums\MusicStatusEnum;
use App\Features\Webhook\Enums\WebhookEnum;
use App\Features\Webhook\Models\Interfaces\FfmpegInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 * @property string $artist
 * @property string $path
 * @property int|null $duration
 * @property MusicStatusEnum $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Music newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Music newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Music query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Music whereArtist($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Music whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Music whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Music whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Music whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Music wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Music whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Music whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Music extends Model implements FfmpegInterface
{
    use HasUuids;

    protected $casts = [
        'status' => MusicStatusEnum::class,
    ];

    public static function updateMediaStatus(string $model_id, WebhookEnum $status, int $duration, string $path): void
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
