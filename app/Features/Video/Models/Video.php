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

/**
 * @property string $id
 * @property string $user_id
 * @property string|null $music_id
 * @property string|null $title
 * @property string $description
 * @property string $thumbnail
 * @property string|null $video_path
 * @property array<array-key, mixed>|null $images
 * @property bool $allow_comments
 * @property VideoPrivacyEnum $privacy
 * @property VideoStatusEnum $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Music|null $music
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereAllowComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereMusicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video wherePrivacy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereThumbnail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereVideoPath($value)
 *
 * @mixin \Eloquent
 */
class Video extends Model implements FfmpegInterface
{
    use HasUuids;

    /**
     * @var string[]
     */
    protected $casts = [
        'images' => 'array',
        'allow_comments' => 'boolean',
        'privacy' => VideoPrivacyEnum::class,
        'status' => VideoStatusEnum::class,
    ];

    protected static function booted(): void
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
