<?php

declare(strict_types=1);

namespace App\Features\Live\Models;

use App\Features\Feed\Models\Feed;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property string $id
 * @property string $stream_key
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $ended_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Feed|null $feed
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Live newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Live newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Live query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Live whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Live whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Live whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Live whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Live whereStreamKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Live whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Live extends Model
{
    use HasUuids;

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * @return MorphOne<Feed, $this>
     */
    public function feed(): MorphOne
    {
        return $this->morphOne(Feed::class, 'content');
    }
}
