<?php

declare(strict_types=1);

namespace App\Features\Chat\Models;

use App\Features\Chat\Enums\ConversationTypeEnum;
use App\Features\User\Models\User;
use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property ConversationTypeEnum $type
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Features\Chat\Models\Chat|null $latestMessage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Features\Chat\Models\Chat> $messages
 * @property-read int|null $messages_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $users
 * @property-read int|null $users_count
 *
 * @method static Builder<static>|Conversation direct()
 * @method static \Database\Factories\ConversationFactory factory($count = null, $state = [])
 * @method static Builder<static>|Conversation forUser(string $userId)
 * @method static Builder<static>|Conversation group()
 * @method static Builder<static>|Conversation newModelQuery()
 * @method static Builder<static>|Conversation newQuery()
 * @method static Builder<static>|Conversation onlyTrashed()
 * @method static Builder<static>|Conversation query()
 * @method static Builder<static>|Conversation whereCreatedAt($value)
 * @method static Builder<static>|Conversation whereDeletedAt($value)
 * @method static Builder<static>|Conversation whereId($value)
 * @method static Builder<static>|Conversation whereName($value)
 * @method static Builder<static>|Conversation whereType($value)
 * @method static Builder<static>|Conversation whereUpdatedAt($value)
 * @method static Builder<static>|Conversation withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Conversation withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'type' => ConversationTypeEnum::class,
    ];

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_user')
            ->withPivot(['last_read_at', 'is_muted', 'is_archived', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<Chat, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Chat::class)->orderBy('created_at', 'desc');
    }

    /**
     * @return HasOne<Chat, $this>
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Chat::class)->latestOfMany();
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeDirect(Builder $query): Builder
    {
        return $query->where('type', 'direct');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeGroup(Builder $query): Builder
    {
        return $query->where('type', 'group');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForUser(Builder $query, string $userId): Builder
    {
        return $query->whereHas('users', function (Builder $q) use ($userId): void {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Find or create a direct conversation between two users
     */
    public static function findOrCreateDirectConversation(string $user1Id, string $user2Id): self
    {
        // Try to find existing direct conversation between these two users
        $conversation = static::query()
            ->direct()
            ->whereHas('users', function (Builder $q) use ($user1Id): void {
                $q->where('user_id', $user1Id);
            })
            ->whereHas('users', function (Builder $q) use ($user2Id): void {
                $q->where('user_id', $user2Id);
            })
            ->first();

        if ($conversation) {
            return $conversation;
        }

        // Create new conversation
        $conversation = static::create(['type' => 'direct']);

        // Attach both users with UUID for pivot
        $conversation->users()->attach([
            $user1Id => [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'joined_at' => now(),
            ],
            $user2Id => [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'joined_at' => now(),
            ],
        ]);

        return $conversation;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\ConversationFactory::new();
    }
}
