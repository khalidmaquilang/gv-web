<?php

declare(strict_types=1);

namespace App\Features\Feed\Models;

use App\Features\Comment\Models\Comment;
use App\Features\Feed\Enums\FeedPrivacyEnum;
use App\Features\Feed\Enums\FeedStatusEnum;
use App\Features\User\Models\User;
use App\Features\Video\Policies\FeedPolicy;
use Binafy\LaravelReaction\Traits\Reactable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $id
 * @property string $user_id
 * @property string $content_type
 * @property string $content_id
 * @property string|null $title
 * @property bool $allow_comments
 * @property FeedPrivacyEnum $privacy
 * @property FeedStatusEnum $status
 * @property int $views
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Comment> $comments
 * @property-read int|null $comments_count
 * @property-read Feed $content
 * @property-read bool $is_reacted
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Binafy\LaravelReaction\Models\Reaction> $reactions
 * @property-read int|null $reactions_count
 * @property-read User $user
 *
 * @method static Builder<static>|Feed accessible(string $user_id)
 * @method static Builder<static>|Feed newModelQuery()
 * @method static Builder<static>|Feed newQuery()
 * @method static Builder<static>|Feed published()
 * @method static Builder<static>|Feed query()
 * @method static Builder<static>|Feed whereAllowComments($value)
 * @method static Builder<static>|Feed whereContentId($value)
 * @method static Builder<static>|Feed whereContentType($value)
 * @method static Builder<static>|Feed whereCreatedAt($value)
 * @method static Builder<static>|Feed whereId($value)
 * @method static Builder<static>|Feed wherePrivacy($value)
 * @method static Builder<static>|Feed whereStatus($value)
 * @method static Builder<static>|Feed whereTitle($value)
 * @method static Builder<static>|Feed whereUpdatedAt($value)
 * @method static Builder<static>|Feed whereUserId($value)
 * @method static Builder<static>|Feed whereViews($value)
 *
 * @mixin \Eloquent
 */
#[UsePolicy(FeedPolicy::class)]
class Feed extends Model
{
    use HasUuids;
    use Reactable;

    /**
     * @var string[]
     */
    protected $casts = [
        'allow_comments' => 'boolean',
        'privacy' => FeedPrivacyEnum::class,
        'status' => FeedStatusEnum::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (Feed $feed): void {
            $user_id = auth()->id();
            abort_if($user_id === null, 404);

            $feed->user_id = $user_id;
            $feed->status = FeedStatusEnum::Processing;
        });
    }

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
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @param  Builder<Feed,>  $query
     * @return Builder<Feed,>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereIn('status', [FeedStatusEnum::Processed, FeedStatusEnum::Approved]);
    }

    public function scopeAccessible(Builder $query, string $user_id): Builder
    {
        return $query
            ->where(function (Builder $query) use ($user_id): void {
                $query->where(function (Builder $q): void {
                    $q->where('privacy', FeedPrivacyEnum::PublicView)
                        ->whereIn('status', [FeedStatusEnum::Processed, FeedStatusEnum::Approved]);
                })
                    ->orWhere('user_id', $user_id);
            });
    }
}
