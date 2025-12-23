<?php

declare(strict_types=1);

namespace App\Features\Feed\Actions;

use App\Features\Feed\Models\Feed;
use Binafy\LaravelReaction\Enums\LaravelReactionTypeEnum;

class ReactFeedAction
{
    public function handle(string $feed_id, string $user_id): void
    {
        $feed = Feed::query()
            ->where('id', $feed_id)
            ->accessible($user_id)
            ->firstOrFail();

        if ($feed->isReacted()) {
            $feed->removeReaction(LaravelReactionTypeEnum::REACTION_LOVE);
        } else {
            $feed->reaction(LaravelReactionTypeEnum::REACTION_LOVE);
        }
    }
}
