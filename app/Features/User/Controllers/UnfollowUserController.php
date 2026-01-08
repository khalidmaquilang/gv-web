<?php

declare(strict_types=1);

namespace App\Features\User\Controllers;

use App\Features\User\Actions\GetUserDataAction;
use App\Features\User\Actions\UnfollowUserAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class UnfollowUserController extends Controller
{
    public function __construct(
        protected UnfollowUserAction $unfollow_user_action,
        protected GetUserDataAction $get_user_data_action
    ) {}

    public function __invoke(string $user_id): JsonResponse
    {
        $user = $this->unfollow_user_action->handle($user_id);

        return response()->json($this->get_user_data_action->handle($user));
    }
}
