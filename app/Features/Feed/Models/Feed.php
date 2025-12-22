<?php

declare(strict_types=1);

namespace App\Features\Feed\Models;

use App\Features\Feed\Enums\FeedPrivacyEnum;
use App\Features\Feed\Enums\FeedStatusEnum;
use App\Features\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property FeedPrivacyEnum $privacy
 * @property FeedStatusEnum $status
 * @property-read Feed $content
 * @property-read User|null $user
 *
 * @method static Builder<static>|Feed newModelQuery()
 * @method static Builder<static>|Feed newQuery()
 * @method static Builder<static>|Feed published()
 * @method static Builder<static>|Feed query()
 *
 * @mixin \Eloquent
 */
class Feed extends Model
{
    use HasUuids;

    /**
     * @var string[]
     */
    protected $casts = [
        'allow_comments' => 'boolean',
        'privacy' => FeedPrivacyEnum::class,
        'status' => FeedStatusEnum::class,
    ];

    /**
     * @return MorphTo<Feed, $this>
     */
    public function content(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<Feed,>  $query
     * @return Builder<Feed,>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereIn('status', [FeedStatusEnum::Processed, FeedStatusEnum::Approved]);
    }
}
