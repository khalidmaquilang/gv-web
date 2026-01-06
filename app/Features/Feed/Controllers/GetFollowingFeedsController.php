<?php

declare(strict_types=1);

namespace App\Features\Feed\Controllers;

use App\Features\Feed\Actions\GetFeedsAction;
use App\Features\Feed\Data\FeedData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class GetFollowingFeedsController extends Controller
{
    public function __construct(protected GetFeedsAction $get_feeds_action) {}

    public function __invoke(): JsonResponse
    {
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        $feeds = $this->get_feeds_action->handle($user_id, true);

        return response()->json(FeedData::collect($feeds));
    }
}
