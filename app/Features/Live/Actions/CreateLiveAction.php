<?php

declare(strict_types=1);

namespace App\Features\Live\Actions;

use App\Features\Feed\Enums\FeedPrivacyEnum;
use App\Features\Feed\Enums\FeedStatusEnum;
use App\Features\Feed\Models\Feed;
use App\Features\Live\Data\CreateLiveData;
use App\Features\Live\Models\Live;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateLiveAction
{
    public function handle(CreateLiveData $data): string
    {
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        return DB::transaction(function () use ($data, $user_id): string {
            // End all ongoing live streams for this user
            Live::query()
                ->whereNull('ended_at')
                ->whereHas('feed', function ($query) use ($user_id): void {
                    $query->where('user_id', $user_id);
                })
                ->update([
                    'ended_at' => now(),
                ]);

            // Create new live stream
            $live = Live::create([
                'stream_key' => Str::random(),
            ]);

            $feed = new Feed([
                'user_id' => $user_id,
                'title' => $data->title ?? '',
                'allow_comments' => true,
                'privacy' => FeedPrivacyEnum::PublicView,
                'status' => FeedStatusEnum::Approved,
            ]);

            $feed->content()->associate($live);
            $feed->saveQuietly();

            return $live->id;
        });
    }
}
