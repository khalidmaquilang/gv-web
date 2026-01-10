<?php

declare(strict_types=1);

namespace App\Features\Chat\Models;

use App\Features\Chat\Enums\ConversationType;
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
        'type' => ConversationType::class,
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
