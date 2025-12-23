<?php

declare(strict_types=1);

namespace App\Features\Feed\Controllers;

use App\Features\Feed\Actions\ReactFeedAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ReactFeedController extends Controller
{
    public function __construct(protected ReactFeedAction $react_feed_action) {}

    public function __invoke(string $feed_id): JsonResponse
    {
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        $this->react_feed_action->handle($feed_id, $user_id);

        return response()->json(['message' => 'success']);
    }
}
