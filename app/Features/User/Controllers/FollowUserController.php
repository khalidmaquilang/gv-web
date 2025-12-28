<?php

declare(strict_types=1);

namespace App\Features\User\Controllers;

use App\Features\User\Actions\FollowUserAction;
use App\Features\User\Actions\GetUserDataAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class FollowUserController extends Controller
{
    public function __construct(
        protected FollowUserAction $follow_user_action,
        protected GetUserDataAction $get_user_data_action
    ) {}

    public function __invoke(string $userId): JsonResponse
    {
        $user = $this->follow_user_action->handle($userId);

        return response()->json($this->get_user_data_action->handle($user));
    }
}
