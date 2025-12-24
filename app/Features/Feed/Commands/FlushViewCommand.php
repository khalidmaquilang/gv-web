<?php

declare(strict_types=1);

namespace App\Features\Feed\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class FlushViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:flush-view-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will flush all the views from cache';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $driver = Cache::getDefaultDriver();

        if ($driver === 'redis') {
            $this->flushRedis();

            return;
        }

        $this->flushLocal();
    }

    protected function flushRedis(): void
    {
        $processing_key = 'dirty_feeds_list_processing_'.time();

        if (! Redis::exists('dirty_feeds_list')) {
            return;
        }

        Redis::rename('dirty_feeds_list', $processing_key);

        $all_feed_ids = Redis::smembers($processing_key);

        $chunks = array_chunk($all_feed_ids, 500);

        foreach ($chunks as $feed_ids_chunk) {
            $cases = [];
            $params = [];
            $in_ids = [];

            foreach ($feed_ids_chunk as $feed_id) {
                $count = Cache::pull('video_views_buffer_'.$feed_id);

                if ($count > 0) {
                    // Prepare the CASE statement part
                    $cases[] = 'WHEN id = ? THEN views + ?';

                    // Add parameters for the CASE part
                    $params[] = $feed_id;
                    $params[] = $count;

                    // Keep track of ID for the IN clause
                    $in_ids[] = $feed_id;
                }
            }

            if ($in_ids !== []) {
                $cases_str = implode(' ', $cases);

                $in_placeholders = implode(',', array_fill(0, count($in_ids), '?'));

                $params = array_merge($params, $in_ids);

                DB::update(
                    sprintf('UPDATE feeds SET views = CASE %s ELSE views END WHERE id IN (%s)', $cases_str, $in_placeholders),
                    $params
                );
            }
        }

        Redis::del($processing_key);
    }

    protected function flushLocal(): void
    {
        $dirty_list = Cache::pull('dirty_feeds_list', []); // Get and clear list

        foreach ($dirty_list as $feed_id) {
            $count = Cache::pull('video_views_buffer_'.$feed_id);

            if ($count > 0) {
                DB::table('feeds')->where('id', $feed_id)->increment('views', $count);
            }
        }
    }
}
