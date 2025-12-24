<?php

declare(strict_types=1);

namespace App\Features\Feed\Controllers;

use App\Features\Feed\Actions\CheckBotViewFeedAction;
use App\Features\Feed\Actions\GetUserFingerPrintAction;
use App\Features\Feed\Actions\SetFeedLockAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ViewFeedController extends Controller
{
    public function __construct(
        protected GetUserFingerPrintAction $get_user_finger_print_action,
        protected SetFeedLockAction $set_feed_lock_action,
        protected CheckBotViewFeedAction $check_bot_view_feed_action,
    ) {}

    public function __invoke(string $feed_id): JsonResponse
    {
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        $finger_print = $this->get_user_finger_print_action->handle($user_id);

        $is_bot = $this->check_bot_view_feed_action->handle($finger_print);

        if ($is_bot) {
            return response()->json(['status' => 'ignored', 'reason' => 'velocity_limit']);
        }

        $is_allowed = $this->set_feed_lock_action->handle($feed_id, $finger_print);

        if (! $is_allowed) {
            return response()->json(['status' => 'ignored', 'reason' => 'cooldown']);
        }

        if (! Cache::has('video_views_buffer_'.$feed_id)) {
            Cache::put('video_views_buffer_'.$feed_id, 0);
        }

        Cache::increment('video_views_buffer_'.$feed_id);

        $this->markFeedAsDirty($feed_id);

        return response()->json(['message' => 'success']);
    }

    /**
     * Safely adds the feed_id to the dirty list based on the cache driver.
     */
    protected function markFeedAsDirty(string $feed_id): void
    {
        if (Cache::getDefaultDriver() === 'redis') {
            // Access the Redis connection through the Cache store
            Cache::store('redis')->connection()->sadd('dirty_feeds_list', $feed_id);

            return;
        }

        $list = Cache::get('dirty_feeds_list', []);

        if (in_array($feed_id, $list)) {
            return;
        }

        $list[] = $feed_id;
        // Save for 1 hour
        Cache::put('dirty_feeds_list', $list, 3600);
    }
}
