<?php

declare(strict_types=1);

namespace App\Features\User\Controllers;

use App\Features\User\Actions\GetUserVideosAction;
use App\Features\User\Models\User;
use App\Features\Video\Data\VideoData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class GetUserVideosController extends Controller
{
    public function __construct(protected GetUserVideosAction $get_user_videos_action) {}

    public function __invoke(string $user_id): JsonResponse
    {
        /** @var ?User $user */
        $user = auth()->user();
        abort_if($user === null, 404);

        $videos = $this->get_user_videos_action->handle($user_id, $user->id);

        return response()->json([
            'data' => VideoData::collect($videos),
        ]);
    }
}
